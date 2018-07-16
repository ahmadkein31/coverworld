<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBase\Model\Robots;

use MageWorx\SeoBase\Helper\Data as HelperData;
use Magento\Framework\Registry;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

/**
 * SEO Base category robots model
 */
class Category extends \MageWorx\SeoBase\Model\Robots
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\Layer\Category\
     */
    protected $catalogLayer = null;

    /**
     * @param HelperData $helperData
     * @param Registry $registry
     * @param RequestInterface $request
     * @param UrlInterface $url
     * @param LayerResolver $layerResolver
     */
    public function __construct(
        HelperData $helperData,
        Registry $registry,
        RequestInterface $request,
        UrlInterface $url,
        LayerResolver $layerResolver,
        $fullActionName
    ) {

        $this->registry = $registry;
        $this->catalogLayer = $layerResolver->get();
        parent::__construct($helperData, $request, $url, $fullActionName);
    }

    /**
     * Retrieve final robots
     *
     * @return string
     */
    public function getRobots()
    {
        $metaRobots = $this->getCategoryRobots();
        return $metaRobots ? $metaRobots : $this->getRobotsBySettings();
    }

    /**
     * Retrieve robots from category atttibute/by layered navigation condition
     *
     * @return string|null
     */
    protected function getCategoryRobots()
    {
        $category = $this->registry->registry('current_category');
        if (is_object($category)) {

            $maxFilters   = $this->helperData->getCountFiltersForNoindex();
            $countFilters = count($this->getCurrentLayeredFilters());

            if ($countFilters && $maxFilters !== '' && !is_null($maxFilters) && $countFilters >= $maxFilters) {
                return 'NOINDEX, FOLLOW';
            }

            if ($this->helperData->isUseNoindexIfCategoryFilter() && $this->isCategoryFilterActive()) {
                return 'NOINDEX, FOLLOW';
            }

            if ($category->getMetaRobots()) {
                return $category->getMetaRobots();
            }
        }
        return null;
    }

    /**
     * @return array
     */
    protected function getCurrentLayeredFilters()
    {
        if (is_object($this->catalogLayer)
            && is_object($this->catalogLayer->getState())
            && is_array($this->catalogLayer->getState()->getFilters())
        ) {
            return $this->catalogLayer->getState()->getFilters();
        }
        return [];
    }

    /**
     * @return boolean
     */
    protected function isCategoryFilterActive()
    {
        $items = $this->getCurrentLayeredFilters();
        if ($items) {
            foreach ($items as $item) {
                if ($item->getFilter()->getRequestVar() == 'cat') {
                    return true;
                }
            }
        }

        return false;
    }

}
