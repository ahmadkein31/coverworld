<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model\Rule\Action\Discount;

/**
 * Action name: Auto add promo items for every $X spent
 */
class Spent extends AbstractDiscount
{
    protected function _getFreeItemsQty(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        $amount = max(1, $rule->getDiscountAmount());
        $step   = $this->priceCurrency->convert($rule->getDiscountStep());

        if (!$step) {
            return 0;
        }

        $totals = $item->getAddress()->setDiscountDescription($rule->getName())->getTotals();
        $qty = floor($totals['subtotal']->getValue() / $step) * $amount;

        $max = $rule->getDiscountQty();
        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }
}
