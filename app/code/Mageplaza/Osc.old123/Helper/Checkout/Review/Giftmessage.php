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

use Mageplaza\Osc\Helper\Config;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Model\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\Store\Model\StoreManagerInterface;

class Giftmessage extends GiftMessageHelper
{
    /**
     * @var Config
     */
    protected $_helperConfig;

    /**
     * @var Layout
     */
    protected $_layoutFactory;

    /**
     * @var Session
     */
    protected $_checkoutSession;

    public function __construct(Context $context,
                                StoreManagerInterface $storeManager,
                                ProductRepositoryInterface $productRepository,
                                LayoutFactory $layoutFactory,
                                MessageFactory $giftMessageFactory,
                                Escaper $escaper,
                                Config $helperConfig,
                                Session $checkoutSession)
    {
        $this->_helperConfig    = $helperConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_layoutFactory   = $layoutFactory;

        parent::__construct($context, $storeManager, $productRepository, $layoutFactory, $giftMessageFactory, $escaper);
    }

    public function isEnabled($store = null)
    {
        return $this->_helperConfig->isEnabledGiftMessage($store);
    }

    /**
     *
     */
    public function getInline($type, DataObject $entity, $dontDisplayContainer = false)
    {
        if (!$this->skipPage($type) && !$this->isMessagesAllowed($type, $entity)) {
            return '';
        }

        return $this->_layoutFactory->create()->createBlock('Magento\GiftMessage\Block\Message\Inline')
            ->setId('giftmessage_form_' . $this->_nextId++)
            ->setDontDisplayContainer($dontDisplayContainer)
            ->setEntity($entity)
            ->setType($type)
            ->setTemplate('Mageplaza_Osc::container/review/addition/giftmessage/inline.phtml')
            ->setFormData($this->_checkoutSession->getData('osc_form_values'))
            ->setHelperMessage($this)
            ->toHtml();
    }
}