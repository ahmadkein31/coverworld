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
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;

class Giftwrap extends Review
{

    public function canShow()
    {
        if (!$this->getHelperConfig()->isEnabledGiftWrap()) {
            return false;
        }

        return true;
    }

    /**
     *
     * @return string
     */
    public function getAddGiftWrapUrl()
    {
        return $this->getUrl('onestepcheckout/ajax/addGiftWrap', ['_secure' => $this->isSecure()]);
    }

    /**
     * @return mixed
     */
    public function isUsedGiftwrap()
    {
        return $this->_checkoutSession->getData('is_used_giftwrap');
    }

}