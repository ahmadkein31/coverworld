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
namespace Mageplaza\Osc\Block\Container\Auth;

use Mageplaza\Osc\Block\Container;

class Form extends Container
{
    /**
     * @return bool
     */
    public function canShow()
    {
        if ($this->_customerSession->isLoggedIn()) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->getUrl('onestepcheckout/ajax/login', ['_secure' => $this->isSecure()]);
    }

    /**
     * @return string
     */
    public function getForgotUrl()
    {
        return $this->getUrl('onestepcheckout/ajax/forgotPassword', ['_secure' => $this->isSecure()]);
    }

    public function getPopupEffect()
    {
        return $this->_helperConfig->getPopupEffect();
    }
}