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
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @author      Mageplaza Developer
 */
namespace Mageplaza\Osc\Model\Total\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Giftwrap extends AbstractTotal
{
    /**
     * Collect total when create Invoice
     *
     * @param Invoice $invoice
     */
    public function collect(Invoice $invoice)
    {
        $order = $invoice->getOrder();
        if ($order->getMcGiftwrapAmount() < 0.0001) {
            return;
        }
        $invoice->setMcGiftwrapBaseAmount(0);
        $invoice->setMcGiftwrapAmount(0);
        $totalGiftwrapAmount     = 0;
        $totalGiftwrapBaseAmount = 0;
        foreach ($invoice->getAllItems() as $item) {
            $orderItem = $item->getOrderItem();
            if ($orderItem->isDummy()) {
                continue;
            }
            $itemQty       = $item->getQty();
            $giftwrapBaseAmount = $orderItem->getMcGiftwrapBaseAmount() * $itemQty;
            $giftwrapAmount     = $orderItem->getMcGiftwrapAmount() * $itemQty;
            $item->setMcGiftwrapBaseAmount($giftwrapBaseAmount);
            $item->setMcGiftwrapAmount($giftwrapAmount);
            $totalGiftwrapBaseAmount += $giftwrapBaseAmount;
            $totalGiftwrapAmount += $giftwrapAmount;
        }
        $invoice->setMcGiftwrapBaseAmount($totalGiftwrapBaseAmount);
        $invoice->setMcGiftwrapAmount($totalGiftwrapAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalGiftwrapAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $totalGiftwrapBaseAmount);

        return $this;
    }

}
