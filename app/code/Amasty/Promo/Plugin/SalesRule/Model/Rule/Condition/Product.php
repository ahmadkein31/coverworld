<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Plugin\SalesRule\Model\Rule\Condition;

use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Product
{
    /**
     * @var StockStateInterface
     */
    private $stockItem;

    public function __construct(StockStateInterface $stockItem)
    {
        $this->stockItem = $stockItem;
    }
    /**
     * Pre validate Product Rule Condition
     *
     * @param \Magento\SalesRule\Model\Rule\Condition\Product $subject
     * @param \Magento\Framework\Model\AbstractModel $model
     *
     * @return array
     */
    public function beforeValidate(
        \Magento\SalesRule\Model\Rule\Condition\Product $subject,
        \Magento\Framework\Model\AbstractModel $model
    ) {
        if ($model->getItemId() && $subject->getAttribute() === 'stock_item_qty') {
            if ($model->getProduct()->getTypeId() === Configurable::TYPE_CODE) {
                $options = $model->getProduct()->getCustomOptions();
                $simple = $options['simple_product'];
                $qty = $this->stockItem->getStockQty(
                    $simple->getProduct()->getId(),
                    $model->getStore()->getWebsiteId()
                );
            } else {
                $qty = $this->stockItem->getStockQty(
                    $model->getProduct()->getId(),
                    $model->getStore()->getWebsiteId()
                );
            }
            $model->getProduct()->setData('stock_item_qty', $qty);
        }

        return [$model];
    }
}
