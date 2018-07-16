<?php

namespace Prolutions\Catalog\Block\Product\View;

class Details extends \Magento\Framework\View\Element\Template
{
    protected $_registry;
    protected $_categoryHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        array $data = []
    )
    {
        $this->_registry = $registry;
        $this->_categoryHelper = $categoryHelper;
        parent::__construct($context, $data);
    }


    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }

    public function getCategoryHelper()
    {
        return $this->_categoryHelper;
    }

}