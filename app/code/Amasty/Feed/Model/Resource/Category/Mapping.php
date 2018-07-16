<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Model\Resource\Category;
use Amasty\Feed\Model\Category;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\AbstractDb;

class Mapping extends AbstractDb
{
    /**
     * Initialize table nad PK name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_feed_category_mapping', 'entity_id');
    }

    public function saveCategoriesMapping($feedMapper, $data)
    {
        $connection = $this->getConnection();

        if (is_array($data)) {
            foreach($data as $categoryId => $item){
                $connection->delete(
                    $this->getMainTable(),
                    ['feed_category_id = ?' => $feedMapper->getId(), 'category_id = ?' => $categoryId]
                );
                if (isset($item['name']) && !empty($item['name'])) {
                    $bind = [
                        'feed_category_id' => $feedMapper->getId(),
                        'category_id' => $categoryId,
                        'variable' => $item['name']
                    ];
                    $connection->insert($this->getMainTable(), $bind);
                }
            }
        }

    }
}