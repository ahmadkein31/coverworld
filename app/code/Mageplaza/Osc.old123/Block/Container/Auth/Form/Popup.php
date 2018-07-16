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
namespace Mageplaza\Osc\Block\Container\Auth\Form;

use Mageplaza\Osc\Block\Container\Auth\Form;

class Popup extends Form
{
    /**
     * get login popup width
     *
     * @return null||number
     */
    public function getPopupWidth()
    {
        return $this->getHelperConfig()->getLoginPopupWidth();
    }

    /**
     * @return null||numberF
     */
    public function getLoginPopupHeight()
    {
        return $this->getHelperConfig()->getLoginPopupHeight();
    }
}