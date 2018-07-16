<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Canonical;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

class Category extends \MageWorx\SeoBase\Model\Canonical
{
    /**#@+
     * Canonical URL for layered navigation pages - admin config settings
     */
    const CATEGORY_LN_CANONICAL_OFF          = 0;

    const CATEGORY_LN_CANONICAL_USE_FILTERS  = 1;

    const CATEGORY_LN_CANONICAL_CATEGORY_URL = 2;

    /**#@+
     * Canonical URL for layered navigation pages - attribute individual settings
     */
    const ATTRIBUTE_LN_CANONICAL_BY_CONFIG    = 0;

    const ATTRIBUTE_LN_CANONICAL_USE_FILTERS  = 1;

    const ATTRIBUTE_LN_CANONICAL_CATEGORY_URL = 2;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \MageWorx\SeoBase\Model\ResourceModel\Catalog\Category\CrossDomainFactory
     */
    protected $crossDomainFactory;

    /**
     * @var \MageWorx\SeoBase\Helper\Url
     */
    protected $helperUrl;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\
     */
    protected $catalogLayer;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * Request object
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $category;

    /**
     *
     * @param \MageWorx\SeoBase\Helper\Data $helperData
     * @param \MageWorx\SeoBase\Helper\Url $helperUrl
     * @param \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     * @param \MageWorx\SeoBase\Model\ResourceModel\Catalog\Category\CrossDomainFactory $crossDomainFactory
     * @param LayerResolver $layerResolver
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\View\Layout $layout
     * @param string $fullActionName
     */
    public function __construct(
        \MageWorx\SeoBase\Helper\Data $helperData,
        \MageWorx\SeoBase\Helper\Url  $helperUrl,
        \MageWorx\SeoBase\Helper\StoreUrl $helperStoreUrl,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url,
        \MageWorx\SeoBase\Model\ResourceModel\Catalog\Category\CrossDomainFactory $crossDomainFactory,
        LayerResolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\View\Layout $layout,
        $fullActionName
    ) {

        $this->registry           = $registry;
        $this->url                = $url;
        $this->catalogLayer       = $layerResolver->get();
        $this->category           = $categoryRepository;
        $this->helperUrl          = $helperUrl;
        $this->layout             = $layout;
        $this->crossDomainFactory = $crossDomainFactory;
        parent::__construct($helperData, $helperUrl, $helperStoreUrl, $fullActionName);
    }

    /**
     * Retrieve category canonical URL
     *
     * @return string|null
     */
    public function getCanonicalUrl()
    {
        if ($this->isCancelCanonical()) {
            return null;
        }
        $category = $this->registry->registry('current_category');
        if (empty($category) || !is_object($category)) {
            return null;
        }

        $crossDomainUrl = $this->convertToCrossDomain($category);
        if ($crossDomainUrl) {
            return $this->trailingSlash($crossDomainUrl);
        }

        $url           = $this->url->getCurrentUrl();
        $pageParamName = $this->getPageVarName();

        if (is_object($this->catalogLayer)
            && is_object($this->catalogLayer->getState())
            && !empty($this->catalogLayer->getState()->getFilters())
        ) {
            if ($this->helperData->getCanonicalTypeForLayeredPages() == self::CATEGORY_LN_CANONICAL_OFF) {
                return '';
            }
            if (!$this->isIncludeLNFiltersToCanonicalUrl()) {
                return $this->trailingSlash($category->getUrl());
            }
            $subCategoryUrl = $this->getSubCategoryUrlByCurrentUrl($url);
            if (!is_null($subCategoryUrl)) {
                $url = $this->convertSubCategoryUrl($url, $subCategoryUrl);
            }

            if ($this->helperData->usePagerForCanonical()) {
                $url          = $this->helperUrl->removeFirstPage($url);
                $exceptParams = array_merge([$pageParamName], $this->getLayeredNavigationFiltersCode());
                $url          = $this->helperUrl->deleteUrlParametrsWithExcept($url, $exceptParams);
            } else {
                $url = $this->helperUrl->deleteUrlParametrsWithExcept($url, $this->getLayeredNavigationFiltersCode());
            }
        } else {
            if ($this->helperData->usePagerForCanonical()) {
                $urlRaw = $this->helperUrl->removeFirstPage($url);
                $url = $this->helperUrl->deleteUrlParametrsWithExcept($urlRaw, [$pageParamName]);
            } else {
                $url = $this->trailingSlash($category->getUrl());
            }
        }

        return $this->helperUrl->escapeUrl($url);
    }

    /**
     * Replace base URL part for canonical URL if cross domain store or URL used
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param string $url
     * @return string|false
     */
    protected function convertToCrossDomain($category)
    {
        $crossDomainStoreByConfig = $this->getCrossDomainStoreId($this->helperData->getCrossDomainStore());
        $crossDomainUrlByConfig   = $this->helperData->getCrossDomainUrl();

        if ($crossDomainStoreByConfig) {
            $crossDomainCategory = $this->crossDomainFactory->create()
                ->getCrossDomainData($category->getId(), $crossDomainStoreByConfig);
            if (is_object($crossDomainCategory)) {
                return $crossDomainCategory->getUrl();
            }
        } elseif ($crossDomainUrlByConfig) {
            $storeBaseUrl = $this->helperStoreUrl->getStoreBaseUrl();
            return str_replace(
                rtrim(
                    $storeBaseUrl,
                    '/'
                ) . '/',
                rtrim(trim($crossDomainUrlByConfig), '/') . '/',
                $category->getUrl()
            );
        }
        return false;
    }

    /**
     * Check if enable layered filters in canonical URL
     *
     * @return boolean
     */
    protected function isIncludeLNFiltersToCanonicalUrl()
    {
        $enableByConfig  = $this->helperData->getCanonicalTypeForLayeredPages();
        $answerByFilters = $this->isIncludeLNFiltersToCanonicalUrlByFilters();

        if ($enableByConfig == self::CATEGORY_LN_CANONICAL_USE_FILTERS
            && $answerByFilters == self::ATTRIBUTE_LN_CANONICAL_CATEGORY_URL
        ) {
            return false;
        }
        if ($enableByConfig == self::CATEGORY_LN_CANONICAL_CATEGORY_URL
            && $answerByFilters == self::ATTRIBUTE_LN_CANONICAL_USE_FILTERS
        ) {
            return true;
        }
        if ($enableByConfig == self::CATEGORY_LN_CANONICAL_USE_FILTERS) {
            return true;
        }
        return false;
    }

    /**
     * Check if enable layered filters in canonical URL by current filters
     *
     * @return int
     */
    protected function isIncludeLNFiltersToCanonicalUrlByFilters()
    {
        $filtersData = $this->getLayeredNavigationFiltersData();

        if (!$filtersData) {
            return self::ATTRIBUTE_LN_CANONICAL_BY_CONFIG;
        }
        usort($filtersData, [$this, "filterSort"]);
        foreach ($filtersData as $data) {
            if (!empty($data['use_in_canonical'])) {
                return $data['use_in_canonical'];
            }
        }
        return false;
    }

    protected function filterSort($a, $b)
    {
        $a['position'] = (empty($a['position'])) ? 0 : $a['position'];
        $b['position'] = (empty($b['position'])) ? 0 : $b['position'];

        if ($a['position'] == $b['position']) {
            return 0;
        }
        return ($a['position'] < $b['position']) ? +1 : -1;
    }

    /**
     * Retrieve subcategory URL if input URL content category filter
     *
     * @param string $url
     * @return string|null
     */
    protected function getSubCategoryUrlByCurrentUrl($url)
    {
        $parseUrl = parse_url($url);

        if (empty($parseUrl['query'])) {
            return $url;
        }
        $params = '';
        parse_str(html_entity_decode($parseUrl['query']), $params);
        if (!empty($params['cat']) && is_numeric($params['cat'])) {
            $subCategoryUrl = $this->category->get($params['cat'])->getUrl();
        }
        return (!empty($subCategoryUrl)) ? $subCategoryUrl : null;
    }

    /**
     * Render subcategory URL
     *
     * @param string $url
     * @param string $categoryUrl
     * @return string
     */
    protected function convertSubCategoryUrl($url, $categoryUrl)
    {
        if ($categoryUrl) {
            $parseUrl = parse_url($url);
            $url      = $categoryUrl . '?' . $parseUrl['query'];
            $url      = $this->helperUrl->deleteUrlParametrs($url, ['cat']);
        }
        return $url;
    }

    /**
     * Retrieve specific filters data as array (use for canonical url)
     * @return array|false
     */
    protected function getLayeredNavigationFiltersData()
    {
        $filterData     = [];
        $appliedFilters = $this->catalogLayer->getState()->getFilters();

        if (is_array($appliedFilters) && count($appliedFilters) > 0) {
            foreach ($appliedFilters as $item) {

                if (is_null($item->getFilter()->getData('attribute_model'))) {
                    //Ex: If $item->getFilter()->getRequestVar() == 'cat'
                    $use_in_canonical = 0;
                    $position         = 0;
                } else {
                    $use_in_canonical = $item->getFilter()->getAttributeModel()->getLayeredNavigationCanonical();
                    $position         = $item->getFilter()->getAttributeModel()->getPosition();
                }

                $filterData[] = [
                    'name'             => $item->getName(),
                    'label'            => $item->getLabel(),
                    'code'             => $item->getFilter()->getRequestVar(),
                    'use_in_canonical' => $use_in_canonical,
                    'position'         => $position
                ];
            }
        }
        return (count($filterData) > 0) ? $filterData : false;
    }

    /**
     * Retrieve list of current filter codes
     *
     * @return array
     */
    protected function getLayeredNavigationFiltersCode()
    {
        $filterCodes    = [];
        $appliedFilters = $this->catalogLayer->getState()->getFilters();

        if (is_array($appliedFilters) && count($appliedFilters) > 0) {
            foreach ($appliedFilters as $item) {
                $filterCodes[] = $item->getFilter()->getRequestVar();
            }
        }
        return $filterCodes;
    }

    /**
     * Retrieve pager block from layout
     *
     * @return \Magento\Theme\Block\Html\Pager
     */
    protected function getPager()
    {
        if (is_object($this->layout)) {
            return $this->layout->getBlock('product_list_toolbar_pager');
        }
        return null;
    }

    /**
     * Retrieve pager var name
     *
     * @return string
     */
    protected function getPageVarName()
    {
        $pager = $this->getPager();
        if (is_object($pager)) {
            return $pager->getPageVarName() ? $pager->getPageVarName() : 'p';
        }
        return 'p';
    }

    /**
     * Retrieve cross domain store ID
     *
     * @return int
     */
    protected function getCrossDomainStoreId($storeId)
    {
        if (!$storeId) {
            return false;
        }
        if (!$this->helperStoreUrl->isActiveStore($storeId)) {
            return false;
        }
        if ($this->helperStoreUrl->getCurrentStoreId() == $storeId) {
            return false;
        }
        return $storeId;
    }
}
