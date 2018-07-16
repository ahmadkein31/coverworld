<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

/**
 * Osc Spend for Order by Point Model
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @author      Mageplaza Developer
 */
namespace Mageplaza\Osc\Model\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Giftwrap extends AbstractTotal
{

    /**
     * Collect total when create Creditmemo
     *
     * @param Creditmemo $creditmemo
     */
    public function collect(Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        if ($creditmemo->getGrandTotal() == 0) {
            $creditmemo->setIsLastCreditmemo(false);
        }
        if ($order->getMcGiftwrapAmount() < 0.0001) {
            return;
        }
        $creditmemo->setMcGiftwrapBaseAmount(0);
        $creditmemo->setMcGiftwrapAmount(0);
        $totalGiftwrapAmount     = 0;
        $totalGiftwrapBaseAmount = 0;
        /** @var $item \Magento\Sales\Model\Order\Credimemo\Item */
        foreach ($creditmemo->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }
            $itemQty            = $item->getQty();
            $giftwrapBaseAmount = $orderItem->getMcGiftwrapBaseAmount() * $itemQty;
            $giftwrapAmount     = $orderItem->getMcGiftwrapAmount() * $itemQty;
            $item->setMcGiftwrapBaseAmount($giftwrapBaseAmount);
            $item->setMcGiftwrapAmount($giftwrapAmount);
            $totalGiftwrapBaseAmount += $giftwrapBaseAmount;
            $totalGiftwrapAmount += $giftwrapAmount;
        }
        $creditmemo->setMcGiftwrapBaseAmount($totalGiftwrapBaseAmount);
        $creditmemo->setMcGiftwrapAmount($totalGiftwrapAmount);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $totalGiftwrapAmount);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $totalGiftwrapBaseAmount);
    }

    /**
     * check credit memo is last or not
     *
     * @param Creditmemo $creditmemo
     * @return boolean
     */
    public function isLast($creditmemo)
    {
        foreach ($creditmemo->getAllItems() as $item) {
            if (!$item->isLast()) {
                return false;
            }
        }

        return true;
    }
}
