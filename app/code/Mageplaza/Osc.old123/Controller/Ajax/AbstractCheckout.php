<?php

/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Osc\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\Osc\Helper\Data as HelperData;
use Mageplaza\Osc\Helper\Config as HelperConfig;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Mageplaza\Osc\Helper\Checkout\Address as CheckoutAddress;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Escaper;
use Magento\Checkout\Model\Type\Onepage;
use Mageplaza\Osc\Helper\Block as HelperBlock;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Locale\Resolver;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\GiftMessage\Model\GiftMessageManager;

abstract class AbstractCheckout extends Action
{
    protected $_helperData;
    protected $_helperConfig;
    protected $_helperBlock;
    protected $_accountManagement;
    protected $_customerSession;
    protected $_checkoutSession;
    protected $_checkoutAddress;
    protected $_jsonHelper;
    protected $_escaper;
    protected $_onepage;
    protected $_cart;
    protected $_result = array();
    protected $_localeResolver;
    protected $_appProductMetadataInterface;
    protected $_customerUrl;
    protected $_resultRawFactory;
    protected $_giftMessage;

    public function __construct(
        Context $context,
        HelperData $helperData,
        HelperConfig $helperConfig,
        HelperBlock $helperBlock,
        AccountManagementInterface $accountManagement,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        CheckoutAddress $checkoutAddress,
        JsonHelper $jsonHelper,
        Escaper $escaper,
        Onepage $onepage,
        Cart $cart,
        Resolver $localeResolver,
        ProductMetadataInterface $appProductMetadataInterface,
        CustomerUrl $customerUrl,
        RawFactory $resultRawFactory,
        GiftMessageManager $giftMessage
    ) {
        parent::__construct($context);
        $this->_helperData                  = $helperData;
        $this->_helperConfig                = $helperConfig;
        $this->_helperBlock                 = $helperBlock;
        $this->_accountManagement           = $accountManagement;
        $this->_customerSession             = $customerSession;
        $this->_checkoutSession             = $checkoutSession;
        $this->_checkoutAddress             = $checkoutAddress;
        $this->_jsonHelper                  = $jsonHelper;
        $this->_escaper                     = $escaper;
        $this->_onepage                     = $onepage;
        $this->_cart                        = $cart;
        $this->_localeResolver              = $localeResolver;
        $this->_appProductMetadataInterface = $appProductMetadataInterface;
        $this->_customerUrl                 = $customerUrl;
        $this->_resultRawFactory            = $resultRawFactory;
        $this->_giftMessage                 = $giftMessage;
    }


    /**
     * @return HelperBlock
     */
    public function getHelperBlock()
    {
        return $this->_helperBlock;
    }

    /**
     * @return HelperData
     */
    public function getHelperData()
    {
        return $this->_helperData;
    }

    /**
     * @return HelperConfig
     */
    public function getHelperConfig()
    {
        return $this->_helperConfig;
    }

    /**
     * @return Onepage
     */
    public function getOnepage()
    {
        return $this->_onepage;
    }

    public function getCart()
    {
        return $this->_cart;
    }

    public function isAjax()
    {
        return $this->getRequest()->isAjax();
    }

    protected function _expireAjax()
    {
        $quote = $this->getOnepage()->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || !$quote->validateMinimumAmount()) {
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if ($this->_objectManager->get('Magento\Checkout\Model\Session')->getCartWasUpdated(true)
            &&
            !in_array($action, ['index'])
        ) {
            return true;
        }

        return false;
    }

    /**
     * @reference \Magento\Checkout\Controller\Onepage\AbstractOnepage
     */
    protected function _ajaxRedirectResponse()
    {
        $result = array(
            'quote_is_empty' => true
        );

        return $this->getBodyResponse($result);
    }

    /**
     * @param $dataShipping
     * @param $customerAddressId
     * @return array
     */
    public function saveShipping($dataShipping)
    {
        $result = array(
            'success' => true
        );

        try {
            $address = $this->getShippingAddress();
            $address->addData($dataShipping);
            $address->setCollectShippingRates(true)->save();

        } catch (Exception $e) {
            $result['success'] = false;
            $result['error']   = $e->getMessage();
        }

        return $result;

    }

    /**
     * @param $dataShipping
     * @param $customerAddressId
     * @return array'
     */
    public function saveBilling($dataBilling)
    {
        $result  = array(
            'success' => true
        );
        $billing = $this->getBillingAddress();
        try {
            $billing->addData($dataBilling);
            $result['success'] = true;
        } catch (Exception $e) {
            $result['success'] = false;
            $result['error']   = $e->getMessage();
        }

        return $result;
    }

    /**
     * @param $result
     */

    public function getBodyResponse($result)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody($this->_jsonHelper->jsonEncode($result));
    }

    /**
     * Get Checkout Onepage Quote
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        return $this->getOnepage()->getQuote();
    }

    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }

    public function getBillingAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    public function collectQuote()
    {
        $this->getQuote()->collectTotals()->save();
    }

    /**
     * @return \Mageplaza\Osc\Model\Updater
     */
    public function getBlockHelper()
    {
        return $this->_helperBlock;
    }

    public function getFormData()
    {
        return $this->_checkoutSession->getData('osc_form_values');
    }

    public function setFormData($data)
    {
        $this->_checkoutSession->setData('osc_form_values', $data);
    }

    protected function _updateOrderReview()
    {
        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }
        $result = [
            'success'     => true,
            'messages'    => [],
            'blocks'      => [],
            'grand_total' => ''
        ];
        try {
            if ($this->getRequest()->isPost()) {
                $this->getOnepage()->getQuote()->collectTotals()->save();
                $result['blocks'] = $this->getBlockHelper()->getActionBlocks();
                if ($this->_helperConfig->showGrandTotal()) {
                    $result['grand_total'] = $this->getGrandTotal();
                }
            } else {
                $result['success']    = false;
                $result['messages'][] = __('Please specify a payment method.');
            }
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['error'][] = __('Unable to update cart item');
        }

        return $this->getBodyResponse($result);

    }

    public function getGrandTotal()
    {
        return $this->_helperData->getGrandTotal($this->getOnepage()->getQuote());
    }

}