<?php

namespace Mageplaza\Osc\Controller\Ajax;

use Magento\Framework\App\ObjectManager;

class SaveOrder extends AbstractCheckout
{

    /**
     * Save order
     *
     * @return json
     */
    public function execute()
    {
        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        try {
            if ($this->getRequest()->isPost()) {
                $billingData  = $this->getRequest()->getPost('billing', []);
                $shippingData = $this->getRequest()->getPost('shipping', []);
                $result       = $this->createAccountWhenCheckout($billingData);
                if ($result['success']) {
                    $saveResult = $this->saveBillingShippingAddress($billingData, $shippingData);
                    if (isset($saveResult['error'])) {

                        $saveResult['message'] = is_array($saveResult['message']) ? $saveResult['message'] : [$saveResult['message']];
                        $result['messages']    = array_merge($result['messages'], $saveResult['message']);
                        $result['success']     = false;
                    } else {
                        $postedAgreements = array_keys($this->getRequest()->getPost('one_step_checkout_agreement', []));
                        if ($diff = array_diff(
                                $this->_helperConfig->getRequiredAgreementIds(),
                                $postedAgreements
                            )
                            && $this->_helperConfig->isEnabledTerm()
                        ) {
                            $result['success']    = false;
                            $result['messages'][] = __('You should agree to the terms and conditions first.');
                        } else {
                            $this->saveGiftMessage();
                            $this->saveOrderData();
                        }
                    }
                }
            } else {
                $result['success'] = false;
            }
        } catch (\Exception $e) {
            $msg                  = __('There was an error processing your order. Please contact us or try again later.');
            $result['messages'][] = $msg;
            $result['messages'][] = $e->getMessage();
            $result['success']    = false;
        }

        return $this->getBodyResponse($result);
    }

    /**
     * Save gift message
     */
    public function saveGiftMessage()
    {
        $isUsedGiftMessage = $this->getRequest()->getPost('use_gift_messages', false);
        if($isUsedGiftMessage){
            $giftMessages      = $this->getRequest()->getPost('giftmessage', []);
            if (!empty($giftMessages)) {
                $quote = $this->getQuote();
                try {
                    $this->_giftMessage->add($giftMessages, $quote);
                } catch (\Exception $e) {
                    /*You can add log here*/
                    \Zend_Debug::dump($e->getMessage());die('ok');
                }
            }
        }
    }

    /**
     * @param $billingData
     * @param $shippingData
     * @return array
     */
    public function saveBillingShippingAddress($billingData, $shippingData)
    {
        $quote   = $this->getQuote();
        $billing = $this->getQuote()->getBillingAddress();
        if (isset($billingData['email'])) {
            $billingData['email'] = trim($billingData['email']);
        }
        $customer = $this->_customerSession->getCustomer();
        if ($customer && $customer->getId()) {
            $addressId = $this->getRequest()->getPost('billing_address_id', false);
            if ($addressId) {
                $address     = $customer->getAddressById($addressId);
                $billingData = array_merge($billingData, $address->getData());
            }
        }

        // Save shipping address
        if (isset($billingData['use_for_shipping'])) {
            if ($billingData['use_for_shipping']) {
                $shippingData = $billingData;
                $addressId    = $this->getRequest()->getPost('billing_address_id', false);
            }
            else{
                $addressId    = $this->getRequest()->getPost('shipping_address_id', false);
            }
            $saveShipping = $this->getOnepage()->saveShipping($shippingData, $addressId);

        }
        $saveBilling         = $this->saveBilling($billingData);
        if (isset($saveShipping)) {
            $saveResult = array_merge($saveBilling, $saveShipping);
        } else {
            $saveResult = $saveBilling;
        }

        return $saveResult;
    }

    /**
     * Create accont when customer checkout
     *
     * @return array
     */
    public function createAccountWhenCheckout($billingData)
    {
        $result = [
            'success'  => true,
            'messages' => [],
        ];
        if (!$this->getOnepage()->getCustomerSession()->isLoggedIn()) {
            if (isset($billingData['create_account'])) {
                $this->getOnepage()->saveCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER);
            } else {
                $this->getOnepage()->saveCheckoutMethod(\Magento\Checkout\Model\Type\Onepage::METHOD_GUEST);
            }
        }
        if (!$this->getQuote()->getCustomerId() &&
            \Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER == $this->getQuote()->getCheckoutMethod()
        ) {
            if ($this->_customerEmailExists($billingData['email'])) {
                $result['success']    = false;
                $result['messages'][] = __('There is already a customer registered using this email address. Please login using this email address or enter a different email address to register your account.');
            } else {
                $this->createCustomerAccount($billingData);
            }
        }

        return $result;
    }

    public function createCustomerAccount($data)
    {
        $request           = $this->getRequest()->setParams($data);
        $customerExtractor = ObjectManager::getInstance()->create('Magento\Customer\Model\CustomerExtractor');
        $customer          = $customerExtractor->extract('customer_account_create', $request);

        $password     = $data['customer_password'];
        $confirmation = $data['confirm_password'];
        if ($this->checkPasswordConfirmation($password, $confirmation)) {
            $customer = $this->_accountManagement
                ->createAccount($customer, $password);

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $confirmationStatus = $this->_accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $this->_customerUrl->getEmailConfirmationUrl($customer->getEmail());
            } else {
                $this->_customerSession->setCustomerDataAsLoggedIn($customer);
            }
            $this->getQuote()->setCustomer($customer);
        }
    }

    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmation
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        return $password == $confirmation;
    }


    /**
     * @param $email
     * @return bool|\Magento\Customer\Model\Customer
     */
    protected function _customerEmailExists($email)
    {
        return $this->getHelperData()->getCustomerByEmail($email);
    }

    /**
     * Save
     */
    public function saveOrderData()
    {
        try {
            $this->_checkoutSession->setData('osc_order_data',
                [
                    'comments'      => $this->getRequest()->getPost('comments', false),
                    'is_subscribed' => $this->getRequest()->getPost('is_subscribed', false),
                    'billing'       => $this->getRequest()->getPost('billing', []),
                ]
            );
            $this->_eventManager->dispatch('osc_save_order_session_data_after',
                [
                    'request' => $this->getRequest()
                ]
            );

            return true;
        } catch (\Exception $e) {
            return false;
        }

    }
}
