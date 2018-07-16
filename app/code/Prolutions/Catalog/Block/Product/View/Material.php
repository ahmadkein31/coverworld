<?php

namespace Prolutions\Catalog\Block\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;

class Material extends \Magento\Catalog\Block\Product\View
{

    public function getMaterialDescription()
    {
        $optionText = $this->_getProductMaterialText();

        switch($optionText){
            case '7 oz. Sun-DURA':
                $html = '100% Solution Dyed Polyester means Sun-DURA™ colors are part of the polyester fiber itself, rather than the dye adhering only to the surface of the fibers. 7oz. marine grade polyester that offers superior resistance to harmful UV rays, fading, mildew, tearing and overall wear-and-tear. Our own unique finish provides exceptional water repellency while maintaining breathability for the health of the boat. Made in the USA.';
                break;
            case '8 oz. Performance Poly-Guard':
                $html = '100% Marine-grade polyester. Highest tear strength available. 8 oz, strong and durable. UV and mildew resistant. Excellent resistance to fading. Extremely water repellant. Breathable to help allow interior moisture to escape. Made in the USA.';
                break;
            default:
                $html = '';
                break;
        }

        return $html;
    }

    public function getFeatures()
    {
        $optionText = $this->_getProductMaterialText();

        switch($optionText){
            case '7 oz. Sun-DURA':
                $bullets = array(
                    '100% Solution Dyed Polyester (Sun-DURA™ Exclusive)',
                    'Made in the USA',
                    '7 Year Warranty',
                    'UV and Fade Resistant',
                    '1/4″ shock cord encased in hem for a snug fit against the hull.',
                    'Strong, reinforced tie-down loops sewn into cover.',
                    'Package includes (six) tie-down straps',
                    'Finished seams – no raw edges'
                );
                break;
            case '8 oz. Performance Poly-Guard':
                $bullets = array(
                    '100% Marine-grade polyester (Performance Poly-Guard® Exclusive)',
                    'Made in the USA',
                    '8 oz Strong & Durable',
                    '5 Year Warranty',
                    '1/4″ shock cord encased in hem for a snug fit against the hull.',
                    'Strong, reinforced tie-down loops sewn into cover.',
                    'Package includes (six) tie-down straps',
                    'Finished seams – no raw edges'
                );
                break;
            default:
                $bullets = array();
                break;
        }

        return $bullets;
    }

    protected function _getProductMaterialText()
    {
        $product = $this->getProduct();

        $attr = $product->getResource()->getAttribute('material_select');
        $optionText = '';
        if ($attr->usesSource()) {
            $optionText = $attr->getSource()->getOptionText($product->getMaterialSelect());
        }
        return $optionText;
    }
}