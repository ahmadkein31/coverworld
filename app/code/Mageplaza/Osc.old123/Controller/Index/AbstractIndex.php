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
namespace Mageplaza\Osc\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Mageplaza\Osc\Helper\Config as HelperConfig;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Osc\Helper\Checkout\Address as CheckoutAddress;
use Mageplaza\Osc\Helper\Checkout\Payment as CheckoutPayment;

abstract class AbstractIndex extends Action
{
    protected $_helperConfig;
    protected $_customerSession;
    protected $_onepage;
    protected $_resultPageFactory;
    protected $_checkoutAddress;
    protected $_checkoutPayment;

    /**
     * Init Checkout Attributes
     *
     * @param Context         $context
     * @param HelperConfig    $helperConfig
     * @param CustomerSession $customerSession
     * @param Onepage         $onepage
     */
    public function __construct(
        Context $context,
        HelperConfig $helperConfig,
        CustomerSession $customerSession,
        Onepage $onepage,
        PageFactory $resultPageFactory,
        CheckoutAddress $checkoutAddress,
        CheckoutPayment $checkoutPayment
    ) {
        $this->_helperConfig      = $helperConfig;
        $this->_customerSession   = $customerSession;
        $this->_onepage           = $onepage;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_checkoutAddress   = $checkoutAddress;
        $this->_checkoutPayment   = $checkoutPayment;
        parent::__construct($context);
    }

    /**
     * get Onepage model
     *
     * @return Onepage
     */
    public function getOnepage()
    {
        return $this->_onepage;
    }

    /**
     * Get Quote Model
     *
     * @return \Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        return $this->getOnepage()->getQuote();
    }

    /**
     * get Shipping Address
     *
     * @return \Magento\Quote\Model\Quote\Address
     */
    public function getShippingAddress()
    {
        return $this->getQuote()->getShippingAddress();
    }
}