<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class Image
{
    protected $_storeManager;
    protected $_urlPrefix;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
        $this->_storeManager = $storeManager;
    }
    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        $this->_urlPrefix = $this->_storeManager->getStore($collection->getStoreId())
                                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                                . 'catalog/product';
        return;
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

        $customData[\Amasty\Feed\Model\Export\Product::PREFIX_IMAGE_ATTRIBUTE] = array(
            'thumbnail' => isset($dataRow['thumbnail']) ? $this->_urlPrefix . $dataRow['thumbnail'] : null,
            'image' => isset($dataRow['image']) ? $this->_urlPrefix . $dataRow['image'] : null,
            'small_image' => isset($dataRow['small_image']) ? $this->_urlPrefix . $dataRow['small_image'] : null,
        );

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