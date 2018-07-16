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

use Mageplaza\Osc\Helper\Data as HelperData;
use Magento\Checkout\Block\Cart;
use Magento\Store\Model\StoreManagerInterface;

class Item extends Cart
{
    /**
     * @var StoreManagerInterface
     */
    protected $_modelStoreManagerInterface;
    protected $_helperData;
    protected $_helperConfig;

    /**
     * @var Session
     */

    /**
     * @var HelperData
     */

    public function __construct(
        \Mageplaza\Osc\Block\Context $context,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrlBuilder,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $customerSession = $context->getObjectManager()->get('\Magento\Customer\Model\Session');
        $checkoutSession = $context->getObjectManager()->get('\Magento\Checkout\Model\Session');
        parent::__construct(
            $context, $customerSession, $checkoutSession, $catalogUrlBuilder, $cartHelper, $httpContext, $data
        );
        $this->_modelStoreManagerInterface = $context->getStoreManager();
        $this->_helperData                 = $context->getHelperData();
        $this->_helperConfig               = $context->getHelperConfig();

    }


    public function getHelperConfig()
    {
        return $this->_helperConfig;
    }

    public function getHelperData()
    {
        return $this->_helperData;
    }

    /**
     * get ajax cart url: (remove item, add a item, sub a item )
     *
     * @return string
     */
    public function getAjaxCartItemUrl()
    {
        $isSecure = $this->_modelStoreManagerInterface->getStore()->isCurrentlySecure();

        return $this->getUrl('onestepcheckout/ajax/ajaxCartItem', ['_secure' => $isSecure]);
    }

    /**
     * @return mixed
     */
    public function getGrandTotal()
    {
        $quote = $this->_checkoutSession->getQuote();

        return $this->_helperData->getGrandTotal($quote);
    }
}