<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */

namespace Amasty\Promo\Block;

class Add extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Promo\Helper\Data
     */
    protected $promoHelper;
    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
    /**
     * @var \Amasty\Promo\Helper\Config
     */
    private $config;

    /**
     * Add constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Amasty\Promo\Helper\Data $promoHelper
     * @param \Amasty\Promo\Helper\Config $config
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Amasty\Promo\Helper\Data $promoHelper,
        \Amasty\Promo\Helper\Config $config,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->promoHelper = $promoHelper;
        $this->urlHelper = $urlHelper;
        $this->config = $config;
    }

    public function hasItems()
    {
        return (bool)$this->promoHelper->getNewItems();
    }

    public function getMessage()
    {
        $message = $this->_scopeConfig->getValue(
            'ampromo/messages/add_message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $message;
    }

    public function isOpenAutomatically()
    {
        $auto = $this->_scopeConfig->isSetFlag(
            'ampromo/messages/auto_open_popup',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $auto && $this->hasItems();
    }
    
    public function getCurrentBase64Url()
    {
        return $this->urlHelper->getCurrentBase64Url();
    }

    /**
     * @return null
     */
    public function getAvailableProductQty()
    {
        return $this->promoHelper->getAllowedProductQty();
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('amasty_promo/cart/add');
    }

    public function getPopupMode()
    {
        return $this->config->getScopeValue("messages/gift_selection_method");
    }
}
