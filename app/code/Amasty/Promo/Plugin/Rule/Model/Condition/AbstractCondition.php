<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Rule\Model\Condition;

use Magento\Rule\Model\Condition\AbstractCondition as MagentoAbstractCondition;

class AbstractCondition
{
    /**
     * Needed because magento save attribute like this "50,0000" but in condition you save like "50"
     *
     * @param MagentoAbstractCondition $subject
     * @param $result
     * @return array
     */
    public function afterGetOperatorSelectOptions(MagentoAbstractCondition $subject, $result)
    {
        $attribute = $subject->getAttribute();
        if ($attribute === 'weight' || $attribute === 'stock_item_qty') {
            $operators = [
                '>=' => __('equals or greater than'),
                '<=' => __('equals or less than'),
                '>' => __('greater than'),
                '<' => __('less than'),
            ];
            $type = $subject->getInputType();
            $result = [];
            $operatorByType = $subject->getOperatorByInputType();
            foreach ($operators as $operatorKey => $operator) {
                if (!$operatorByType || in_array($operatorKey, $operatorByType[$type])) {
                    $result[] = ['value' => $operatorKey, 'label' => $operator];
                }
            }
        }
        return $result;
    }
}