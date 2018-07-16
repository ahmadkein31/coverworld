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
namespace Mageplaza\Osc\Block\Plugin\Checkout\Cart;

use Mageplaza\Osc\Block\Plugin\Checkout\AbstractCheckout;

class Sidebar extends AbstractCheckout
{
    /**
     * @param \Magento\Checkout\Block\Onepage\Link $onePageLink
     * @param                                      $result
     * @return string
     */
    public function afterGetCheckoutUrl(\Magento\Checkout\Block\Cart\Sidebar $sidebar, $result)
    {
        return $this->_getCheckoutUrl();
    }
}