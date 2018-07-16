<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\DbWriter\Product;

class Eav extends \MageWorx\SeoXTemplates\Model\DbWriter\Product
{
    /**
     * Write to database converted string from template code
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @param \MageWorx\SeoXTemplates\Model\AbstractTemplate $template
     * @param int $customStoreId
     * @return array|false
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

        return true;
    }

    /**
     * Write dispatch
     *
     * @param string $hash
     * @param array $attributeData
     */
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

    /**
     * Insert proccess
     *
     * @param string $table
     * @param array $multipleData
     */
    protected function doInsert($table, $multipleData)
    {
        $multipleData = array_filter($multipleData);

        if (!empty($multipleData)) {
                $this->_connection->insertMultiple(
                $table,
                $multipleData
            );
        }
    }

    /**
     * Update proccess
     *
     * @param string $table
     * @param array $multipleData
     */
    protected function doUpdate($table, $multipleData)
    {
        if (!empty($multipleData)) {
            foreach ($multipleData as $data) {
                $where = [
                    'attribute_id = ?' => (int)$data['attribute_id'],
                    'entity_id = ?'    => (int)$data['entity_id'],
                    'store_id =?'       => (int)$data['store_id']
                ];
                $bind = ['value' => $data['value']];
                $this->_connection->update($table, $bind, $where);
            }
        }
    }
}