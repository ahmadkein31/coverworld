<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

/**
 * TODO: map 'custom_options' field.
 *
 * @see \Magento\CatalogImportExport\Model\Import\Product\Option::_getMultiRowFormat()
 *
 * TODO: map stock field.
 * @see \Magento\CatalogImportExport\Model\Import\Product::_saveStockItem()
 */

namespace Firebear\ImportExport\Model\Import;

use Magento\CatalogImportExport\Model\Import\Product\TaxClassProcessor;
use Magento\Framework\Stdlib\DateTime;
use Magento\CatalogImportExport\Model\Import\Product as MagentoProduct;
use Magento\ImportExport\Model\Import;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface as ValidatorInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Magento\Framework\App\Filesystem\DirectoryList;

class Product extends MagentoProduct
{
    use \Firebear\ImportExport\Traits\General;

    protected $addFields = [
        'manage_stock',
        'use_config_manage_stock',
        'qty',
        'min_qty',
        'use_config_min_qty',
        'min_sale_qty',
        'use_config_min_sale_qty',
        'max_sale_qty',
        'use_config_max_sale_qty',
        'is_qty_decimal',
        'backorders',
        'use_config_backorders',
        'notify_stock_qty',
        'use_config_notify_stock_qty',
        'enable_qty_increments',
        'use_config_enable_qty_inc',
        'qty_increments',
        'use_config_qty_increments',
        'is_in_stock',
        'low_stock_date',
        'stock_status_changed_auto',
        'is_decimal_divided',
        'has_options'
    ];
    /**
     * Default website id
     */
    const DEFAULT_WEBSITE_ID = 1;

    /**
     * Used when create new attributes in column name
     */
    const ATTRIBUTE_SET_GROUP = 'attribute_set_group';

    /**
     * Attribute sets column name
     */
    const ATTRIBUTE_SET_COLUMN = 'attribute_set';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Firebear\ImportExport\Helper\Data
     */
    protected $helper;

    /**
     * @var \Firebear\ImportExport\Helper\Additional
     */
    protected $additional;

    /**
     * @var \Firebear\ImportExport\Model\Source\Type\AbstractType
     */
    protected $sourceType;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $eavEntityFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var array
     */
    protected $_attributeSetGroupCache;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    protected $_debugMode;

    /**
     * @var \Firebear\ImportExport\Model\Source\Import\Config
     */
    protected $fireImportConfig;

    protected $duplicateFields = ['sku', 'scope', 'url_key'];

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * Product constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Firebear\ImportExport\Helper\Data $helper
     * @param \Firebear\ImportExport\Helper\Additional $additional
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Eav\Model\Config $config
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param Import\Config $importConfig
     * @param \Firebear\ImportExport\Model\Source\Import\Config $fireImportConfig
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory
     * @param MagentoProduct\OptionFactory $optionFactory
     * @param Product\OptionFactory $fireOptionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory
     * @param MagentoProduct\Type\Factory $productTypeFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory
     * @param \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory
     * @param \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac
     * @param DateTime\TimezoneInterface $localeDate
     * @param DateTime $dateTime
     * @param LoggerInterface $logger
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param MagentoProduct\StoreResolver $storeResolver
     * @param MagentoProduct\SkuProcessor $skuProcessor
     * @param MagentoProduct\CategoryProcessor $categoryProcessor
     * @param MagentoProduct\Validator $validator
     * @param ObjectRelationProcessor $objectRelationProcessor
     * @param TransactionManagerInterface $transactionManager
     * @param TaxClassProcessor $taxClassProcessor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param ConsoleOutput $output
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Firebear\ImportExport\Helper\Data $helper,
        \Firebear\ImportExport\Helper\Additional $additional,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Eav\Model\Config $config,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\Spi\StockStateProviderInterface $stockStateProvider,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\ImportExport\Model\Import\Config $importConfig,
        \Firebear\ImportExport\Model\Source\Import\Config $fireImportConfig,
        \Magento\CatalogImportExport\Model\Import\Proxy\Product\ResourceModelFactory $resourceFactory,
        \Magento\CatalogImportExport\Model\Import\Product\OptionFactory $optionFactory,
        \Firebear\ImportExport\Model\Import\Product\OptionFactory $fireOptionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setColFactory,
        \Magento\CatalogImportExport\Model\Import\Product\Type\Factory $productTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Product\LinkFactory $linkFactory,
        \Magento\CatalogImportExport\Model\Import\Proxy\ProductFactory $proxyProdFactory,
        \Magento\CatalogImportExport\Model\Import\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockResItemFac,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        DateTime $dateTime,
        LoggerInterface $logger,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\CatalogImportExport\Model\Import\Product\StoreResolver $storeResolver,
        \Magento\CatalogImportExport\Model\Import\Product\SkuProcessor $skuProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\CategoryProcessor $categoryProcessor,
        \Magento\CatalogImportExport\Model\Import\Product\Validator $validator,
        ObjectRelationProcessor $objectRelationProcessor,
        TransactionManagerInterface $transactionManager,
        TaxClassProcessor $taxClassProcessor,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\Url $productUrl,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Catalog\Helper\Product $productHelper,
        \Symfony\Component\Console\Output\ConsoleOutput $output,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        array $data = []
    ) {
        $this->output = $output;
        $this->request = $request;
        $this->helper = $helper;
        $this->attributeFactory = $attributeFactory;
        $this->eavEntityFactory = $eavEntityFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->productHelper = $productHelper;
        $this->additional = $additional;
        $this->_logger = $logger;
        $this->fireImportConfig = $fireImportConfig;
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $config,
            $resource,
            $resourceHelper,
            $string,
            $errorAggregator,
            $eventManager,
            $stockRegistry,
            $stockConfiguration,
            $stockStateProvider,
            $catalogData,
            $importConfig,
            $resourceFactory,
            $optionFactory,
            $setColFactory,
            $productTypeFactory,
            $linkFactory,
            $proxyProdFactory,
            $uploaderFactory,
            $filesystem,
            $stockResItemFac,
            $localeDate,
            $dateTime,
            $logger,
            $indexerRegistry,
            $storeResolver,
            $skuProcessor,
            $categoryProcessor,
            $validator,
            $objectRelationProcessor,
            $transactionManager,
            $taxClassProcessor,
            $scopeConfig,
            $productUrl,
            $data
        );
        $this->_optionEntity = isset(
            $data['option_entity']
        )
            ? $data['option_entity']
            : $fireOptionFactory->create(
                ['data' => ['product_entity' => $this]]
            );
        $this->_debugMode = $helper->getDebugMode();
        $this->productMetadata = $productMetadata;
    }

    /**
     * Initialize source type model
     *
     * @param $type
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initSourceType($type)
    {
        if (!$this->sourceType) {
            $this->sourceType = $this->additional->getSourceModelByType($type);
            $this->sourceType->setData($this->_parameters);
        }
    }

    /**
     * import product data
     */
    public function importData()
    {
        $this->_validatedRows = null;

        if (Import::BEHAVIOR_REPLACE == $this->getBehavior()) {
            $this->_replaceFlag = true;
            $this->replaceProducts();
        } elseif (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->_deleteProducts();
        } else {
            $this->saveProductsData();
        }
        $this->_eventManager->dispatch('catalog_product_import_finish_before', ['adapter' => $this]);

        return true;
    }

    /**
     * Replace imported products.
     *
     * @return $this
     */
    protected function replaceProducts()
    {
        $this->deleteProductsForReplacement();
        $this->_oldSku = $this->skuProcessor->reloadOldSkus()->getOldSkus();
        $this->_validatedRows = null;
        $this->setParameters(
            array_merge(
                $this->getParameters(),
                ['behavior' => Import::BEHAVIOR_APPEND]
            )
        );
        $this->saveProductsData();

        return $this;
    }

    /**
     * Save products data.
     *
     * @return $this
     */
    protected function saveProductsData()
    {
        $this->saveProducts();
        foreach ($this->_productTypeModels as $productTypeModel) {
            $productTypeModel->saveData();
        }
        $this->_saveLinks();
        $this->_saveStockItem();
        if ($this->_replaceFlag) {
            $this->getOptionEntity()->clearProductsSkuToId();
        }
        $this->getOptionEntity()->importData();

        return $this;
    }

    /**
     * Gather and save information about product entities.
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function saveProducts()
    {
        /** @var $resource \Magento\CatalogImportExport\Model\Import\Proxy\Product\Resource */
        if (isset($this->_parameters['import_source']) && $this->_parameters['import_source'] != 'file') {
            $this->_initSourceType($this->_parameters['import_source']);
        }
        $isPriceGlobal = $this->_catalogData->isPriceGlobal();
        $productLimit = null;
        $productsQty = null;
        while ($nextBunch = $this->_dataSourceModel->getNextBunch()) {
            $entityRowsIn = $entityRowsUp = [];
            $attributes = [];
            $this->websitesCache = $this->categoriesCache = [];
            $mediaGallery = $uploadedImages = [];
            $tierPrices = [];
            $previousType = $prevAttributeSet = null;
            $existingImages = $this->getExistingImages($nextBunch);
            if ($this->sourceType) {
                $nextBunch = $this->prepareImagesFromSource($nextBunch);
            }
            $prevData = [];
            foreach ($nextBunch as $rowNum => $rowData) {
                if (strpos($this->productMetadata->getVersion(),'2.2') !== false) {
                    $rowData[self::COL_SKU] = strtolower($rowData[self::COL_SKU]);
                }
                if (isset($rowData[self::COL_CATEGORY]) && $rowData[self::COL_CATEGORY]) {
                    $rowData[self::COL_CATEGORY] = str_replace(
                        $this->_parameters['category_levels_separator'],
                        "/",
                        str_replace(
                            $this->_parameters['categories_separator'],
                            ",",
                            $rowData[self::COL_CATEGORY]
                        )
                    );
                }
                if (empty($rowData[self::COL_SKU])) {
                    $rowData = array_merge($prevData, $this->deleteEmpty($rowData));
                } else {
                    $prevData = $rowData;
                }
                $time = explode(" ", microtime());
                $startTime = $time[0] + $time[1];

                $sku = $rowData['sku'];
                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addLogWriteln(__('sku: %1 is not valided', $sku), $this->output, 'info');
                    continue;
                }

                $rowScope = $this->getRowScope($rowData);
                $rowSku = $rowData[self::COL_SKU];
                if (!$rowSku) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                    continue;
                } elseif (self::SCOPE_STORE == $rowScope) {
                    // set necessary data from SCOPE_DEFAULT row
                    $rowData[self::COL_TYPE] = $this->skuProcessor->getNewSku($rowSku)['type_id'];
                    $rowData['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    $rowData[self::COL_ATTR_SET] = $this->skuProcessor->getNewSku($rowSku)['attr_set_code'];
                }

                // Entity phase
                if (!isset($this->_oldSku[$rowSku])) {
                    // new row
                    if (!$productLimit || $productsQty < $productLimit) {
                        if (isset($rowData['has_options'])) {
                            $hasOptions = $rowData['has_options'];
                        } else {
                            $hasOptions = 0;
                        }
                        $entityRowsIn[$rowSku] = [
                            'attribute_set_id' => $this->skuProcessor->getNewSku($rowSku)['attr_set_id'],
                            'type_id' => $this->skuProcessor->getNewSku($rowSku)['type_id'],
                            'sku' => $rowSku,
                            'has_options' => $hasOptions,
                            'created_at' => $this->_localeDate->date()->format(DateTime::DATETIME_PHP_FORMAT),
                            'updated_at' => $this->_localeDate->date()->format(DateTime::DATETIME_PHP_FORMAT),
                        ];
                        $productsQty++;
                    } else {
                        $rowSku = null;
                        // sign for child rows to be skipped
                        $this->getErrorAggregator()->addRowToSkip($rowNum);
                        continue;
                    }
                } else {

                    $array = [
                        'updated_at' => $this->_localeDate->date()->format(DateTime::DATETIME_PHP_FORMAT),
                        'entity_id' => $this->_oldSku[$rowSku]['entity_id'],
                    ];
                    if (strpos($this->productMetadata->getVersion(),'2.2') !== false) {
                        $array['attribute_set_id'] = $this->skuProcessor->getNewSku($rowSku)['attr_set_id'];
                    }
                    // existing row
                    $entityRowsUp[] = $array;

                }

                // Categories phase
                if (!array_key_exists($rowSku, $this->categoriesCache)) {
                    $this->categoriesCache[$rowSku] = [];
                }

                $rowData['rowNum'] = $rowNum;
                $categoryIds = $this->processRowCategories($rowData);
                foreach ($categoryIds as $id) {
                    $this->categoriesCache[$rowSku][$id] = true;
                }
                unset($rowData['rowNum']);

                if (!array_key_exists($rowSku, $this->websitesCache)) {
                    $this->websitesCache[$rowSku] = [];
                }
                // Product-to-Website phase
                if (!empty($rowData[self::COL_PRODUCT_WEBSITES])) {
                    $websiteCodes = explode($this->getMultipleValueSeparator(), $rowData[self::COL_PRODUCT_WEBSITES]);
                    foreach ($websiteCodes as $websiteCode) {
                        $websiteId =
                            $this->storeResolver->getWebsiteCodeToId($websiteCode);
                        $this->websitesCache[$rowSku][$websiteId] = true;
                    }
                }

                // Tier prices phase
                if (!empty($rowData['_tier_price_website'])) {
                    $tierPrices[$rowSku][] = [
                        'all_groups' => $rowData['_tier_price_customer_group'] == self::VALUE_ALL,
                        'customer_group_id' => $rowData['_tier_price_customer_group'] ==
                        self::VALUE_ALL ? 0 : $rowData['_tier_price_customer_group'],
                        'qty' => $rowData['_tier_price_qty'],
                        'value' => $rowData['_tier_price_price'],
                        'website_id' => self::VALUE_ALL == $rowData['_tier_price_website'] || $isPriceGlobal
                            ? 0
                            : $this->storeResolver->getWebsiteCodeToId(
                                $rowData['_tier_price_website']
                            ),
                    ];
                }

                if (!$this->validateRow($rowData, $rowNum)) {
                    $this->addLogWriteln(__('sku: %1 is not valided', $sku), $this->output, 'info');
                    continue;
                }
                // Media gallery phase
                $disabledImages = [];
                list($rowImages, $rowLabels) = $this->getImagesFromRow($rowData);
                if (isset($rowData['_media_is_disabled'])) {
                    $disabledImages = array_flip(
                        explode($this->getMultipleValueSeparator(), $rowData['_media_is_disabled'])
                    );
                }
                $rowData[self::COL_MEDIA_IMAGE] = [];
                foreach ($rowImages as $column => $columnImages) {
                    foreach ($columnImages as $position => $columnImage) {
                        if (isset($uploadedImages[$columnImage])) {
                            $uploadedFile = $uploadedImages[$columnImage];
                        } else {
                            $uploadedFile = $this->uploadMediaFiles(trim($columnImage), true);
                            if ($uploadedFile) {
                                $uploadedImages[$columnImage] = $uploadedFile;
                            } else {
                                $this->addRowError(
                                    ValidatorInterface::ERROR_MEDIA_URL_NOT_ACCESSIBLE,
                                    $rowNum,
                                    null,
                                    null,
                                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                                );
                            }
                        }

                        if ($uploadedFile && $column !== self::COL_MEDIA_IMAGE) {
                            $rowData[$column] = $uploadedFile;
                        }

                        $imageNotAssigned = !isset($existingImages[$rowSku][$uploadedFile]);

                        if ($uploadedFile && $imageNotAssigned) {
                            if ($column == self::COL_MEDIA_IMAGE) {
                                $rowData[$column][] = $uploadedFile;
                            }
                            $mediaGallery[$rowSku][] = [
                                'attribute_id' => $this->getMediaGalleryAttributeId(),
                                'label' => isset($rowLabels[$column][$position]) ? $rowLabels[$column][$position] : '',
                                'position' => $position + 1,
                                'disabled' => isset($disabledImages[$columnImage]) ? '1' : '0',
                                'value' => $uploadedFile,
                            ];
                            $existingImages[$rowSku][$uploadedFile] = true;
                        }
                    }
                }

               // if ($this->getErrorAggregator()->hasToBeTerminated()) {
               //     $this->getErrorAggregator()->addRowToSkip($rowNum);
               //     $this->addLogWriteln(__('sku: %1 is not valided', $sku), $this->output, 'info');
               //    continue;
               // }

                // 6. Attributes phase
                $rowStore = (self::SCOPE_STORE == $rowScope)
                    ? $this->storeResolver->getStoreCodeToId($rowData[self::COL_STORE])
                    : 0;
                $productType = isset($rowData[self::COL_TYPE]) ? $rowData[self::COL_TYPE] : null;
                if (!$productType === null) {
                    $previousType = $productType;
                }
                if (isset($rowData[self::COL_ATTR_SET])) {
                    $prevAttributeSet = $rowData[self::COL_ATTR_SET];
                }
                if (self::SCOPE_NULL == $rowScope) {
                    // for multiselect attributes only
                    if (!$prevAttributeSet === null) {
                        $rowData[self::COL_ATTR_SET] = $prevAttributeSet;
                    }
                    if ($productType === null && !$previousType === null) {
                        $productType = $previousType;
                    }
                    if ($productType === null) {
                        continue;
                    }
                }

                $productTypeModel = $this->_productTypeModels[$productType];
                if (!empty($rowData['tax_class_name'])) {
                    $rowData['tax_class_id'] =
                        $this->taxClassProcessor->upsertTaxClass($rowData['tax_class_name'], $productTypeModel);
                }

                if ($this->getBehavior() == Import::BEHAVIOR_APPEND || empty($rowData[self::COL_SKU])
                ) {
                    $rowData = $productTypeModel->clearEmptyData($rowData);
                }

                $createValuesAllowed = (bool)$this->scopeConfig->getValue(
                    \Firebear\ImportExport\Model\Import::CREATE_ATTRIBUTES_CONF_PATH,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

                if ($createValuesAllowed) {
                    $rowData = $this->createAttributeValues(
                        $productTypeModel,
                        $rowData
                    );
                }

                $rowData = $productTypeModel->prepareAttributesWithDefaultValueForSave(
                    $rowData,
                    !isset($this->_oldSku[$rowSku])
                );
                $product = $this->_proxyProdFactory->create(['data' => $rowData]);

                foreach ($rowData as $attrCode => $attrValue) {
                    $attribute = $this->retrieveAttributeByCode($attrCode);

                    if ('multiselect' != $attribute->getFrontendInput() && self::SCOPE_NULL == $rowScope) {
                        // skip attribute processing for SCOPE_NULL rows
                        continue;
                    }
                    $attrId = $attribute->getId();
                    $backModel = $attribute->getBackendModel();
                    $attrTable = $attribute->getBackend()->getTable();
                    $storeIds = [0];

                    if ('datetime' == $attribute->getBackendType() && strtotime($attrValue)) {
                        $attrValue = $this->dateTime->gmDate(
                            'Y-m-d H:i:s',
                            $this->_localeDate->date($attrValue)->getTimestamp()
                        );
                    } elseif ($backModel) {
                        $attribute->getBackend()->beforeSave($product);
                        $attrValue = $product->getData($attribute->getAttributeCode());
                    }
                    if (self::SCOPE_STORE == $rowScope) {
                        if (self::SCOPE_WEBSITE == $attribute->getIsGlobal()) {
                            // check website defaults already set
                            if (!isset($attributes[$attrTable][$rowSku][$attrId][$rowStore])) {
                                $storeIds = $this->storeResolver->getStoreIdToWebsiteStoreIds($rowStore);
                            }
                        } elseif (self::SCOPE_STORE == $attribute->getIsGlobal()) {
                            $storeIds = [$rowStore];
                        }
                        if (!isset($this->_oldSku[$rowSku])) {
                            $storeIds[] = 0;
                        }
                    }
                    foreach ($storeIds as $storeId) {
                        if (!isset($attributes[$attrTable][$rowSku][$attrId][$storeId])) {
                            $attributes[$attrTable][$rowSku][$attrId][$storeId] = $attrValue;
                        }
                    }
                    // restore 'backend_model' to avoid 'default' setting
                    $attribute->setBackendModel($backModel);
                }
                $time = explode(" ", microtime());
                $endTime = $time[0] + $time[1];
                $totalTime = $endTime - $startTime;
                $totalTime = round($totalTime, 5);
                $this->addLogWriteln(__('sku: %1 .... %2s', $sku, $totalTime), $this->output, 'info');
            }

            if (method_exists($this, '_saveProductEntity')) {
                $this->_saveProductEntity(
                    $entityRowsIn,
                    $entityRowsUp
                );
            } else {
                $this->saveProductEntity(
                    $entityRowsIn,
                    $entityRowsUp
                );
            }
            $this->addLogWriteln(__('Imported: %1 rows', count($entityRowsIn)), $this->output, 'info');
            $this->addLogWriteln(__('Updated: %1 rows', count($entityRowsUp)), $this->output, 'info');

            $this->_saveProductWebsites(
                $this->websitesCache
            )->_saveProductCategories(
                $this->categoriesCache
            )->_saveProductTierPrices(
                $tierPrices
            )->_saveMediaGallery(
                $mediaGallery
            )->_saveProductAttributes(
                $attributes
            );
            $this->_eventManager->dispatch(
                'catalog_product_import_bunch_save_after',
                ['adapter' => $this, 'bunch' => $nextBunch]
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function _saveValidatedBunches()
    {
        $source = $this->_getSource();
        $source->rewind();
        $platformModel = null;
        $prevData = [];
        while ($source->valid()) {
            try {
                $rowData = $source->current();
            } catch (\InvalidArgumentException $e) {
                $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                $this->_processedRowsCount++;
                $source->next();
                continue;
            }
            $rowData = $this->customFieldsMapping($rowData);

            if (empty($rowData[self::COL_SKU])) {
                $rowData = array_merge($prevData, $this->deleteEmpty($rowData));
            } else {
                $prevData = $rowData;
            }

            $this->validateRow($rowData, $source->key());
            $source->next();
        }
        $this->checkUrlKeyDuplicates();
        $this->getOptionEntity()->validateAmbiguousData();
        return parent::_saveValidatedBunches();
    }

    /**
     * Import images via initialized source type
     *
     * @param $bunch
     *
     * @return mixed
     */
    protected function prepareImagesFromSource($bunch)
    {
        foreach ($bunch as &$rowData) {
            $rowData = $this->customFieldsMapping($rowData);
            foreach ($this->_imagesArrayKeys as $image) {
                if (empty($rowData[$image])) {
                    continue;
                }
                $dispersionPath =
                    \Magento\Framework\File\Uploader::getDispretionPath($rowData[$image]);
                $importImages = explode($this->getMultipleValueSeparator(), $rowData[$image]);
                $imageArr = [];
                foreach ($importImages as $importImage) {
                    $imageSting = mb_strtolower(
                        $dispersionPath . '/' . preg_replace('/[^a-z0-9\._-]+/i', '', $importImage)
                    );

                    if ($this->sourceType) {
                        $this->sourceType->importImage($importImage, $imageSting);
                    }
                    $imageArr[] = $this->sourceType->getCode() . $imageSting;
                }

                $rowData[$image] = implode($this->getMultipleValueSeparator(), $imageArr);
            }
        }

        return $bunch;
    }

    /**
     * Retrieving images from all columns and rows
     *
     * @param $bunch
     *
     * @return array
     */
    protected function getBunchImages($bunch)
    {
        $allImagesFromBunch = [];
        foreach ($bunch as $rowData) {
            $rowData = $this->customFieldsMapping($rowData);
            foreach ($this->_imagesArrayKeys as $image) {
                if (empty($rowData[$image])) {
                    continue;
                }
                $dispersionPath =
                    \Magento\Framework\File\Uploader::getDispretionPath($rowData[$image]);
                $importImages = explode($this->getMultipleValueSeparator(), $rowData[$image]);
                foreach ($importImages as $importImage) {
                    $imageSting = mb_strtolower(
                        $dispersionPath . '/' . preg_replace('/[^a-z0-9\._-]+/i', '', $importImage)
                    );
                    /**
                     * TODO: check source type 'file'.
                     * Compare code with default Magento\CatalogImportExport\Model\Import\Product
                     */
                    if (isset($this->_parameters['import_source']) && $this->_parameters['import_source'] != 'file') {
                        $allImagesFromBunch[$this->sourceType->getCode() . $imageSting] = $imageSting;
                    } else {
                        $allImagesFromBunch[$importImage] = $imageSting;
                    }
                }
            }
        }

        return $allImagesFromBunch;
    }

    /**
     * Convert attribute string syntax to array.
     *
     * @param $columnData
     *
     * @return array
     * @throws \Exception
     */
    protected function prepareAttributeData($columnData)
    {
        $result = [];
        foreach ($columnData as $field) {
            $field = explode(':', $field);
            if (isset($field[1])) {
                if (preg_match('/^(frontend_label_)[0-9]+/', $field[0])) {
                    $result['frontend_label'][(int)substr($field[0], -1)] = $field[1];
                } else {
                    $result[$field[0]] = $field[1];
                }
            }
        }

        if (!empty($result)) {
            $attributeCode = isset($result['attribute_code']) ? $result['attribute_code'] : null;
            $frontendLabel = $result['frontend_label'][0];
            $attributeCode = $attributeCode ?: $this->generateAttributeCode($frontendLabel);
            $result['attribute_code'] = $attributeCode;

            $entityTypeId = $this->eavEntityFactory->create()->setType(
                \Magento\Catalog\Model\Product::ENTITY
            )->getTypeId();
            $result['entity_type_id'] = $entityTypeId;
            $result['is_user_defined'] = 1;
        }

        return $result;
    }

    /**
     * Generate code from label
     *
     * @param string $label
     *
     * @return string
     */
    protected function generateAttributeCode($label)
    {
        $code = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->productUrl->formatUrlKey($label)
            ),
            0,
            30
        );
        $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(hash("md5", time()), 0, 8));
        }

        return $code;
    }

    /**
     * Custom fields mapping for changed purposes of fields and field names.
     *
     * @param array $rowData
     *
     * @return array
     */
    private function customFieldsMapping($rowData)
    {
        foreach ($this->_fieldsMap as $systemFieldName => $fileFieldName) {
            if (array_key_exists($fileFieldName, $rowData)) {
                $rowData[$systemFieldName] = $rowData[$fileFieldName];
            }
        }

        $rowData = $this->_parseAdditionalAttributes($rowData);
        $rowData = $this->setStockUseConfigFieldsValues($rowData);
        if (array_key_exists('status', $rowData)
            && $rowData['status'] != \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED
        ) {
            if ($rowData['status'] == 'yes') {
                $rowData['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
            } elseif (!empty($rowData['status']) || $this->getRowScope($rowData) == self::SCOPE_DEFAULT) {
                $rowData['status'] = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
            }
        }

        return $rowData;
    }

    /**
     * Parse attributes names and values string to array.
     *
     * @param array $rowData
     *
     * @return array
     */
    private function _parseAdditionalAttributes($rowData)
    {
        if (empty($rowData['additional_attributes'])) {
            return $rowData;
        }

        $valuePairs = explode(
            $this->getMultipleValueSeparator(),
            $rowData['additional_attributes']
        );
        foreach ($valuePairs as $valuePair) {
            $separatorPosition = strpos($valuePair, self::PAIR_NAME_VALUE_SEPARATOR);
            if ($separatorPosition !== false) {
                $key = substr($valuePair, 0, $separatorPosition);
                $value = substr(
                    $valuePair,
                    $separatorPosition + strlen(self::PAIR_NAME_VALUE_SEPARATOR)
                );
                $rowData[$key] = $value === false ? '' : $value;
            }
        }

        return $rowData;
    }

    /**
     * Set values in use_config_ fields.
     *
     * @param array $rowData
     *
     * @return array
     */
    private function setStockUseConfigFieldsValues($rowData)
    {
        $useConfigFields = [];
        foreach ($rowData as $key => $value) {
            if (isset($this->defaultStockData[$key])
                && isset($this->defaultStockData[self::INVENTORY_USE_CONFIG_PREFIX . $key])
                && !empty($value)
            ) {
                $useConfigFields[self::INVENTORY_USE_CONFIG_PREFIX . $key] =
                    ($value == self::INVENTORY_USE_CONFIG) ? 1 : 0;
            }
        }
        $rowData = array_merge($rowData, $useConfigFields);

        return $rowData;
    }

    /**
     * Validate data
     *
     * @return ProcessingErrorAggregatorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateData()
    {
        if (!$this->_dataValidated) {
            $this->getErrorAggregator()->clear();
            // do all permanent columns exist?
            $platformModel = null;
            $absentColumns =
                array_diff($this->_permanentAttributes, $this->getSource()->getColNames());
            $this->addErrors(self::ERROR_CODE_COLUMN_NOT_FOUND, $absentColumns);

            // check attribute columns names validity
            $columnNumber = 0;
            $emptyHeaderColumns = [];
            $invalidColumns = [];
            $invalidAttributes = [];
            foreach ($this->getSource()->getColNames() as $columnName) {
                $this->addLogWriteln(__('Checked column %1', $columnNumber), $this->output);
                $columnNumber++;
                if (!$this->isAttributeParticular($columnName)) {
                    /**
                     * Check syntax when attribute should be created on the fly
                     */
                    $createValuesAllowed = (bool)$this->scopeConfig->getValue(
                        \Firebear\ImportExport\Model\Import::CREATE_ATTRIBUTES_CONF_PATH,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                    $isNewAttribute = false;

                    if ($createValuesAllowed && preg_match('/^(attribute\|).+/', $columnName)) {
                        $isNewAttribute = true;
                        $columnData = explode('|', $columnName);
                        $columnData = $this->prepareAttributeData($columnData);
                        $attribute = $this->attributeFactory->create();
                        $attribute->loadByCode(\Magento\Catalog\Model\Product::ENTITY, $columnData['attribute_code']);
                        if (!$attribute->getId()) {
                            $attribute->setBackendType(
                                $attribute->getBackendTypeByInput($columnData['frontend_input'])
                            );
                            $defaultValueField = $attribute->getDefaultValueByInput($columnData['frontend_input']);
                            if (!$defaultValueField && isset($columnData['default_value'])) {
                                unset($columnData['default_value']);
                            }
                            $columnData['source_model'] = $this->productHelper->getAttributeSourceModelByInputType(
                                $columnData['frontend_input']
                            );
                            $columnData['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType(
                                $columnData['frontend_input']
                            );

                            $attribute->addData($columnData);
                            try {
                                $attribute->save();
                            } catch (\Exception $e) {
                                $invalidColumns[] = $columnName;
                            }

                            $attributeSetCodes = explode(',', $columnData[self::ATTRIBUTE_SET_COLUMN]);
                            foreach ($attributeSetCodes as $attributeSetCode) {
                                if (isset($this->_attrSetNameToId[$attributeSetCode])) {
                                    $attributeSetId = $this->_attrSetNameToId[$attributeSetCode];
                                    $attributeGroupCode = isset($columnData[self::ATTRIBUTE_SET_GROUP])
                                        ? $columnData[self::ATTRIBUTE_SET_GROUP] : 'product-details';
                                    if (!isset($this->_attributeSetGroupCache[$attributeSetId])) {
                                        $groupCollection =
                                            $this->groupCollectionFactory->create()->setAttributeSetFilter(
                                                $attributeSetId
                                            )->load();
                                        foreach ($groupCollection as $group) {
                                            $this->_attributeSetGroupCache[$attributeSetId][$group->getAttributeGroupCode()] = $group->getAttributeGroupId();
                                        }
                                    }
                                    foreach ($this->_attributeSetGroupCache[$attributeSetId] as $groupCode => $groupId) {
                                        if ($groupCode == $attributeGroupCode) {
                                            $attribute->setAttributeSetId($attributeSetId);
                                            $attribute->setAttributeGroupId($groupId);
                                            try {
                                                $attribute->save();
                                            } catch (\Exception $e) {
                                                $this->addLogWriteln($e->getMessage(), $this->output, 'error');
                                            }
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        $this->_initTypeModels();
                    }

                    if (trim($columnName) == '') {
                        $emptyHeaderColumns[] = $columnNumber;
                    } elseif (!preg_match('/^[a-zA-Z][a-zA-Z0-9_]*$/', $columnName) && !$isNewAttribute) {
                        $invalidColumns[] = $columnName;
                    } elseif ($this->needColumnCheck && !in_array($columnName, $this->getValidColumnNames())) {
                        $invalidAttributes[] = $columnName;
                    }
                }
            }
            $this->addErrors(self::ERROR_CODE_INVALID_ATTRIBUTE, $invalidAttributes);
            $this->addErrors(self::ERROR_CODE_COLUMN_EMPTY_HEADER, $emptyHeaderColumns);
            $this->addErrors(self::ERROR_CODE_COLUMN_NAME_INVALID, $invalidColumns);
            $this->addLogWriteln(__('Finish checking columns'), $this->output);
            $this->addLogWriteln(__('Errors count: %1', $this->getErrorAggregator()->getErrorsCount()), $this->output);
            if (!$this->getErrorAggregator()->getErrorsCount()) {
                $this->addLogWriteln(__('Start saving bunches'), $this->output);
                $this->mergeFieldsMap();
                $this->_saveValidatedBunches();
                $this->addLogWriteln(__('Finish saving bunches'), $this->output);
                $this->_dataValidated = true;
            }
        }

        return $this->getErrorAggregator();
    }

    /**
     * Add custom field mapping.
     * $this->_fieldsMap – system magento mapping. Merge it with custom admin mapping.
     *
     * @see \Firebear\ImportExport\Block\Adminhtml\Job\Edit\Tab\Map
     */
    protected function mergeFieldsMap()
    {

        if (isset($this->_parameters['map'])) {
            $newAttributes = [];
            foreach ($this->_parameters['map'] as $field) {
                $attribute = $this->getResource()->getAttribute($field['system']);
                $attributeCode = '';
                if ($attribute) {
                    $attributeCode = $attribute->getAttributeCode();
                    $newAttributes[$attribute->getAttributeCode()] = $field['import'];
                } else {
                    $attributeCode = $field['system'];
                    $newAttributes[$field['system']] = $field['import'];
                }
            }

            $this->_fieldsMap = array_merge($this->_fieldsMap, $newAttributes);
        }
    }


    /**
     * @return string[]
     */
    public function getSpecialAttributes()
    {
        return $this->_specialAttributes;
    }

    /**
     * @param $productTypeModel
     * @param $rowData
     *
     * @return mixed
     */
    public function createAttributeValues($productTypeModel, $rowData)
    {
        $options = [];
        $attributeSet = $rowData[\Magento\CatalogImportExport\Model\Import\Product::COL_ATTR_SET];
        foreach ($rowData as $attrCode => $attrValue) {
            /**
             * Add attribute to set & set's group
             */
            if (preg_match('/^(attribute\|).+/', $attrCode)) {
                $columnData = explode('|', $attrCode);
                $columnData = $this->prepareAttributeData($columnData);
                $rowData[$columnData['attribute_code']] = $rowData[$attrCode];
                unset($rowData[$attrCode]);
                $attrCode = $columnData['attribute_code'];
            }

            /**
             * Prepare new values
             */
            $attrParams = $productTypeModel->retrieveAttribute($attrCode, $attributeSet);
            if (!empty($attrParams)) {
                if (!$attrParams['is_static'] && isset($rowData[$attrCode]) && !empty($rowData[$attrCode])) {
                    switch ($attrParams['type']) {
                        case 'select':
                            if (!isset($attrParams['options'][strtolower($rowData[$attrCode])])) {
                                $options[$attrParams['id']][] = [
                                    'sort_order' => count($attrParams['options']) + 1,
                                    'value' => $rowData[$attrCode],
                                    'code' => $attrCode
                                ];
                            }
                            break;
                        case 'multiselect':
                            foreach (explode(Product::PSEUDO_MULTI_LINE_SEPARATOR, $rowData[$attrCode]) as $value) {
                                if (!isset($attrParams['options'][strtolower($value)])) {
                                    $options[$attrParams['id']][] = [
                                        'sort_order' => count($attrParams['options']) + 1,
                                        'value' => $value,
                                        'code' => $attrCode
                                    ];
                                }
                            }
                            break;
                        default:
                            break;
                    }
                }
            }
        }

        /**
         * Create new values
         */
        if (!empty($options)) {
            foreach ($options as $attributeId => $optionsArray) {
                foreach ($optionsArray as $option) {
                    /**
                     * @see \Magento\Eav\Model\ResourceModel\Entity\Attribute::_updateAttributeOption()
                     */
                    $connection = $this->_connection;
                    $resource = $this->_resourceFactory->create();
                    $table = $resource->getTable('eav_attribute_option');
                    $data = ['attribute_id' => $attributeId, 'sort_order' => $option['sort_order']];
                    $connection->insert($table, $data);
                    $intOptionId = $connection->lastInsertId($table);
                    /**
                     * @see \Magento\Eav\Model\ResourceModel\Entity\Attribute::_updateAttributeOptionValues()
                     */
                    $table = $resource->getTable('eav_attribute_option_value');
                    $data = ['option_id' => $intOptionId, 'store_id' => 0, 'value' => $option['value']];
                    $connection->insert($table, $data);
                    $productTypeModel->addAttributeOption($option['code'], strtolower($option['value']), $intOptionId);
                }
            }
        }

        return $rowData;
    }

    /**
     * @return array
     */
    public function getAddFields()
    {
        return $this->addFields;
    }

    /**
     * @return $this
     */
    protected function _initTypeModels()
    {
        $this->_importConfig = $this->fireImportConfig;

        parent::_initTypeModels();

        return $this;
    }

    /**
     * @param $array
     * @return array
     */
    protected function deleteEmpty($array)
    {
        $newElement = [];
        foreach ($array as $key => $element) {
            if ($element) {
                $newElement[$key] = $element;
            }
        }

        return $newElement;
    }

    public function getProductWebsites($productSku)
    {
        if (strpos($this->productMetadata->getVersion(),'2.2') !== false) {
            $productSku = strtolower($productSku);
        }

        return array_keys($this->websitesCache[$productSku]);
    }

    /**
     * @param string $productSku
     * @return array
     */
    public function getProductCategories($productSku)
    {
        if (strpos($this->productMetadata->getVersion(),'2.2') !== false) {
            $productSku = strtolower($productSku);
        }

        return array_keys($this->categoriesCache[$productSku]);
    }

    /**
     * @return \Magento\CatalogImportExport\Model\Import\Uploader
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getUploader()
    {
        $DS = DIRECTORY_SEPARATOR;
        if (is_null($this->_fileUploader)) {
            $this->_fileUploader = $this->_uploaderFactory->create();
            $this->_fileUploader->init();
            $dirConfig = DirectoryList::getDefaultConfig();
            $dirAddon = $dirConfig[DirectoryList::MEDIA][DirectoryList::PATH];
            if (!empty($this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR])) {
                $tmpPath = $this->_parameters[Import::FIELD_NAME_IMG_FILE_DIR];
            } else {
                $tmpPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath('import');
            }
            if (!$this->_fileUploader->setTmpDir($tmpPath)) {
                $this->addLogWriteln(__('File directory \'%1\' is not readable.', $tmpPath), $this->output, 'info');
                $this->addRowError(
                    __('File directory \'%1\' is not readable.', $tmpPath),
                    null,
                    null,
                    null,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                );
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not readable.', $tmpPath)
                );
            }
            $destinationDir = "catalog/product";
            $destinationPath = $dirAddon . $DS . $this->_mediaDirectory->getRelativePath($destinationDir);

            $this->_mediaDirectory->create($destinationPath);
            if (!$this->_fileUploader->setDestDir($destinationPath)) {
                 $this->addRowError(
                     __('File directory \'%1\' is not writable.', $destinationPath),
                    null,
                    null,
                    null,
                    ProcessingError::ERROR_LEVEL_NOT_CRITICAL
                );
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('File directory \'%1\' is not writable.', $destinationPath)
                );
            }
        }

        return $this->_fileUploader;
    }
}
