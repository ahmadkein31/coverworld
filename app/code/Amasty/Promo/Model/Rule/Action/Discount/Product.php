<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model\Rule\Action\Discount;

/**
 * Action name: Auto add promo items with products
 */
class Product extends AbstractDiscount
{
    /**
     * {@inheritdoc}
     */
    protected function _getFreeItemsQty(
        \Magento\SalesRule\Model\Rule $rule,
        \Magento\Quote\Model\Quote\Item\AbstractItem $item
    ) {
        $qty    = 0;
        $amount = max(1, $rule->getDiscountAmount());
        $step   = max(1, $rule->getDiscountStep());
        foreach ($item->getQuote()->getAllVisibleItems() as $item) {
            if (!$item || $this->promoItemHelper->isPromoItem($item) || $item->getProduct()->getParentProductId()) {
                continue;
            }

            if (!$rule->getActions()->validate($item)) {
                // if condition not valid for Parent, but valid for child then collect qty of child
                foreach ($item->getChildren() as $child) {
                    if ($rule->getActions()->validate($child)) {
                        $qty += $child->getTotalQty();
                    }
                }
                continue;
            }

            $qty += $item->getQty();
        }
        $item->getAddress()->setDiscountDescription($rule->getName());

        $qty = floor($qty / $step) * $amount;
        $max = $rule->getDiscountQty();
        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }
}
