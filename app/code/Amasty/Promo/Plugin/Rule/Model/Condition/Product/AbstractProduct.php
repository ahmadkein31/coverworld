<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\Rule\Model\Condition\Product;

class AbstractProduct
{
    public function afterLoadAttributeOptions(
        \Magento\Rule\Model\Condition\Product\AbstractProduct $subject,
        $result
    ) {
        //apply to sales (cart) rule only
        if (is_a($subject, 'Magento\SalesRule\Model\Rule\Condition\Product')) {
            $attributes = $result->getAttributeOption();
            $attributes['stock_item_qty'] = __('Quantity in Stock');
            $attributes['weight'] = __('Weight');
            asort($attributes);
            $result->setAttributeOption($attributes);
        }

        return $result;
    }
}
