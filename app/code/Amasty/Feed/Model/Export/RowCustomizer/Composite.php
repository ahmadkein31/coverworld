<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Feed\Model\Export\RowCustomizer;

use Magento\Framework\ObjectManagerInterface;

class Composite extends \Magento\CatalogImportExport\Model\Export\RowCustomizer\Composite
{
    protected $_storeId;
    protected $_skipRelationCustomizer = false;
    protected $_objects = array();

    public function init(\Amasty\Feed\Model\Export\Product $exportProduct)
    {
        if (!$exportProduct->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_IMAGE_ATTRIBUTE))
        {
            unset($this->customizers['imagesData']);
        }

        if (!$exportProduct->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_GALLERY_ATTRIBUTE))
        {
            unset($this->customizers['galleryData']);
        }

        if (!$exportProduct->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_CATEGORY_ATTRIBUTE) &&
            !$exportProduct->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_CATEGORY_PATH_ATTRIBUTE) &&
            !$exportProduct->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE) &&
            !$exportProduct->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE)){
            unset($this->customizers['categoryData']);
        }

        if (!$exportProduct->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_URL_ATTRIBUTE))
        {
            unset($this->customizers['urlData']);
        }

        if (!$exportProduct->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_PRICE_ATTRIBUTE))
        {
            unset($this->customizers['priceData']);
        }

        if (!$exportProduct->hasParentAttributes())
        {
            unset($this->customizers['relationData']);
        }
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
    }

    public function skipRelationCustomizer($skipRelationCustomizer)
    {
        $this->_skipRelationCustomizer = $skipRelationCustomizer;
    }

    protected function _getObject($className)
    {
        if (!isset($this->_objects[$className])){
            $this->_objects[$className] = $this->objectManager->create($className);
        }
        return $this->_objects[$className];
    }

    public function prepareData($collection, $productIds)
    {
        foreach ($this->customizers as $key => $className) {
            if ($this->_skipRelationCustomizer && $key == 'relationData'){
                continue;
            }
            $collection->setStoreId($this->_storeId);
            $this->_getObject($className)->prepareData(clone $collection, $productIds);
        }
    }

    public function addData($dataRow, $productId)
    {
        $dataRow['product_id'] = $productId;

        if (!isset($dataRow['amasty_custom_data'])){
            $dataRow['amasty_custom_data'] = array();
        }

        foreach ($this->customizers  as $key => $className) {
            if ($this->_skipRelationCustomizer && $key == 'relationData'){
                continue;
            }
            $dataRow = $this->_getObject($className)->addData($dataRow, $productId);
        }

        return $dataRow;
    }
}
