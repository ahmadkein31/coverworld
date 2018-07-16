<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Model\Rule\Condition;

class Product extends \Magento\CatalogRule\Model\Rule\Condition\Product
{

    public function getAvailableInCategories($object)
    {
        $connection = $object->getResource()->getConnection();

        // is_parent=1 ensures that we'll get only category IDs those are direct parents of the product, instead of
        // fetching all parent IDs, including those are higher on the tree
        $select = $object->getResource()->getConnection()->select()->distinct()->from(
            $object->getResource()->getTable('catalog_category_product'),
            ['category_id']
        )->where(
            'product_id = ?',
            (int)$object->getEntityId()
        );
//            ->where(
//            'visibility != ?',
//            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
//        );

        return $connection->fetchCol($select);
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $attrCode = $this->getAttribute();

        if ('category_ids' == $attrCode) {

            return $this->validateAttribute($this->getAvailableInCategories($model));
        }

        $oldAttrValue = $model->hasData($attrCode) ? $model->getData($attrCode) : null;
        $this->_setAttributeValue($model);
        $result = $this->validateAttribute($model->getData($this->getAttribute()));
        $this->_restoreOldAttrValue($model, $oldAttrValue);

        return (bool)$result;
    }

    public function loadAttributeOptions()
    {
        parent::loadAttributeOptions();

        $options = $this->getAttributeOption();

        $options['type_id'] =  __('Type');

        asort($options);

        $this->setAttributeOption($options);

        return $this;
    }

   public function getValueSelectOptions()
   {
       if ($this->getAttribute() == 'type_id') {
           $selectOptions = $this->getData('value_select_options');

          $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
          $type = $objectManager->create('\\Magento\Catalog\Model\Product\Type');

          $selectOptions = $type->getOptions();

          $this->setData('value_select_options', $selectOptions);

          $this->getAttributeObject()->setFrontendInput('select');
       }

       return parent::getValueSelectOptions();
   }
}