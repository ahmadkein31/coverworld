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
namespace Mageplaza\Osc\Block\Container\Shipping;

use Mageplaza\Osc\Block\Container;
use Mageplaza\Osc\Block\Context;
use Magento\Tax\Helper\Data as TaxHelper;

class Method extends Container
{
    protected $_rates = null;
    protected $_address = null;
    private $_taxHelper;

    public function __construct(Context $context,
                                TaxHelper $taxHelper,
                                array $data = [])
    {
        parent::__construct($context, $data);
        $this->_taxHelper = $taxHelper;
    }

    /**
     * get Shipping Address From Quote
     *
     * @return Address|null
     */
    public function getShippingAddress()
    {
        if (empty($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }

        return $this->_address;
    }

    public function getShippingPrice($price, $flag)
    {
        return $this->getHelperData()->formatPrice($this->_taxHelper->getShippingPrice($price, $flag, $this->getShippingAddress()), true);
    }

    public function getSaveShippingMethodUrl()
    {
        return $this->getUrl('onestepcheckout/ajax/saveShippingMethod', ['_secure' => $this->isSecure()]);
    }

}