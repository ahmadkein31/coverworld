<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class Category
{
    protected $_storeManager;
    protected $_export;
    protected $_category;
    protected $_mapping;

    protected $_mappingCategories;
    protected $_mappingData;
    protected $_rowCategories;
    protected $_categoriesPath;
    protected $_categoriesLast;


    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Feed\Model\Export\Product $export,
        \Amasty\Feed\Model\Category $category,
        \Amasty\Feed\Model\Category\Mapping $mapping
    ){
        $this->_storeManager = $storeManager;
        $this->_export = $export;
        $this->_category = $category;
        $this->_mapping = $mapping;
    }
    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->_export->hasAttributes(\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE)
            || $this->_export->hasAttributes(\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE)){

            $this->_mappingCategories = array_merge($this->_export->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE), $this->_export->getAttributesByType(\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE));

            $categoryCollection = $this->_category->getSortedCollection();
            $categoryCollection->addFieldToFilter('code', array(
                'in' => $this->_mappingCategories
            ));

            foreach($categoryCollection as $category){
                $this->_mappingData[$category->getCode()] = array();
                $mappingCollection = $this->_mapping->getCategoriesMappingCollection($category);
                foreach($mappingCollection as $mapping){
                    $this->_mappingData[$category->getCode()][$mapping->getCategoryId()] = $mapping->getVariable();

                }
            }

            $multirawData = $this->_export->getMultirawData();
            $this->_rowCategories = $multirawData['rowCategories'];

            $this->_categoriesPath = $this->_export->getCategoriesPath();
            $this->_categoriesLast = $this->_export->getCategoriesLast();
        }
    }

    /**
     * @inheritdoc
     */
    public function addHeaderColumns($columns)
    {
        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function addData(&$dataRow, $productId)
    {
        $customData = &$dataRow['amasty_custom_data'];

        $customData[\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE] = array();
        $customData[\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE] = array();

        if (is_array($this->_mappingCategories)){
            foreach($this->_mappingCategories as $code){
                if (isset($this->_rowCategories[$productId])){
                    $categories = $this->_rowCategories[$productId];

                    $lastCategoryId = count($categories) > 0 ? $categories[count($categories) - 1] : null;

                    if (isset($this->_categoriesLast[$lastCategoryId]) && is_array($this->_mappingCategories)){

                        $lastCategoryVar = $this->_categoriesLast[$lastCategoryId];

                        $customData[\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE][$code] =
                            isset($this->_mappingData[$code][$lastCategoryId]) ? $this->_mappingData[$code][$lastCategoryId] :
                                $lastCategoryVar;
                    }

                    $categoriesPath = [];

                    foreach ($categories as $categoryId) {
                        if (isset($this->_categoriesPath[$categoryId])){

                            $path = $this->_categoriesPath[$categoryId];
                            foreach($path as $id => $var){
                                $path[$id] = isset($this->_mappingData[$code][$id]) ? $this->_mappingData[$code][$id] :
                                     $var;
                            }

                            $categoriesPath[] = implode('/', $path);

                        }
                    }

                    $customData[\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE][$code] = implode(\Magento\CatalogImportExport\Model\Import\Product::PSEUDO_MULTI_LINE_SEPARATOR, $categoriesPath);
                }

            }
        }

        return $dataRow;
    }



    /**
     * @inheritdoc
     */
    public function getAdditionalRowsCount($additionalRowsCount, $productId)
    {
        return $additionalRowsCount;
    }
}