<?php

/**
 * Copyright ? 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageplaza\Osc\Observer;

use Magento\GiftMessage\Helper\Message;
use Mageplaza\Osc\Helper\Config as HelperConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Config\Model\ResourceModel\Config as ModelConfig;

class GiftMessage implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     */
    protected $_helperConfig;
    protected $_modelConfig;

    /**
     * GiftMessageConfigObserver constructor.
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
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
        $isGiftMessage = $this->_helperConfig->isEnabledGiftMessage();
        $this->_modelConfig->saveConfig(
            Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER,
            $isGiftMessage,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId
        );
        $this->_modelConfig->saveConfig(
            Message::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            $isGiftMessage,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $scopeId
        );
    }
}
