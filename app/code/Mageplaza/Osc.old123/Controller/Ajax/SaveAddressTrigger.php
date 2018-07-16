<?php

namespace Mageplaza\Osc\Controller\Ajax;

class SaveAddressTrigger extends AbstractCheckout
{
    /**
     * Save Address Action Response
     *
     * @regurn json
     */
    public function execute()
    {
        if (!$this->isAjax()) {
            return;
        }
        $result = array(
            'success'  => true,
            'messages' => [],
            'blocks'   => [],
        );
        if ($this->getRequest()->isPost()) {
            $newData     = $this->getRequest()->getPost('billing');
            $currentData = is_array($this->getFormData()) ? $this->getFormData() : [];
            $this->setFormData(array_merge($currentData, $newData));
            $dataBilling = $this->getRequest()->getPost('billing', []);

            if (isset($dataBilling['email'])) {
                $dataBilling['email'] = trim($dataBilling['email']);
            }
            if (!isset($dataBilling['country_id'])) {
                $dataBilling['country_id'] = $this->_helperConfig->getDefaultCountryId();
            }
            $saveBilling    = $this->saveBilling($dataBilling);
            $useForShipping = isset($dataBilling['use_for_shipping']) ? $dataBilling['use_for_shipping'] : 0;
            if ($useForShipping == 0) {
                $this->_checkoutSession->setData('same_as_billing', 0);
                $dataShipping                    = $this->getRequest()->getPost('shipping', []);
                $dataShipping['same_as_billing'] = 0;

            } else {
                $dataShipping                    = $dataBilling;
                $dataShipping['same_as_billing'] = 1;
                $this->_checkoutSession->setData('same_as_billing', 1);
            }
            $saveShipping = $this->saveShipping($dataShipping);
            if (isset($saveShipping)) {
                $saveResult = array_merge($saveBilling, $saveShipping);
            } else {
                $saveResult = $saveBilling;
            }
            if (is_array($saveResult) && isset($saveResult['error'])) {
                if (isset($saveResult['message']) && is_array($saveResult['message'])) {
                    $result['messages'] = array_merge($result['messages'], $saveResult['message']);
                } else {
                    $result['messages'] = array_merge($result['messages'], $saveResult['message']);

                }
                $result['success'] = false;
            }

            /**
             * Set Default Shipping Method
             **/
            $this->_checkoutAddress->setDefaultShippingMethod($this->getShippingAddress());
            $this->collectQuote();
            $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
        } else {
            $result['messages'][] = __('Please enter billing address information.');
            $result['success']    = false;
        }
        if (!empty($result)) {
            $this->_result = $result;
        }

        return $this->getBodyResponse($this->_result);
    }
}
