<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\Converter;

use MageWorx\SeoXTemplates\Model\Converter;
use MageWorx\SeoXTemplates\Helper\Data as HelperData;

abstract class Category extends Converter
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $resourceCategory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *
     * @param \Magento\Catalog\Model\ResourceModel\Category $resourceCategory
     *
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageWorx\SeoXTemplates\Model\ResourceModel\Category $resourceCategory,
        /* \Magento\Catalog\Model\ResourceModel\Category $resourceCategory, */
        HelperData $helperData
    ) {
        parent::__construct($helperData);
        $this->storeManager     = $storeManager;
        $this->resourceCategory = $resourceCategory;
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

        foreach ($vars as $key => $params) {
            foreach ($params['attributes'] as $attributeCode) {

                switch ($attributeCode) {
                    case 'category':
                        $value = $this->_convertName();
                        break;
                    case 'price':
                    case 'special_price':
                        break;
                    case 'parent_category':
                        $value = $this->_convertParentCategory();
                        break;
                    case 'categories':
                        $value = $this->_convertCategories();
                        break;
                    case 'subcategories':
                        $value = $this->_convertSubCategories();
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
     *
     * @return string
     */
    protected function _convertName()
    {
        return $this->_item->getName();
    }

    /**
     *
     * @return string
     */
    protected function _convertParentCategory()
    {
        $value = '';
        $parentId = $this->_item->getParentId();
        if ($parentId && !$this->isRootCategoryId($parentId)) {
            $value = $this->resourceCategory->getAttributeRawValue(
                $parentId,
                'name',
                $this->storeManager->getStore($this->_item->getStoreId())
            );
        }
        if ( $value == 'Root Catalog' ) {
            $value = '';
        }

        return $value;
    }

    /**
     *
     * @return string
     */
    protected function _convertCategories()
    {
        $value     = '';
        $separator = $this->helperData->getTitleSeparator($this->_item->getStoreId());
        $paths     = explode('/', $this->_item->getPath());
        $paths     = (is_array($paths)) ? array_slice($paths, 1) : $this->_item->getParentCategories();

        if (is_array($paths)) {
            foreach ($paths as $category) {
                $categoryId = is_object($category) ? $category->getId() : $category;

                if ($this->helperData->isCropRootCategory($this->_item->getStoreId())
                    && $this->isRootCategoryId($categoryId)
                ) {
                    continue;
                }

                $partPath = $this->resourceCategory->getAttributeRawValue(
                    $categoryId,
                    'name',
                    $this->storeManager->getStore($this->_item->getStoreId())
                );

                if ($partPath == 'Root Catalog') {
                    continue;
                }

                $path[] = $partPath;
            }

            if (!empty($path) && is_array($path) && count($path) > 0) {
                $path  = array_filter($path);
                $value = join($separator, array_reverse($path));
            }
        }

        return $value;
    }

    /**
     *
     * @return string
     */
    protected function _convertSubCategories()
    {
        $value     = '';
        $childIdsAsString  = $this->_item->getChildren();

        if (!$childIdsAsString) {
            return $value;
        }

        $childIds = explode(',', $childIdsAsString);

        $separator = ', ';
        $names     = [];

        foreach ($childIds as $categoryId) {

            if ($this->helperData->isCropRootCategory($this->_item->getStoreId())
                && $this->isRootCategoryId($categoryId)
            ) {
                continue;
            }

            $partNames = $this->resourceCategory->getAttributeRawValue(
                $categoryId,
                'name',
                $this->storeManager->getStore($this->_item->getStoreId())
            );

            if ($partNames == 'Root Catalog') {
                continue;
            }

            $names[] = $partNames;
            $names = array_filter($names);
        }

        if (!empty($names) && is_array($names)) {
            $names  = array_filter($names);
            $value = join($separator, $names);
        }

        return $value;
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
    protected function _convertAttribute($attributeCode)
    {
        $value = '';
        if ($attribute = $this->_item->getResource()->getAttribute($attributeCode)) {
            $value = $attribute->getSource()->getOptionText($this->_item->getData($attributeCode));
        }
        if (!$value) {
            $value = $this->_item->getData($attributeCode);
        }
        if (is_array($value)) {
            $value = implode(', ', $value);
        }

        return $value;
    }

    /**
     *
     * @param string $convertValue
     * @return string
     */
    protected function _render($convertValue)
    {
        return trim($convertValue);
    }

    /**
     *
     * @param int $id
     * @return boolean
     */
    protected function isRootCategoryId($id)
    {
        return $this->storeManager->getStore($this->_item->getStoreId())->getRootCategoryId() == $id;
    }

}