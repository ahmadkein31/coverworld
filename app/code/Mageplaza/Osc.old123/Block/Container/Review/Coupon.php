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
namespace Mageplaza\Osc\Block\Container\Review;

use Mageplaza\Osc\Block\Container\Review;

class Coupon extends Review
{
    
    public function getCouponCode()
    {
        return $this->getQuote()->getCouponCode();
    }

    /**
     * @return mixed
     */
    public function getApplyCouponAjaxUrl()
    {
        return $this->getUrl('onestepcheckout/ajax/saveCoupon', ['_secure' => $this->isSecure()]);
    }

    /**
     * enable gift message or not
     *
     */
    public function canShow()
    {
        return $this->getHelperConfig()->isCoupon();
    }

    /**
     * @return mixed
     */
    public function getShowApplyButton()
    {
        return $this->getHelperConfig()->isApplyCouponButton();
    }

}