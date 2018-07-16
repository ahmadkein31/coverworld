<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\Observer;

use Magento\Store\Model\StoreManagerInterface;
use MageWorx\SeoXTemplates\Helper\Data as HelperData;
use Magento\Catalog\Model\ResourceModel\Category as ResourceCategory;
use Magento\Framework\App\RequestInterface;

/**
 * Observer class for product seo name
 */
class ConvertProductCategories implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageWorx\SeoXTemplates\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category
     */
    protected $resourceCategory;

    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     *
     * @param StoreManagerInterface $storeManager
     * @param HelperData $helperData
     * @param ResourceCategory $resourceCategory
     * @param RequestInterface $request
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        HelperData $helperData,
        ResourceCategory $resourceCategory,
        RequestInterface $request
    ) {
        $this->storeManager     = $storeManager;
        $this->helperData       = $helperData;
        $this->resourceCategory = $resourceCategory;
        $this->request          = $request;
    }

    /**
     * Convert properties of the product that contain [category] and [categories]
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product = $observer->getData('product');

        if ($this->_out($product)) {
            return;
        }

        $properties = array();

        $properties['metaTitle']        = $product->getMetaTitle();
        $properties['metaDescription']  = $product->getMetaDescription();
        $properties['metaKeyword']      = $product->getMetaKeyword();
        $properties['description']      = $product->getDescription();
        $properties['shortDescription'] = $product->getShortDescription();

        $categoryFlag   = false;
        $categoriesFlag = false;

        foreach ($properties as $property) {
            if (strpos($property, '[category]') !== false) {
                $categoryFlag = true;
            }
            if (strpos($property, '[categories]') !== false) {
                $categoryFlag   = true;
                $categoriesFlag = true;
                break;
            }
        }

        if (!$categoryFlag && !$categoriesFlag) {
            return;
        }

        $catData  = $this->_getCatData($categoriesFlag);
        $catArray = ['[category]', '[categories]'];

        $product->setMetaTitle(str_ireplace($catArray, $catData, $product->getMetaTitle()));
        $product->setMetaDescription(str_ireplace($catArray, $catData, $product->getMetaDescription()));
        $product->setMetaKeyword(str_ireplace($catArray, $catData, $product->getMetaKeyword()));
        $product->setDescription(str_ireplace($catArray, $catData, $product->getDescription()));
        $product->setShortDescription(str_ireplace($catArray, $catData, $product->getShortDescription()));
    }

    /**
     *
     * @param Mage_Catalog_Model_Product $product
     * @param bool $categoriesFlag
     * @return array
     */
    protected function _getCatData($categoriesFlag)
    {
        $params = $this->request->getParams();

        if (empty($params['category'])) {
            return $this->_getDefaultCatData();
        }

        if ($categoriesFlag) {

            $category = '';
            $categories = '';

            $path = $this->resourceCategory->getAttributeRawValue(
                $params['category'],
                'path',
                $this->storeManager->getStore()
            );
            $pathArray = array_reverse(explode('/', $path));
            $names     = array();
            foreach ($pathArray as $id) {

                if ($this->helperData->isCropRootCategory() && $this->isRootCategoryId($id)) {
                    continue;
                }

                $value = $this->resourceCategory->getAttributeRawValue(
                    $id,
                    'name',
                    $this->storeManager->getStore()
                );
                if ($value && $value != 'Root Catalog') {
                    $names[$id] = $value;
                }
            }

            $categories = trim(implode(', ', $names));
            $category   = array_shift($names);
            return array('category' => $category, 'categories' => $categories);
        }
        else {
            $name = '';
            if ($this->helperData->isCropRootCategory() && $this->isRootCategoryId($params['category'])) {
                $category = '';
            }
            else {
                $name = $this->resourceCategory->getAttributeRawValue(
                    $params['category'],
                    'name',
                    $this->storeManager->getStore()
                );
                if ($name == 'Root Catalog') {
                    $name = '';
                }
            }
            return array('category' => $name, 'categories' => '');
        }
    }

    /**
     *
     * @return array
     */
    protected function _getDefaultCatData()
    {
        return array('category' => '', 'categories' => '');
    }

    /**
     * Check if go out
     *
     * @param $product
     * @return boolean
     */
    protected function _out($product)
    {
        if (!is_object($product)) {
            return true;
        }

        if (!in_array($this->request->getFullActionName(), $this->_getAvailableActions())) {
            return true;
        }

        if ($product->getId() != $this->request->getParam('id')) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param int $id
     * @return boolean
     */
    protected function isRootCategoryId($id)
    {
        return $this->storeManager->getStore()->getRootCategoryId() == $id;
    }

    /**
     * Retrive list of available actions
     *
     * @return array
     */
    protected function _getAvailableActions()
    {
        return array('catalog_product_view');
    }

}
