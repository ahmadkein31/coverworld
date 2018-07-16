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
namespace Mageplaza\Osc\Helper;

use Magento\Checkout\Helper\Data as HelperData;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData as AbstractHelper;

/**
 * Class Data
 * @package Mageplaza\Osc\Helper
 */
class Data extends AbstractHelper
{
	/**
	 * @type \Magento\Checkout\Helper\Data
	 */
	protected $_helperData;

	/**
	 * @type \Mageplaza\Osc\Helper\Config
	 */
	protected $_helperConfig;

	/**
	 * @type \Magento\Framework\Pricing\PriceCurrencyInterface
	 */
	protected $_priceCurrency;

	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Checkout\Helper\Data $helperData
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Mageplaza\Osc\Helper\Config $helperConfig
	 * @param \Magento\Framework\ObjectManagerInterface $objectManager
	 * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
	 */
	public function __construct(
		Context $context,
		HelperData $helperData,
		StoreManagerInterface $storeManager,
		Config $helperConfig,
		ObjectManagerInterface $objectManager,
		PriceCurrencyInterface $priceCurrency
	)
	{
		$this->_helperData   = $helperData;
		$this->_helperConfig = $helperConfig;
		$this->_priceCurrency   = $priceCurrency;

		parent::__construct($context, $objectManager, $storeManager);
	}

	/**
	 * @return \Mageplaza\Osc\Helper\Config
	 */
	public function getConfig()
	{
		return $this->_helperConfig;
	}

	/**
	 * @param null $store
	 * @return bool
	 */
	public function isEnabled($store = null)
	{
		return $this->getConfig()->isEnabled($store);
	}

	/**
	 * @param $amount
	 * @param null $store
	 * @return float
	 */
	public function convertPrice($amount, $store = null)
	{
		return $this->_priceCurrency->convert($amount, $store);
	}

	/**
	 * @param $quote
	 * @return float|int
	 */
	public function calculateGiftWrapAmount($quote)
	{
		$baseOscGiftWrapAmount = $this->getConfig()->getOrderGiftwrapAmount();
		if ($baseOscGiftWrapAmount < 0.0001) {
			return 0;
		}

		$giftWrapType = $this->getConfig()->getGiftWrapType();
		if ($giftWrapType == \Mageplaza\Osc\Model\System\Config\Source\Giftwrap::PER_ITEM) {
			$giftWrapBaseAmount    = $baseOscGiftWrapAmount;
			$baseOscGiftWrapAmount = 0;
			foreach ($quote->getAllVisibleItems() as $item) {
				if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
					continue;
				}
				$baseOscGiftWrapAmount += $giftWrapBaseAmount * $item->getQty();
			}
		}

		return $this->convertPrice($baseOscGiftWrapAmount, $quote->getStore());
	}
}
