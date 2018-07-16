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
namespace Mageplaza\Osc\Block\Container;

use Mageplaza\Osc\Block\Context;
use Mageplaza\Osc\Block\Container;
use Mageplaza\Osc\Helper\Checkout\Payment as CheckoutPayment;

class Form extends Container
{
    protected $_checkoutPayment;

    public function __construct(
        Context $context,
        CheckoutPayment $checkoutPayment
    ) {
        parent::__construct($context);
        $this->_checkoutPayment = $checkoutPayment;

    }

    /**
     * @return array
     */
    public function getCheckoutConfig()
    {
        return $this->_configProvider->getConfig();
    }

    public function getOscConfig()
    {
        return array(
            'savePaymentUrl'       => $this->getSavePaymentUrl(),
            'defaultPaymentMethod' => $this->_checkoutPayment->getDefaultPaymentMethod()
        );
    }

    /**
     * @return mixed
     */
    public function showGrandTotal()
    {
        return $this->getHelperConfig()->showGrandTotal();
    }

    /**
     * @return string
     */
    public function getBlockSection()
    {
        return $this->_jsonHelper->jsonEncode($this->_helperBlock->getBlocksSection());
    }

    public function getPlaceOrderUrl()
    {
        return $this->getUrl('onestepcheckout/ajax/saveOrder', ['_secure' => $this->isSecure()]);
    }

    public function getCheckoutSuccessUrl()
    {
        return $this->getUrl('checkout/onepage/success', ['_secure' => $this->isSecure()]);
    }
}