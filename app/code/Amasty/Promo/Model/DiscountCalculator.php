<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model;

use Magento\Quote\Model\Quote\Item;

class DiscountCalculator
{
    private $baseDiscountAmount;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    public function __construct(\Magento\Store\Model\Store $store)
    {
        $this->store = $store;
        $this->baseDiscountAmount = 0;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param Item $item
     * @return float|int|mixed|null
     */
    public function getBaseDiscountAmount(\Magento\SalesRule\Model\Rule $rule, Item $item)
    {
        $promoDiscount = trim($rule->getAmpromoRule()->getItemsDiscount());
        $itemPrice = $item->getPrice();
        switch ($promoDiscount) {
            case $promoDiscount == "100%":
            case $promoDiscount == "":
                $baseDiscount = $itemPrice;
                break;
            case strpos($promoDiscount, "%") !== false:
                $baseDiscount = $this->getPercentDiscount($itemPrice, $promoDiscount);
                break;
            case strpos($promoDiscount, "-") !== false:
                $baseDiscount = $this->getFixedDiscount($itemPrice, $promoDiscount);
                break;
            default:
                $baseDiscount = $this->getFixedPrice($itemPrice, $promoDiscount);
                break;
        }

        $baseDiscount = $this->getDiscountAfterMinimalPrice($rule, $itemPrice, $baseDiscount);
        $this->baseDiscountAmount = $baseDiscount * $item->getQty();

        return $this->baseDiscountAmount;
    }

    /**
     * @return float|int
     */
    public function getDiscountAmount()
    {
        $discountAmount = $this->baseDiscountAmount * $this->store->getCurrentCurrencyRate();

        return $discountAmount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getPercentDiscount($itemPrice, $promoDiscount)
    {
        $percent = (float)str_replace("%", "", $promoDiscount);
        $discount = $itemPrice * $percent / 100;

        return $discount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getFixedDiscount($itemPrice, $promoDiscount)
    {
        $discount = abs($promoDiscount);
        if ($discount > $itemPrice) {
            $discount = $itemPrice;
        }

        return $discount;
    }

    /**
     * @param $itemPrice
     * @param $promoDiscount
     * @return mixed
     */
    private function getFixedPrice($itemPrice, $promoDiscount)
    {
        $discount = $itemPrice - (float)$promoDiscount;
        if ($discount < 0) {
            $discount = 0;
        }

        return $discount;
    }

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param $itemPrice
     * @param $discount
     * @return mixed
     */
    private function getDiscountAfterMinimalPrice(\Magento\SalesRule\Model\Rule $rule, $itemPrice, $discount)
    {
        $minimalPrice = $rule->getAmpromoRule()->getMinimalItemsPrice();
        if ($itemPrice > $minimalPrice && $itemPrice - $discount < $minimalPrice) {
            $discount = $itemPrice - $minimalPrice;
        }

        return $discount;
    }
}
