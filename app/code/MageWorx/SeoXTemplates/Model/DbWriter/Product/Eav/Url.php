<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\DbWriter\Product;

use Magento\Framework\App\ResourceConnection;
use MageWorx\SeoXTemplates\Model\DataProviderProductFactory;

use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;
use Magento\UrlRewrite\Model\UrlPersistInterface;

class Eav extends \MageWorx\SeoXTemplates\Model\DbWriter\Product
{
    /**
     * @var ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var UrlPersistInterface
     */
    protected $urlPersist;

    protected $dataProviderProductFactory;

    /**
     * @var Resource
     */
    protected $_resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    protected $_defaultStore;
    protected $_storeId;
    protected $_attributeCodes = array();
    protected $_converter;
    protected $_collection;
    protected $_testMode;

    public function __construct
    (
        ResourceConnection $resource,
        DataProviderProductFactory $dataProviderProductFactory,
        ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        UrlPersistInterface $urlPersist
    ) {
        parent::__construct($dataProviderProductFactory, $resource);
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
    }

    /**
     * Write to database converted string from template code
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \MageWorx\SeoXTemplates\Model\AbstractTemplate $template
     * @param int|null $customStoreId
     * @return array|boolean
     */
    public function write($collection, $template, $customStoreId = null)
    {
        if (!$collection) {
            return false;
        }

        $this->_collection = $collection;

        $dataProvider = $this->dataProviderProductFactory->create($template->getTypeId());
        $data         = $dataProvider->getData($collection, $template, $customStoreId);

        foreach ($data as $attributeHash => $attributeData) {
            $this->attributeDataWrite($attributeHash, $attributeData);
        }
    }

    protected function attributeDataWrite($hash, $attributeData)
    {
        foreach ($attributeData as $insertType => $multipleData) {

            list($attributeId, $attributeCode, $backendType) = explode('#', $hash);
            $tableName = $this->_resource->getTableName('catalog_product_entity_' . $backendType);

            switch ($insertType):
                case 'insert':
                    $this->doInsert($tableName, $multipleData);
                    break;
                case 'update':
                    $this->doUpdate($tableName, $multipleData);
                    break;
            endswitch;
        }
    }

    protected function doInsert($table, $multipleData)
    {
        foreach ($multipleData as $data) {
            if (!empty($data)) {
                $data['attribute_id'] = 115;
                $this->_connection->insert($table, $data);
            }

            $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));
        }

        if (!empty($multipleData)) {
                $this->_connection->insertMultiple(
                $table,
                $multipleData
            );
        }
    }

    protected function doUpdate($table, $multipleData)
    {
        if (!empty($multipleData)) {
            foreach ($multipleData as $data) {
                if (!empty($data)) {
                    $where = [
                        'attribute_id = ?' => (int)$data['attribute_id'],
                        'entity_id = ?'    => (int)$data['entity_id'],
                        'store_id =?'       => (int)$data['store_id']
                    ];
                    $bind = ['value' => $data['value']];
                    $this->_connection->update($table, $bind, $where);
                }

                if ($data['entity_id'] == 1) {
                    if (!empty($data)) {

                        $where = [
                            'attribute_id = ?' => 115,
                            'entity_id = ?'    => 1,
                            'store_id =?'      => 1
                        ];
                        $bind = ['value' => 'joust-duffle-bag-cat'];
                        $this->_connection->update($table, $bind, $where);

                        $product = $this->_collection->getItemById(1);
                        $product->setStoreId($data['store_id']);
                        $product->setUrlKey($bind['value']);
                        $product->setUrlPath($bind['value']);
                        $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($product));

//                        unset($data['old_value']);
//                        $data['attribute_id'] = 115;
//                        $data['value']        = 'joust-duffle-bag-tut';
//                        $this->_connection->insert($table, $data);
//
//                        $product = $this->_collection->getItemById(1);
//                        $product->setStoreId($data['store_id']);
//                        $product->setUrlKey('value');
//                        $this->urlPersist->replace($this->productUrlRewriteGenerator->generate($this->_collection->getItemById(1)->setStoreId($data['store_id'])));
                    }
                }
            }
        }
    }

}