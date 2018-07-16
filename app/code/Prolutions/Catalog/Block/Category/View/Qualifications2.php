<?php

namespace Prolutions\Catalog\Block\Category\View;

use Magento\Catalog\Model\Product;

class Qualifications extends \Magento\Catalog\Block\Category\View
{
    protected $_product;
    protected $_customProductHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Helper\Category $categoryHelper
     * @param \Prolutions\Catalog\Helper\Product $customProductHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Helper\Category $categoryHelper,
        \Prolutions\Catalog\Helper\Product $customProductHelper,
        array $data = []
    ) {
        $this->_customProductHelper = $customProductHelper;
        parent::__construct($context, $layerResolver, $registry, $categoryHelper, $data);
    }

    public function getProductQualifications()
    {
        $product = $this->_product;
        $limit = 5;

        return $this->_customProductHelper->getProductQualifications($product, $limit);
    }

    public function setProduct($product)
    {
        $this->_product = $product;
    }
}