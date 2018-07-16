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
namespace Mageplaza\Osc\Block\Container\Shipping\Method;

use Mageplaza\Osc\Block\Container\Shipping\Method;

class Available extends Method
{


    public function getShippingRates()
    {
        if (empty($this->_rates)) {
            $this->getShippingAddress()->collectShippingRates()->save();
            $groups = $this->getShippingAddress()->getGroupedAllShippingRates();
        }

        return $this->_rates = $groups;

    }

    /**
     * @param $carrierCode
     *
     * @return mixed
     */
    public function getCarrierName($carrierCode)
    {
        if ($name = $this->getHelperConfig()->getCarrierName($carrierCode)) {
            return $name;
        }

        return $carrierCode;
    }

    public function isChecked($code)
    {
        if ($currentCode = $this->getShippingAddress()->getShippingMethod()) {
            return $currentCode == $code;
        } else {
            return $code == $this->getHelperConfig()->getDefaultShippingMethod();
        }
    }


}