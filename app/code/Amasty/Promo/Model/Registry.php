<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model;

class Registry extends \Magento\Framework\Model\AbstractModel
{
    protected $_hasItems = false;
    protected $_locked = false;
    protected $_isHandled = [];
    protected $autoAddTypes = ['simple', 'virtual', 'downloadable'];

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Amasty\Promo\Helper\Item
     */
    protected $promoItemHelper;

    /**
     * @var \Amasty\Promo\Helper\Messages
     */
    protected $promoMessagesHelper;

    /**
     * @var \Amasty\Promo\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    public function __construct(
        \Magento\Checkout\Model\Session $resourceSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\Promo\Helper\Item $promoItemHelper,
        \Amasty\Promo\Helper\Messages $promoMessagesHelper,
        \Amasty\Promo\Helper\Config $config,
        \Magento\Store\Model\Store $store
    ) {
        $this->_checkoutSession    = $resourceSession;
        $this->scopeConfig         = $scopeConfig;
        $this->_productFactory     = $productFactory;
        $this->_storeManager       = $storeManager;
        $this->promoItemHelper     = $promoItemHelper;
        $this->promoMessagesHelper = $promoMessagesHelper;
        $this->config = $config;
        $this->store = $store;
    }

    public function getApplyAttempt($ruleId)
    {
        if (isset($this->_isHandled[$ruleId])) {
            return false;
        }
        $this->_isHandled[$ruleId] = true;

        return true;
    }

    /**
     * @param $sku
     * @param $qty
     * @param $ruleId
     * @param $discountData
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addPromoItem($sku, $qty, $ruleId, $discountData)
    {
        if ($this->_locked) {
            return;
        }

        if (!$this->_hasItems) {
            $this->reset();
        }

        $discountData = $this->getCurrencyDiscount($discountData);

        $this->_hasItems = true;
        $items = $this->getPromoItems();
        $autoAdd = false;

        $addAutomatically = $this->config->getScopeValue("general/auto_add");
        $isFullDiscount = ($discountData['discount_item'] === "100%"
            || $discountData['discount_item'] === null
            || $discountData['discount_item'] === "")
            && !$discountData['minimal_price'];

        if (is_array($sku) && count($sku) == 1) {
            $sku = $sku[0];
        }

        if (!is_array($sku)) {
            if ($addAutomatically && $isFullDiscount) {
                $collection = $this->_productFactory->create()->getCollection()
                    ->addAttributeToSelect(['name', 'status', 'required_options'])
                    ->addAttributeToFilter('sku', $sku)
                    ->setPage(1, 1);

                $product = $collection->getFirstItem();

                $currentWebsiteId = $this->_storeManager->getWebsite()->getId();
                if (!is_array($product->getWebsiteIds())
                    || !in_array($currentWebsiteId, $product->getWebsiteIds())
                ) {
                    // Ignore products from other websites
                    return;
                }

                if (!$product || !$product->isInStock() || !$product->isSalable()) {
                    $this->promoMessagesHelper->addAvailabilityError($product);
                } else {
                    if (in_array($product->getTypeId(), $this->autoAddTypes)
                        && !$product->getTypeInstance(true)->hasRequiredOptions($product)
                    ) {
                        $autoAdd = true;
                    }
                }
            }

            if (isset($items[$sku])) {
                $items[$sku]['qty'] += $qty;
            } else {
                $items[$sku] = [
                    'sku'      => $sku,
                    'qty'      => $qty,
                    'auto_add' => $autoAdd,
                    'discount' => $discountData
                ];
            }
        } else {
            $items['_groups'][$ruleId] = [
                'sku' => $sku,
                'qty' => $qty,
                'discount' => $discountData
            ];
        }

        $this->_checkoutSession->setAmpromoItems($items);
    }

    /**
     * @param $discountData
     * @return mixed
     */
    private function getCurrencyDiscount($discountData)
    {
        preg_match('/^-*\d+.*\d*$/', $discountData['discount_item'], $discount);
        if (isset($discount[0]) && is_numeric($discount[0])) {
            $discountData['discount_item'] = $discount[0] * $this->store->getCurrentCurrencyRate();
        }

        return $discountData;
    }

    public function getPromoItems()
    {
        $items = $this->_checkoutSession->getAmpromoItems();

        return $items ? $items : ['_groups' => []];
    }

    public function reset()
    {
        if ($this->_hasItems) {
            $this->_locked = true;
            return;
        }

        $this->_checkoutSession->setAmpromoItems(['_groups' => []]);
    }

    public function getLimits()
    {
        $allowed = null;
        $quote   = $this->_checkoutSession->getQuote();

        if ($quote->getId() > 0) {
            $allowed = $this->getPromoItems();
            foreach ($quote->getAllItems() as $item) {
                $sku = $item->getProduct()->getData('sku');

                if ($this->promoItemHelper->isPromoItem($item)) {
                    $ruleId = $this->promoItemHelper->getRuleId($item);

                    if (isset($allowed['_groups'][$ruleId])) {
                        if ($item->getParentItem()) {
                            continue;
                        }

                        $allowed['_groups'][$ruleId]['qty'] -= $item->getQty();
                        if ($allowed['_groups'][$ruleId]['qty'] <= 0) {
                            unset($allowed['_groups'][$ruleId]);
                        }
                    } else {
                        if (isset($allowed[$sku])) {
                            $allowed[$sku]['qty'] -= $item->getQty();

                            if ($allowed[$sku]['qty'] <= 0) {
                                unset($allowed[$sku]);
                            }
                        }
                    }
                }
            }
        }

        return $allowed;
    }

    public function deleteProduct($sku)
    {
        $deletedItems = $this->_checkoutSession->getAmpromoDeletedItems();

        if (!$deletedItems) {
            $deletedItems = [];
        }

        $deletedItems[$sku] = true;

        $this->_checkoutSession->setAmpromoDeletedItems($deletedItems);
    }

    public function restore($sku)
    {
        $deletedItems = $this->_checkoutSession->getAmpromoDeletedItems();

        if (!$deletedItems || !isset($deletedItems[$sku])) {
            return;
        }

        unset($deletedItems[$sku]);

        $this->_checkoutSession->setAmpromoDeletedItems($deletedItems);
    }

    public function getDeletedItems()
    {
        $deletedItems = $this->_checkoutSession->getAmpromoDeletedItems();

        if (!$deletedItems) {
            $deletedItems = [];
        }

        return $deletedItems;
    }
}
