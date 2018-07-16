<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Model;

use Magento\Framework\Model\AbstractModel;

class Category extends \Magento\Framework\Model\AbstractModel
{
    protected $_resourceMapping;
    protected $_mapping;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Feed\Model\Resource\Category $resource = null,
        \Amasty\Feed\Model\Resource\Category\Collection $resourceCollection = null,
        \Amasty\Feed\Model\Resource\Category\Mapping $resourceMapping,
        \Amasty\Feed\Model\Category\Mapping $mapping,

        array $data = []
    ){
        $this->_resourceMapping = $resourceMapping;
        $this->_mapping = $mapping;

        parent::__construct(
                    $context,
                    $registry,
                    $resource,
                    $resourceCollection,
                    $data
                );
    }

    protected function _construct()
    {
        $this->_init('Amasty\Feed\Model\Resource\Category');
        $this->setIdFieldName('feed_category_id');
    }

    public function saveCategoriesMapping()
    {
        $this->_resourceMapping->saveCategoriesMapping($this, $this->getData("mapping"));
    }

    protected function _afterLoad()
    {
        $collection = $this->_mapping->getCategoriesMappingCollection($this);
        if (!$this->getData('mapping')){
            $mapping = array();
            foreach($collection as $mappedCategory){
                $mapping[$mappedCategory->getCategoryId()] = array(
                    'name' => $mappedCategory->getVariable()
                );
            }
            $this->setData('mapping', $mapping);
        }

        parent::afterSave();

    }

    public function getSortedCollection()
    {
        $collection = $this->getCollection();
        $collection->addOrder('name');
        return $collection;
    }
}