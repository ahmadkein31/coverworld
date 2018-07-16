<?php

namespace Prolutions\Catalog\Block\Category;

class View extends \Magento\Catalog\Block\Category\View
{
    protected $_filterProvider;
    protected $_storeManager;
    protected $_blockFactory;


    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        array $data = []
    )
    {
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
        $this->_filterProvider = $filterProvider;
        $this->_storeManager = $storeManager;
        $this->_blockFactory = $blockFactory;
    }

    public function getTitle()
    {
        return $this->getCurrentCategory()->getContentTitle();
    }

    public function getSubtitle()
    {
        return $this->getCurrentCategory()->getContentSubtitle();
    }

    public function getMaxChildLevel()
    {
        $maxChildLevel = $this->_categoryHelper->getMaxChildLevel($this->getCurrentCategory());

        return $maxChildLevel;
    }

    public function getSubcategories()
    {
        $collection = $this->getCurrentCategory()->getCollection();
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection->addAttributeToSelect(
            'url_key'
        )->addAttributeToSelect(
            'image'
        )->addAttributeToSelect(
            'name'
        )->addAttributeToSelect(
            'all_children'
        )->addAttributeToSelect(
            'is_anchor'
        )->addAttributeToFilter(
            'is_active',
            1
        )->addIdFilter(
            $this->getCurrentCategory()->getChildren()
        )->setOrder(
            'position',
            \Magento\Framework\DB\Select::SQL_ASC
        )->joinUrlRewrite()->load();

        return $collection;
    }

    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }

    public function getParentCategories()
    {
        return $this->_categoryHelper->getParentCategories($this->getCurrentCategory());
    }

    public function getInformationTabsHtml()
    {
        $blockIdentifier = str_replace(' ', '_', strtolower($this->getCurrentCategory()->getName()));
        $blockIdentifier .= '_tabs';
        $html = '';
        if ($blockIdentifier) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var \Magento\Cms\Model\Block $block */
            $block = $this->_blockFactory->create();
            $block->setStoreId($storeId)->load($blockIdentifier);

            $html = $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
        }
        return $html;
    }

    public function getColumnsClass()
    {
        $columnsClass = '';
        if ($columns = $this->getCurrentCategory()->getGridColumns()) {
            $columnsClass = 'columns-' . $columns;
        }

        return $columnsClass;
    }

    public function getFirstStepTitle()
    {
        switch($this->getCurrentCategory()->getName()){   
            case stripos($this->getCurrentCategory()->getName(),'Jet Ski') !== false:   
                $title = 'Select Size';
                break;
            default:
                $title = 'Select Type';
                break;
        }
        return $this->getCurrentCategory()->getName();
    }
    
    public function getCategoryTitle()
    {                                              
        switch($this->getCurrentCategory()->getName()){
            case $this->getCurrentCategory()->getName() == 'Jet Ski Covers':   
                $titleCat = 'Shop by Size';
                break;
            case stripos($this->getCurrentCategory()->getName(),'Jet Ski') !== false:   
                $titleCat = 'View '.$this->getCurrentCategory()->getName().' Covers';
                break;
            default:
                $titleCat = 'Shop by Type';
                break;
        }
        return $this->getCurrentCategory()->getName();                 
    }
}