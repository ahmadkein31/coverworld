<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Model\DataProvider\Category;

class Eav extends \MageWorx\SeoXTemplates\Model\DataProvider\Category
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     *
     * @var int
     */
    protected $_defaultStore;

    /**
     *
     * @var int
     */
    protected $_storeId;


    /**
     *
     * @var array
     */
    protected $_attributeCodes = array();

    /**
     *
     * @var \Magento\Framework\Data\Collection
     */
    protected $_collection;

    /**
     * Retrive data
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \MageWorx\SeoXTemplates\Model\AbstractTemplate $template
     * @param int|null $customStoreId
     * @return array
     */
    public function getData($collection, $template, $customStoreId = null)
    {
        if (!$collection) {
            return false;
        }

        $this->_collection = $collection;
        $this->_storeId    = $customStoreId ? $customStoreId : $template->getStoreId();

        $this->_attributeCodes = $template->getAttributeCodesByType();

        $attributes  = array();
        $connection  = $this->_getConnection();

        $select        = $connection->select()
            ->from($this->_resource->getTableName('eav_entity_type'))
            ->where("entity_type_code = 'catalog_category'");
        $categoryTypeId = $connection->fetchOne($select);

        foreach ($this->_attributeCodes as $_attrName) {
            $select                 = $connection->select()
                ->from($this->_resource->getTableName('eav_attribute'))
                ->where("entity_type_id = $categoryTypeId AND (attribute_code = '" . $_attrName . "')");

            if ($res = $connection->fetchRow($select)) {
                $attributes[$_attrName] = $res;
            }
        }

        $categoryIds       = array_keys($this->_collection->toOptionHash());
        $categoryIdsString = implode(',', $categoryIds);

        $data = [];

        foreach ($attributes as $attribute) {

            $idsByAttribute = [
                'insert' => array_fill_keys($categoryIds, []),
                'update' => []
            ];

            $select = $connection->select(['entity_id'])
                ->from($this->_resource->getTableName('catalog_category_entity') . '_' . $attribute['backend_type'])
                ->where("attribute_id = '$attribute[attribute_id]' AND entity_id IN ({$categoryIdsString}) AND store_id = {$this->_storeId} AND CHAR_LENGTH(value) > 0");

            $existRecords = $connection->fetchAll($select);

            foreach ($existRecords as $record) {
                if ($template->isScopeForAll()) {
                    $idsByAttribute['update'][$record['entity_id']] = ['old_value' => $record['value']];
                }
                unset($idsByAttribute['insert'][$record['entity_id']]);
            }

            $attributeHash = $attribute['attribute_id'] . '#' .  $attribute['attribute_code'] . '#' . $attribute['backend_type'];
            $data[$attributeHash] = $idsByAttribute;
        }

        $this->fillData($template, $data);

        return $data;
    }

    /**
     * Add data for each entityId
     *
     *
     *
     * @param array $data
     */
    protected function fillData($template, &$data)
    {
        foreach ($data as $attributeHash => $attributeData) {

            list($attributeId, $attributeCode) = explode('#', $attributeHash);

            $converter = $this->converterCategoryFactory->create($attributeCode);

            foreach ($attributeData as $insertTypeName => $insertData) {

                foreach ($insertData as $entityId => $emptyValue) {

//                    $microtime = microtime(1);
                    $attributeValue = '';
                    $category = $this->_collection->getItemById($entityId);
                    if ($category) {
                        $attributeValue = $converter->convert($category->setStoreId($this->_storeId), $template->getCode());
                    }

//                    echo "<br><font color = green>" . number_format((microtime(1) - $microtime), 5) . " sec need for " . get_class($this) . "</font>";

                    if ($attributeValue) {
                        $data[$attributeHash][$insertTypeName][$entityId] = array_merge($data[$attributeHash][$insertTypeName][$entityId], [
                            'attribute_id' => $attributeId,
                            'entity_id'    => $entityId,
                            'store_id'     => $this->_storeId,
                            'value'        => $attributeValue,
                        ]);
                    } else {
                        unset($data[$attributeCode][$insertTypeName][$entityId]);
                    }
                }
            }
        }
    }

    public function getAttributeCodes()
    {
        return $this->_attributeCodes;
    }
}