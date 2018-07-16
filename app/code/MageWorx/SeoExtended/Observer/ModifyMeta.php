<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoExtended\Observer;

use Magento\Framework\View\Page\Config as PageConfig;
use MageWorx\SeoExtended\Helper\Data as HelperData;

/**
 * Observer class for modify meta title, description, keywords
 */
class ModifyMeta implements \Magento\Framework\Event\ObserverInterface
{
    /**
     *
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\View\Page\Config
     */
    protected $pageConfig;

    /**
     *
     * @var \MageWorx\SeoExtended\Model\PageNumFactory
     */
    protected $pageNumFactory;

    /**
     *
     * @var \MageWorx\SeoExtended\Model\LayeredFiltersFactory
     */
    protected $layeredFiltersFactory;

    /**
     *
     * @var \MageWorx\SeoExtended\Model\FiltersConvertorFactory
     */
    protected $filtersConvertorFactory;

    /**
     * Filter manager
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     *
     * @var int|false|null
     */
    protected $currentPageNum;

    /**
     *
     * @var string|null
     */
    protected $filtersString;

    /**
     *
     * @param HelperData $helperData
     * @param PageConfig $pageConfig
     * @param \MageWorx\SeoExtended\Model\PageNumFactory $pageNumFactory
     * @param \MageWorx\SeoExtended\Model\FiltersConvertorFactory $filtersConvertorFactory
     */
    public function __construct(
        HelperData $helperData,
        PageConfig $pageConfig,
        \MageWorx\SeoExtended\Model\PageNumFactory $pageNumFactory,
        \MageWorx\SeoExtended\Model\FiltersConvertorFactory $filtersConvertorFactory,
        \Magento\Framework\Filter\FilterManager $filterManager
    ) {
        $this->helperData = $helperData;
        $this->pageConfig = $pageConfig;
        $this->pageNumFactory = $pageNumFactory;
        $this->filtersConvertorFactory = $filtersConvertorFactory;
        $this->filterManager = $filterManager;
    }

    /**
     * Modify meta data
     * event: layout_generate_blocks_after
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $fullActionName = $observer->getFullActionName();

        $title = $this->pageConfig->getTitle();
        if ($title instanceof \Magento\Framework\View\Page\Title && $title->getShortHeading()) {
            if ($this->helperData->isAddLayeredFiltersToMetaTitle()) {
                $this->addLayeredFiltersToMetaTitle($title, $fullActionName);
            }

            if (!$this->helperData->isAddPageNumToMetaTitleDisable()) {
                $this->addPageNumToTitle($title, $fullActionName);
            }
        }

        if ($this->pageConfig->getDescription()) {
            if ($this->helperData->isAddLayeredFiltersToMetaDescription()) {
                $this->addLayeredFiltersToMetaDescription($fullActionName);
            }

            if (!$this->helperData->isAddPageNumToMetaDescriptionDisable()) {
                $this->addPageNumToMetaDescription($fullActionName);
            }
        }
    }

    /**
     *
     * @param \Magento\Framework\View\Page\Title $title
     * @param string $fullActionName
     * @return void
     */
    protected function addPageNumToTitle(\Magento\Framework\View\Page\Title $title, $fullActionName)
    {
        $pageNum = $this->getCurrentPageNum($fullActionName);
        $pageStringPart = __('Page') . ' ' .  $pageNum;

        if (!$pageNum) {
            return;
        }

        if ($this->helperData->isAddPageNumToBeginningMetaTitle()) {
            $title->set($pageStringPart . ' | ' . $title->getShortHeading());
        } elseif ($this->helperData->isAddPageNumToEndMetaTitle()) {
            $title->set($title->getShortHeading() . ' | ' . $pageStringPart);
        }
    }

    /**
     *
     * @param string $fullActionName
     * @return void
     */
    protected function addPageNumToMetaDescription($fullActionName)
    {
        $pageNum = $this->getCurrentPageNum($fullActionName);
        if (!$pageNum) {
            return;
        }

        $pageStringPart = __('Page') . ' ' .  $pageNum;

        if ($this->helperData->isAddPageNumToBeginningMetaDescription()) {
            $this->pageConfig->setDescription($pageStringPart . ' | ' . $this->pageConfig->getDescription());
        } elseif ($this->helperData->isAddPageNumToEndMetaDescription()) {
            $this->pageConfig->setDescription($this->pageConfig->getDescription() . ' | ' . $pageStringPart);
        }
    }

    /**
     *
     * @param string $fullActionName
     * @return int
     */
    protected function getCurrentPageNum($fullActionName)
    {
        if ($this->currentPageNum === null) {
            /**
             * @var \MageWorx\SeoExtended\Model\PageNumInterface
             */
            $pageNumModel = $this->pageNumFactory->create($fullActionName);
            $this->currentPageNum = $pageNumModel ? $pageNumModel->getCurrentPageNum() : false;
        }
        return $this->currentPageNum;
    }

    /**
     *
     * @param \Magento\Framework\View\Page\Title $title
     * @param string $fullActionName
     */
    protected function addLayeredFiltersToMetaTitle(\Magento\Framework\View\Page\Title $title, $fullActionName)
    {
        $filtersString = $this->getStringByFilters($fullActionName);
        if ($filtersString) {
            $title->set($title->getShortHeading() . ' | ' . $filtersString);
        }
    }

    /**
     *
     * @param \Magento\Framework\View\Page\Title $title
     * @param string $fullActionName
     */
    protected function addLayeredFiltersToMetaDescription($fullActionName)
    {
        $filtersString = $this->getStringByFilters($fullActionName);
        if ($filtersString) {
            ($this->pageConfig->setDescription($this->pageConfig->getDescription() . ' | ' . $filtersString));
        }
    }

    /**
     *
     * @param string $fullActionName
     * @return string
     */
    protected function getStringByFilters($fullActionName)
    {
        if ($this->filtersString === null) {
            /**
             * @var \MageWorx\SeoExtended\Model\FiltersConvertorInterface
             */
            $layeredFiltersGetterModel = $this->filtersConvertorFactory->create($fullActionName);
            $this->filtersString = $layeredFiltersGetterModel ? $layeredFiltersGetterModel->getStringByFilters() : '';
        }

        return $this->stripTags($this->filtersString);
    }

    /**
     * Wrapper for standard strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string|null $allowableTags
     * @param bool $allowHtmlEntities
     * @return string
     */
    protected function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        return $this->filterManager->stripTags(
            $data,
            [
                'allowableTags' => $allowableTags,
                'escape' => $allowHtmlEntities
            ]
        );
    }
}
