<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\Converter;

use MageWorx\SeoXTemplates\Model\Converter;
use Magento\Framework\Pricing\Helper\Data as HelperPrice;
use MageWorx\SeoXTemplates\Helper\Data as HelperData;
use Magento\Tax\Helper\Data as HelperTax;

abstract class Product extends Converter
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceProduct;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @var HelperTax
     */
    protected $helperTax;

    /**
     *
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceProduct
     * @param \MageWorx\SeoXTemplates\Model\Converter\HelperData $helperData
     * @param HelperPrice $helperPrice
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        HelperData $helperData,
        HelperPrice $helperPrice,
        HelperTax $helperTax
    ) {
        parent::__construct($helperData);
        $this->helperPrice     = $helperPrice;
        $this->storeManager    = $storeManager;
        $this->resourceProduct = $resourceProduct;
        $this->helperTax       = $helperTax;
    }

    /**
     * Returns price converted to current currency rate
     *
     * @param float $price
     * @return float
     */
    public function getCurrencyPrice($price)
    {
        $store = $this->_item->getStoreId();
        return $this->pricingHelper->currencyByStore($price, $store, false);
    }

    /**
     * Retrive converted string by template code
     *
     * @param array $vars
     * @param string $templateCode
     * @return string
     */
    protected function __convert($vars, $templateCode)
    {
        $convertValue = $templateCode;
        $includingTax = $this->displayPriceIncludingTax($this->_item->getStoreId());

        foreach ( $vars as $key => $params ) {
            foreach ( $params['attributes'] as $attributeCode ) {
                switch ($attributeCode) {
                    case 'name':
                        $value = $this->_convertName($attributeCode);
                        break;
                    case 'category':
                        $value = $this->_convertCategory();
                        break;
                    case 'categories':
                        $value = $this->_convertCategories();
                        break;
                    case 'store_view_name':
                        $value = $this->_convertStoreViewName();
                        break;
                    case 'store_name':
                        $value = $this->_convertStoreName();
                        break;
                    case 'website_name':
                        $value = $this->_convertWebsiteName();
                        break;
                    case 'price':
                        $value = $this->_convertPrice($includingTax);
                        break;
                    case 'special_price':
                        $value = $this->_convertSpecialPrice($includingTax);
                        break;
                    default:
                        $value = $this->_convertAttribute($attributeCode);
                        break;
                }

                if ($value) {
                    $value = $params['prefix'] . $value . $params['suffix'];
                    break;
                }
            }

            $convertValue = str_replace($key, $value, $convertValue);
        }

        return $this->_render($convertValue);
    }

    /**
     * Retrive converted string
     *
     * @param string $attribute
     * @return string
     */
    protected function _convertName($attribute)
    {
        return $this->_convertAttribute($attribute);
    }

    /**
     *
     * @return string
     */
    protected function _convertStoreViewName()
    {
        return $this->storeManager->getStore($this->_item->getStoreId())->getName();
    }

    /**
     *
     * @return string
     */
    protected function _convertStoreName()
    {
        return $this->storeManager->getStore($this->_item->getStoreId())->getGroup()->getName();
    }

    /**
     *
     * @return string
     */
    protected function _convertWebsiteName()
    {
        return $this->storeManager->getStore($this->_item->getStoreId())->getWebsite()->getName();
    }

    /**
     *
     * @return string
     */
    protected function _convertCategory()
    {
        return '[category]';
    }

    /**
     *
     * @return string
     */
    protected function _convertCategories()
    {
        return '[categories]';
    }

    /**
     * Retrive converted string
     * @param int $includingTax
     * @return string
     */
    protected function _convertPrice($includingTax)
    {
        return $this->_item->getFinalPrice();

        if ($this->_item->getTypeId() == 'bundle') {
            $value = $this->_convertPriceForBundle($includingTax);
        }
        elseif ($this->_item->getTypeId() == 'grouped') {
            $value = $this->_convertPriceForGrouped($includingTax);
        }
        else {
            $value = $this->_convertPriceByDefault($includingTax);
        }
        return $value;
    }

    /**
     * Retrive converted string
     * @return string
     */
    protected function _convertPriceForBundle()
    {
        return false;
    }

    /**
     * Retrive converted string
     * @param int $includingTax
     * @return string
     */
    protected function _convertPriceForGrouped($includingTax)
    {
        return false;
    }

    /**
     * Retrive converted string
     *
     * @param int $includingTax
     * @return string
     */
    protected function _convertSpecialPrice($includingTax)
    {
        return false;
    }

    /**
     * Retrive converted string
     * @param string $attributeCode
     * @return string
     */
    protected function _convertAttribute($attributeCode)
    {
        $tempValue = '';
        $value     = $this->_item->getData($attributeCode);
        if ($_attr     = $this->_item->getResource()->getAttribute($attributeCode)) {
            $_attr->setStoreId($this->_item->getStoreId());
            if ($_attr->usesSource()) {
                $tempValue = $_attr->setStoreId($this->_item->getStoreId())->getSource()->getOptionText($this->_item->getData($attributeCode));
            }
        }
        if ($tempValue) {
            $value = $tempValue;
        }
        if (!$value) {
            if ($this->_item->getTypeId() == 'configurable') {
                $productAttributeOptions = $this->_item->getTypeInstance(true)->getConfigurableAttributesAsArray($this->_item);
                $attributeOptions        = array();
                foreach ( $productAttributeOptions as $productAttribute ) {
                    if ($productAttribute['attribute_code'] == $attributeCode) {
                        foreach ( $productAttribute['values'] as $attribute ) {
                            $attributeOptions[] = $attribute['store_label'];
                        }
                    }
                }
                if (count($attributeOptions) == 1) {
                    $value = array_shift($attributeOptions);
                }
            }
            else {
                $value = $this->_item->getData($attributeCode);
            }
        }
        return is_array($value) ? implode(', ', $value) : $value;
    }

    /**
     *
     * @param string $converValue
     * @return string
     */
    protected function _render($converValue)
    {
        return trim($converValue);
    }

    /**
     * Check if we have display in catalog prices including tax
     *
     * @param int|Store
     * @return bool
     */
    public function displayPriceIncludingTax($store)
    {
        return $this->getPriceDisplayType($store) == \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * Get product price display type
     *  1 - Excluding tax
     *  2 - Including tax
     *  3 - Both
     *
     * @param  int|Store $store
     * @return int
     */
    public function getPriceDisplayType($store)
    {
        return $this->helperTax->getPriceDisplayType($store);
    }

}
