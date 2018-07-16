<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Controller\Cart;

use Magento\Catalog\Api\ProductRepositoryInterface;

class Add extends \Magento\Framework\App\Action\Action
{
    const KEY_QTY_ITEM_PREFIX = 'ampromo_qty_select_';

    /**
     * @var \Amasty\Promo\Model\Registry
     */
    protected $promoRegistry;

    /**
     * @var \Amasty\Promo\Helper\Cart
     */
    protected $promoCartHelper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Amasty\Promo\Model\Registry $promoRegistry,
        \Amasty\Promo\Helper\Cart $promoCartHelper,
        ProductRepositoryInterface $productRepository
    ) {
        parent::__construct($context);
        $this->promoRegistry = $promoRegistry;
        $this->promoCartHelper = $promoCartHelper;
        $this->productRepository = $productRepository;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if (!($data = $this->getRequest()->getParam('data'))) {
            $data[] = $this->getRequest()->getParams();
        }

        foreach ($data as $item) {
            $productId = (int)$item['product_id'];

            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId);

            if ($product->getId()) {
                $limits = $this->promoRegistry->getLimits();
                $sku = $product->getSku();
                $addAllRule = isset($limits[$sku]) && $limits[$sku] > 0;
                $addOneRule = false;
                $discount = isset($limits[$sku]) ? $limits[$sku]['discount']['discount_item'] : null;
                $minimalPrice = isset($limits[$sku]) ? $limits[$sku]['discount']['minimal_price'] : null;

                if (!$addAllRule && is_array($limits['_groups'])) {
                    foreach ($limits['_groups'] as $ruleId => $rule) {
                        if (in_array($sku, $rule['sku'])) {
                            $addOneRule = $ruleId;
                        }
                        $discount = $rule['discount']['discount_item'];
                        $minimalPrice = $rule['discount']['minimal_price'];
                    }
                }

                if ($addAllRule || $addOneRule) {
                    $params = $item;
                    $requestOptions = array_intersect_key($params, array_flip([
                        'super_attribute', 'options', 'super_attribute', 'links'
                    ]));

                    $this->promoCartHelper->addProduct(
                        $product,
                        $this->getQtyByProductId($productId, $item),
                        $addOneRule,
                        $requestOptions,
                        $discount,
                        $minimalPrice
                    );
                }
            }

            $this->promoCartHelper->updateQuoteTotalQty(true);
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();

        return $resultRedirect;
    }

    /**
     * @param $productId
     * @param $data
     * @return int
     */
    protected function getQtyByProductId($productId, $data)
    {
        return isset($data[self::KEY_QTY_ITEM_PREFIX . $productId])
            ? $data[self::KEY_QTY_ITEM_PREFIX . $productId] : 1;
    }
}
