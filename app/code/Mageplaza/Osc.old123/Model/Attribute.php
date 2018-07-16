<?php

namespace Mageplaza\Osc\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Attribute
{
    protected $_collection;

    public function __construct(Context $context,
                                Registry $registry,
                                Collection $collection
    ) {
        $this->_collection            = $collection;
    }


    /**
     * get all customer attribute used for onetepcheckout by postion
     *
     * @param null $store
     */
    public function getSortedFields()
    {
        $attributeArray = [
            [
                'attribute_code' => 'firstname',
                'field_option'   => 'req',
                'sort_order'     => 1,
                'colspan'        => 1,
                'entity_type_id' => 2
            ],
            [
                'attribute_code' => 'lastname',
                'field_option'   => 'req',
                'sort_order'     => 2,
                'colspan'        => 1,
                'entity_type_id' => 2
            ],
            [
                'attribute_code' => 'email',
                'field_option'   => 'req',
                'sort_order'     => 3,
                'colspan'        => 2,
                'entity_type_id' => 1
            ],
            [
                'attribute_code' => 'street',
                'field_option'   => 'req',
                'sort_order'     => 4,
                'colspan'        => 2,
                'entity_type_id' => 2
            ],
            [
                'attribute_code' => 'country_id',
                'field_option'   => 'req',
                'sort_order'     => 5,
                'colspan'        => 1,
                'entity_type_id' => 2
            ],
            [
                'attribute_code' => 'city',
                'field_option'   => 'req',
                'sort_order'     => 6,
                'colspan'        => 1,
                'entity_type_id' => 2
            ],
            [
                'attribute_code' => 'postcode',
                'field_option'   => 'req',
                'sort_order'     => 7,
                'colspan'        => 1,
                'entity_type_id' => 2
            ],
            [
                'attribute_code' => 'region',
                'field_option'   => 'opt',
                'sort_order'     => 8,
                'colspan'        => 1,
                'entity_type_id' => 2
            ],
            [
                'attribute_code' => 'telephone',
                'field_option'   => 'req',
                'sort_order'     => 9,
                'colspan'        => 1,
                'entity_type_id' => 2
            ],
            [
                'attribute_code' => 'company',
                'field_option'   => 'opt',
                'sort_order'     => 10,
                'colspan'        => 1,
                'entity_type_id' => 2
            ]
        ];
        $container      = new DataObject(
            [
                'attribute_array' => $attributeArray
            ]
        );
        $sortedAttributes = $this->sortAttributes($container->getData('attribute_array'));
        if (!$this->_collection->count()) {
            foreach ($sortedAttributes as $attribute) {
                $item = new DataObject($attribute);
                $this->_collection->addItem($item);

            }
        }

        return $this->_collection;
    }

    /**
     * Sort Attribute By Position
     *
     * @param $attributes
     * @return mixed
     */
    public function sortAttributes($attributes)
    {
        $sortArray = [];

        foreach ($attributes as $attribute) {
            foreach ($attribute as $key => $value) {
                if (!isset($sortArray[$key])) {
                    $sortArray[$key] = [];
                }
                $sortArray[$key][] = $value;
            }
        }
        array_multisort($sortArray['sort_order'], SORT_ASC, $attributes);

        return $attributes;
    }


}