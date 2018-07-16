<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Finder\Model;

use Magento\Framework\Phrase;

class Dropdown extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManager;


    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();;
        parent::_construct();
        $this->_init('Amasty\Finder\Model\Resource\Dropdown');
    }

    public function getValues($parentId, $selected=0)
    {
        $options[] = array(
            'value'    => 0,
            'label'    => __('Please Select ...'),
            'selected' => false,
        );

        /**
         * @var $collection \Amasty\Finder\Model\Resource\Value\Collection
         */
        $collection = $this->objectManager->create('Amasty\Finder\Model\Value')->getCollection()
            ->addFieldToFilter('parent_id', $parentId);

        if (!$this->getPos()){
            $collection->addFieldToFilter('dropdown_id', $this->getId());
        }
        switch ($this->getSort()) {
            case \Amasty\Finder\Helper\Data::SORT_STRING_ASC :
                $order = 'name ASC';
                break;
            case \Amasty\Finder\Helper\Data::SORT_STRING_DESC :
                $order = 'name DESC';
                break;
            case \Amasty\Finder\Helper\Data::SORT_NUM_ASC :
                $order = new \Zend_Db_Expr('CAST(`name` AS DECIMAL(10,2)) ASC');
                break;
            case \Amasty\Finder\Helper\Data::SORT_NUM_DESC :
                $order = new \Zend_Db_Expr('CAST(`name` AS DECIMAL(10,2)) DESC');
                break;
        }

        $collection->getSelect()->order($order);
        foreach ($collection as $option){
            $labelValue = ucwords(strtolower($option->getName()));
            $options[] = array(
                'value'    => $option->getValueId(),
                'label'    => __($labelValue),
                'selected' => ($selected == $option->getValueId()),
            );
        }



        return $options;
    }
}
