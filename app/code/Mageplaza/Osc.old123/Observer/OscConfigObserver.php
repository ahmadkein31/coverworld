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
namespace Mageplaza\Osc\Observer;

use Magento\Config\Model\ResourceModel\Config as ModelConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\GiftMessage\Helper\Message;
use Mageplaza\Osc\Helper\Config as HelperConfig;

/**
 * Class OscConfigObserver
 * @package Mageplaza\Osc\Observer
 */
class OscConfigObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var HelperConfig
     */
    protected $_helperConfig;

    /**
     * @var ModelConfig
     */
    protected $_modelConfig;

    /**
     * @param \Mageplaza\Osc\Helper\Config $helperConfig
     * @param \Magento\Config\Model\ResourceModel\Config $modelConfig
     */
    public function __construct(
        HelperConfig $helperConfig,
        ModelConfig $modelConfig
    ) {
        $this->_helperConfig = $helperConfig;
        $this->_modelConfig  = $modelConfig;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $scopeId       = 0;
        $isGiftMessage = !$this->_helperConfig->isDisabledGiftMessage();
        $isEnableTOC   = ($this->_helperConfig->disabledPaymentTOC() || $this->_helperConfig->disabledReviewTOC());
        $this->_modelConfig
            ->saveConfig(
                Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER,
                $isGiftMessage,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $scopeId
            )->saveConfig(
                'checkout/options/enable_agreements',
                $isEnableTOC,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
                $scopeId
            );
    }
}
