<?php

namespace Prolutions\HomeCategories\Block;

use Magento\Framework\View\Element\Template;

class Grid extends Template
{
    protected $_categoryHelper;
    protected $_categoryCollect;
    //protected $_storeManager;
// removed by Ralph from constructor to resolve compiler error    
//\Magento\Store\Model\StoreManagerInterface $storeManager
    public function __construct( \Magento\Framework\View\Element\Template\Context $context,
                                 \Magento\Catalog\Helper\Category $categoryHelper,
                                 \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
                                 )
    {
        $this->_categoryHelper = $categoryHelper;
        $this->_categoryCollection = $categoryCollection;
        //$this->_storeManager = $storeManager;
        parent::__construct($context);
    }
    /**
     * Return categories helper
     */
    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }

    public function getCategoryCollection()
    {
        $collection = $this->_categoryCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('parent_id', $this->_storeManager->getStore()->getRootCategoryId())
            ->addAttributeToFilter('is_active', 1);

        return $collection;
    }

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/category/';
    }
}