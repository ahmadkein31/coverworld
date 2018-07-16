<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoMarkup\Block\Head\Json;

class Category extends \MageWorx\SeoMarkup\Block\Head\Json
{
    /**
     *
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageWorx\SeoMarkup\Helper\Category
     */
    protected $helperCategory;

    /**
     * @var \MageWorx\SeoMarkup\Helper\DataProvider\Category
     */
    protected $helperDataProvider;

    /**
     * @var \MageWorx\SeoMarkup\Helper\DataProvider\Product
     */
    protected $helperProductDataProvider;

    /**
     * Category constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \MageWorx\SeoMarkup\Helper\Category $helperCategory
     * @param \MageWorx\SeoMarkup\Helper\DataProvider\Category $dataProviderCategory
     * @param \MageWorx\SeoMarkup\Helper\DataProvider\Product $dataProviderProduct
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageWorx\SeoMarkup\Helper\Category $helperCategory,
        \MageWorx\SeoMarkup\Helper\DataProvider\Category $dataProviderCategory,
        \MageWorx\SeoMarkup\Helper\DataProvider\Product $dataProviderProduct,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->registry                   = $registry;
        $this->helperCategory             = $helperCategory;
        $this->helperDataProvider         = $dataProviderCategory;
        $this->helperProductDataProvider  = $dataProviderProduct;
        parent::__construct($context, $data);
    }

    /**
     *
     * {@inheritDoc}
     */
    protected function getMarkupHtml()
    {
        $html = '';

        if (!$this->helperCategory->isRsEnabled()) {
            return $html;
        }

        if ($this->helperCategory->isUseCategoryRobotsRestriction() && $this->isNoindexPage()) {
            return $html;
        }

        $categoryJsonData = $this->getJsonCategoryData();
        $categoryJson     = $categoryJsonData  ? json_encode($categoryJsonData) : '';

        if ($categoryJsonData) {
            $html .= '<script type="application/ld+json">' . $categoryJson . '</script>';
        }

        return $html;
    }

    protected function getJsonCategoryData()
    {
        $category = $this->registry->registry('current_category');
        if (!is_object($category)) {
            return false;
        }

        $productCollection = $this->getProductCollection();

        $data = [];
        $data['@context']    = 'http://schema.org';
        $data['@type']       = 'WebPage';
        $data['url']         = $this->_urlBuilder->getCurrentUrl();
        $data['mainEntity']                    = [];
        $data['mainEntity']['@context']        = 'http://schema.org';
        $data['mainEntity']['@type']           = 'ItemList';
        $data['mainEntity']['name']            = $category->getName();
        $data['mainEntity']['url']             = $this->_urlBuilder->getCurrentUrl();
        $data['mainEntity']['numberOfItems']   = count($productCollection->getItems());
        $data['mainEntity']['itemListElement'] = [];

        foreach ($productCollection as $product) {
            $data['mainEntity']['itemListElement'][] = $this->getProductData($product);
        }
        return $data;
    }

    protected function getProductData($product)
    {
        $data = [];
        $data['@type']    = "Product";
        $data['url']      = $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
        $data['name']     = $product->getName();
        ///
        //$data['image'] = $this->helperProductDataProvider->getProductImageUrl($product);
        ///
        if ($this->helperCategory->isUseOfferForCategoryProducts()) {
            $offerData        = $this->getOfferData($product);
            if (!empty($offerData['price'])) {
                $data['offers'] = $offerData;
            }
        }

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product
     * @return array
     */
    protected function getOfferData($product)
    {
        $data          = [];
        $data['@type'] = \MageWorx\SeoMarkup\Block\Head\Json\Product::OFFER;
        $data['price'] = $product->getFinalPrice();
        $data['priceCurrency'] = $this->helperProductDataProvider->getCurrentCurrencyCode();


        if ($this->helperProductDataProvider->getAvailability($product)) {
            $data['availability'] = \MageWorx\SeoMarkup\Block\Head\Json\Product::IN_STOCK;
        } else {
            $data['availability'] = \MageWorx\SeoMarkup\Block\Head\Json\Product::OUT_OF_STOCK;
        }

        $condition = $this->helperProductDataProvider->getConditionValue($product);
        if ($condition) {
            $data['itemCondition'] = $condition;
        }

        return $data;
    }

    /**
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|null
     */
    protected function getProductCollection()
    {
        $productList = $this->_layout->getBlock('category.products.list');

        if (is_object($productList) && ($productList instanceof \Magento\Catalog\Block\Product\ListProduct)) {
            return $productList->getLoadedProductCollection();
        }

        $pager = $this->_layout->getBlock('product_list_toolbar_pager');
        if (!is_object($pager)) {
            $pager = $this->getPagerFromToolbar();
        } elseif (!$pager->getCollection()) {
            $pager = $this->getPagerFromToolbar();
        }

        if(!is_object($pager)){
            return null;
        }

        return $pager->getCollection();
    }

    /**
     *
     * @return \Magento\Catalog\Block\Product\ListProduct|null
     */
    protected function getPagerFromToolbar()
    {
        $toolbar = $this->_layout->getBlock('product_list_toolbar');
        if (is_object($toolbar)) {
            $pager = $toolbar->getChild('product_list_toolbar_pager');
        }
        return is_object($pager) ? $pager : null;
    }

    /**
     * @return bool
     */
    protected function isNoindexPage()
    {
        $robots = $this->pageConfig->getRobots();

        if ($robots && stripos($robots, 'noindex') !== false) {
            return true;
        }
        return false;
    }
}