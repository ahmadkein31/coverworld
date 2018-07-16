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

use Magento\Cms\Model\ResourceModel\Block\Collection;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Result\LayoutFactory;

class Block extends AbstractHelper
{
    /**
     * @var Collection
     */
    protected $_blockCollection;

    /**
     * @var RequestInterface
     */

    /**
     * @var StoreManagerInterface
     */
    protected $_modelStoreManagerInterface;
    protected $_resultLayoutFactory;
    protected $_config;
    const CONFIG_ACTIONS_NODE = 'osc/actions';
    const CONFIG_BLOCKS_NODE = 'osc/blocks';
    const FULL_HANDLE_NAME = 'onestepcheckout_ajax_update';

    /**
     * @var Package
     */

    public function __construct(Context $context,
                                Collection $blockCollection,
                                StoreManagerInterface $modelStoreManagerInterface,
                                LayoutFactory $layoutFactory,
                                Config $helperConfig
    ) {
        parent::__construct($context);
        $this->_blockCollection            = $blockCollection;
        $this->_modelStoreManagerInterface = $modelStoreManagerInterface;
        $this->_resultLayoutFactory        = $layoutFactory;
        $this->_helperConfig               = $helperConfig;
    }

    public function toOptionArray()
    {
        /**
         * Set default
         */
        $optionArray = [
            [
                'label' => __('--Select a CMS Static Blocks--'),
                'value' => null,
            ]
        ];

        if ($blockCollection = $this->getStaticBlockCollection()) {
            foreach ($blockCollection as $block) {
                $optionArray[] = [
                    'value' => $block->getIdentifier(),
                    'label' => $block->getTitle(),
                ];
            }
        }

        return $optionArray;
    }

    public function toArray()
    {
        if ($blockCollection = $this->getStaticBlockCollection()) {
            $array = [];
            foreach ($blockCollection as $block) {
                $array[$block->getIdentifier()] = $block->getIdentifier();
                $array[$block->getTitle()]      = $block->getTitle();
            }
        }

        return $array;
    }

    /**
     * get action node in config.xml
     *
     * @return \Magento\Framework\App\Config\Element
     */
    public function getActionNode()
    {
        $config = $this->_helperConfig->getConfigValue(self::CONFIG_ACTIONS_NODE);
        if (!is_null($config))
            return $config;

        return null;
    }

    protected function _getBlocksNode()
    {
        $config = $this->_helperConfig->getConfigValue(self::CONFIG_BLOCKS_NODE);
        if (!is_null($config))
            return $config;

        return null;
    }

    /**
     * @return array
     */
    public function getBlocksSection()
    {
        $blocks        = (array)$this->_getBlocksNode();
        $blocksSection = [];
        foreach ($blocks as $name => $element) {
            $blocksSection[$name] = $element;
        }

        return $blocksSection;
    }

    public function getStaticBlockCollection()
    {
        $collection = $this->_blockCollection;

        return $collection;
    }

    public function getBlockMapping()
    {
        return (array)$this->getActionNode()->children();
    }

    /**
     * @param null $handle
     * @param null $layout
     */
    public function getActionBlocks($actionName = null, $handleName = null, $layout = null)
    {
        if (!$actionName)
            $actionName = $this->_request->getActionName();
        if (!$handleName)
            $handleName = self::FULL_HANDLE_NAME;
        if (!$layout) {
            $resultLayout = $this->_resultLayoutFactory->create();
            $resultLayout->addHandle($handleName);
            $layout = $resultLayout->getLayout();
        }
        $actionNode     = $this->getActionNode();
        $blockNode      = isset($actionNode[$actionName]) ? $actionNode[$actionName] : array();
        $reloadSections = $this->_getReloadSectionByAction($actionName);
        $blocks         = [];
        foreach ($blockNode as $section => $blockName) {
            $block = $layout->getBlock($blockName);
            if (!in_array($section, $reloadSections))
                continue;
            if ($block) {
                $blocks[$section] = $block->toHtml();
            }
        }

        return $blocks;
    }

    /**
     * get reload section by action
     *
     * @param $action
     * @return mixed
     */
    protected function _getReloadSectionByAction($action)
    {
        $reloadSections = $this->getReloadSection();
        if ($reloadSections[$action])
            return $reloadSections[$action];

        return null;

    }

    /**
     *
     * @return mixed
     */
    public function getReloadSection()
    {
        $reloadSections = [
            'saveAddressTrigger' => ['shipping_method', 'payment_method', 'review_cart', 'review_coupon'],
            'saveShippingMethod' => ['payment_method', 'review_cart', 'review_coupon'],
            'savePayment'        => ['review_cart', 'review_coupon'],
            'ajaxCartItem'       => ['shipping_method', 'payment_method', 'review_cart', 'review_coupon', 'cart_sidebar'],
            'addGiftWrap'        => ['review_cart', 'review_coupon','payment_method'],
            'saveCoupon'         => ['payment_method', 'review_cart']
        ];
        $container      = new DataObject(
            [
                'sections' => $reloadSections
            ]
        );
        $this->_eventManager->dispatch('osc_get_reload_section_after', [
            'container' => $container
        ]);

        return $container->getSections();
    }

}