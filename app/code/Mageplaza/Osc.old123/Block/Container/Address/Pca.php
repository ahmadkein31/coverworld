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
namespace Mageplaza\Osc\Block\Container\Address;

use Mageplaza\Osc\Block\Container\Address;

class Pca extends Address
{
    public function isEnabled($store = null)
    {
        return $this->getHelperConfig()->isEnabledAddressByCountry($store);
    }

    public function getPcaWebsiteKey($store = null)
    {
        return $this->getHelperConfig()->getPcaWebsiteKey($store);
    }

    public function getPcaCountryLookup($store = null)
    {
        return $this->getHelperConfig()->getPcaCountryLookup($store);
    }


}