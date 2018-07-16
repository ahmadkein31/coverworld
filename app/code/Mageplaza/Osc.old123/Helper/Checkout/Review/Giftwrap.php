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
namespace Mageplaza\Osc\Helper\Checkout\Review;

use Mageplaza\Osc\Helper\Data as HelperData;
use Mageplaza\Osc\Helper\Config as HelperConfig;
use Mageplaza\Osc\Model\System\Config\Source\Giftwrap as SourceGiftwrap;
use Magento\Backend\Model\Session\Quote;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Giftwrap extends AbstractHelper
{
    /**
     * @var Config
     */
    protected $_helperConfig;
    protected $_helperData;

    /**
     * @var StoreManagerInterface
     */
    protected $_modelStoreManagerInterface;

    /**
     * @var Quote
     */
    protected $_sessionQuote;

    /**
     * @var Session
     */
    protected $_modelSession;

    public function __construct(Context $context,
                                HelperData $helperData,
                                HelperConfig $helperConfig,
                                StoreManagerInterface $modelStoreManagerInterface,
                                Quote $sessionQuote,
                                Session $modelSession)
    {
        $this->_helperData                 = $helperData;
        $this->_helperConfig               = $helperConfig;
        $this->_modelStoreManagerInterface = $modelStoreManagerInterface;
        $this->_sessionQuote               = $sessionQuote;
        $this->_modelSession               = $modelSession;

        parent::__construct($context);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isEnabled($store = null)
    {
        return $this->_helperConfig->isEnabledGiftWrap($store);
    }

    /**
     * get current checkout quote
     *
     * @return \Magento\Sales\Model\Quote
     */
    public function getQuote()
    {
        if ($this->_modelStoreManagerInterface->getStore()->isAdmin()) {
            return $this->_sessionQuote->getQuote();
        }

        return $this->_modelSession->getQuote();
    }

    public function getGiftWrapAmount($quote = null)
    {
        if (is_null($quote)) {
            $quote = $this->getQuote();
        }
        $items       = $quote->getAllVisibleItems();
        $total_items = 0;
        foreach ($items as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            $total_items += $item->getQty();
        }
        $giftWrapType   = $this->_helperConfig->getGiftWrapType();
        $giftWrapAmount = $this->_helperConfig->getOrderGiftwrapAmount();
        if (!$total_items)
            return 0;
        if ($giftWrapType == SourceGiftwrap::PER_ITEM) {
            $giftWrapAmount *= $total_items;
        }
        $this->_addGiftWrapToItems($quote, $giftWrapAmount / $total_items);

        return $giftWrapAmount;
    }

    protected function _addGiftWrapToItems($quote, $giftWrapBaseAmount)
    {
        $items          = $quote->getAllVisibleItems();
        $giftWrapAmount = $this->_helperData->convertPrice($giftWrapBaseAmount);
        foreach ($items as $item) {
            if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                continue;
            }
            $item->setMcGiftwrapBaseAmount($item->getMcGiftwrapBaseAmount() + $giftWrapBaseAmount);
            $item->setMcGiftwrapAmount($item->getMcGiftwrapAmount() + $giftWrapAmount);
        }
    }
}