<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Amasty\Promo\Model\Registry
     */
    protected $promoRegistry;

    /**
     * @var \Amasty\Promo\Helper\Messages
     */
    protected $promoMessagesHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Amasty\Promo\Helper\Cart
     */
    protected $promoCartHelper;

    protected $_productsCache = null;

    protected $_allowedTypes = [
        'simple',
        'configurable',
        'virtual',
        'bundle',
        'downloadable'
    ];

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Helper\Messages $promoMessagesHelper,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Amasty\Promo\Helper\Cart $promoCartHelper
    ) {
        parent::__construct($context);

        $this->promoRegistry = $promoRegistry;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->_productFactory = $productFactory;
        $this->promoCartHelper = $promoCartHelper;
    }

    public function getNewItems()
    {
        if ($this->_productsCache === null) {
            $items = $this->promoRegistry->getLimits();

            $groups = $items['_groups'];
            unset($items['_groups']);

            if (!$items && !$groups) {
                $this->_productsCache = false;

                return false;
            }

            $allowedSku = array_keys($items);
            foreach ($groups as $rule) {
                $allowedSku = array_merge($allowedSku, $rule['sku']);
            }

            $products = $this->_productFactory->create()->getCollection()
                ->addAttributeToSelect(['name', 'small_image', 'status', 'visibility'])
                ->addFieldToFilter('sku', ['in' => $allowedSku])
            ;

            foreach ($products as $key => $product) {
                if (!in_array($product->getTypeId(), $this->_allowedTypes)) {
                    $this->promoMessagesHelper->showMessage(__(
                        "We apologize, but products of type <strong>%1</strong> are not supported",
                        $product->getTypeId()
                    ));

                    $products->removeItemByKey($key);
                }

                if ($product->getTypeId() == 'simple' && (!$product->isInStock() || !$product->isSalable()
                    || !$this->promoCartHelper->checkAvailableQty($product, 1))
                ) {
                    $this->promoMessagesHelper->addAvailabilityError($product);

                    $products->removeItemByKey($key);
                }

                foreach ($product->getProductOptionsCollection() as $option) {
                    $option->setProduct($product);
                    $product->addOption($option);
                }

                if (isset($items[$product->getSku()])) {
                    $product->setAmpromoDiscount($items[$product->getSku()]['discount']);
                }
            }

            if ($products->getSize() > 0) {
                $this->_productsCache = $products;
            } else {
                $this->_productsCache = false;
            }
        }

        return $this->_productsCache;
    }

    /**
     * @return null
     */
    public function getAllowedProductQty()
    {
        $result = [];
        $items = $this->promoRegistry->getLimits();
        $qty = 0;
        if (isset($items['_groups'])) {
            $discountData = [];
            $item = array_shift($items['_groups']);
            if (isset($item['sku'])) {
                foreach ($item['sku'] as $sku) {
                    $discountData[$sku] = ['discount' => $item['discount']];
                }
            }

            if (isset($item['qty'])) {
                $qty += $item['qty'];

            }
            unset($items['_groups']);
            foreach ($items as $item) {
                if (isset($item['qty'])) {
                    $qty += $item['qty'];
                }
            }
            $discountData += $items;

            $result += [
                'common_qty' => $qty,
                'triggered_products' => $discountData
            ];
        }

        return $result;
    }
}
