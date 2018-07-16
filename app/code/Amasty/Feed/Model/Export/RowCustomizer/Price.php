<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Model\Export\RowCustomizer;

use Magento\CatalogImportExport\Model\Export\RowCustomizerInterface;

class Price
{
    protected $_prices = array();
    protected $_storeManager;
    protected $_export;
    protected $_calculationCollectionFactory;
    protected $_objectConverter;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Feed\Model\Export\Product $export,
        \Magento\Tax\Model\ResourceModel\Calculation\CollectionFactory $calculationCollectionFactory,
        \Magento\Framework\Convert\DataObject $objectConverter
    ){
        $this->_storeManager = $storeManager;
        $this->_export = $export;
        $this->_calculationCollectionFactory = $calculationCollectionFactory;
        $this->_objectConverter = $objectConverter;
    }
    /**
     * @inheritdoc
     */
    public function prepareData($collection, $productIds)
    {
        if ($this->_export->hasAttributes(\Amasty\Feed\Model\Export\Product::PREFIX_PRICE_ATTRIBUTE)) {

            $collection->applyFrontendPriceLimitations();

            $calculationCollection = $this->_calculationCollectionFactory->create()
                ->join(
                    ['rate_table' => 'tax_calculation_rate'],
                    'rate_table.tax_calculation_rate_id = main_table.tax_calculation_rate_id',
                    ['tax_percents' => 'rate_table.rate']
                );

            $calculationCollection->getSelect()->group('main_table.product_tax_class_id');

            $taxClassToPercents = $this->_objectConverter->toOptionHash(
                $calculationCollection->getItems(),
                'product_tax_class_id',
                'tax_percents'
            );

            foreach ($collection as &$item) {

                $this->_prices[$item['entity_id']] = array(
                    'final_price' => $item['final_price'],
                    'price' => $item['price'],
                    'min_price' => $item['min_price'],
                    'max_price' => $item['max_price'],
                    'tax_price' => array_key_exists($item['tax_class_id'], $taxClassToPercents) ?
                        ($item['price'] + $item['price'] * $taxClassToPercents[$item['tax_class_id']] / 100)
                        : $item['price']
                );
            }
        }

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

        $customData[\Amasty\Feed\Model\Export\Product::PREFIX_PRICE_ATTRIBUTE] = isset($this->_prices[$productId]) ? $this->_prices[$productId] : array();

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