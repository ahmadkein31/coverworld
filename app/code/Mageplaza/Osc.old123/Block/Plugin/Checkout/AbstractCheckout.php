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
namespace Mageplaza\Osc\Block\Plugin\Checkout;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use Mageplaza\Osc\Helper\Config as HelperConfig;

class AbstractCheckout extends Template
{
    public function __construct(
        Context $context,
        HelperConfig $systemConfig,
        array $data = []

    ) {
        $this->_systemConfig = $systemConfig;
        parent::__construct($context, $data);

    }

    /**
     * Get checkout url
     *
     * @return string
     */
    protected function _getCheckoutUrl()
    {
        return $this->_systemConfig->isEnabled() ?
            $this->getUrl('onestepcheckout') : $this->getUrl('checkout');
    }
}