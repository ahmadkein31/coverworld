<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab;

use Magento\Backend\Block\Widget;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

/**
 * Adminhtml tier price item renderer
 */
class Content extends Widget implements RendererInterface
{
    protected $_export;
    protected $_category;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Amasty\Feed\Model\Export\Product $export,
        \Amasty\Feed\Model\Category $_category,
        array $data = []
    ) {
        $this->_export = $export;
        $this->_category = $_category;

        parent::__construct($context, $data);
    }


    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getFormats()
    {
        return array(
            "as_is" => 'As Is',
            "date" => 'Date',
            "price" => 'Price',
        );
    }

    public function getParentsVars()
    {
        return array(
            'no' => 'No',
            'yes' => 'Yes',
        );
    }

    public function getInventoryAttributes(){
        //all inventory qty,min_qty,use_config_min_qty,is_qty_decimal,backorders,use_config_backorders,min_sale_qty,use_config_min_sale_qty,max_sale_qty,use_config_max_sale_qty,is_in_stock,notify_stock_qty,use_config_notify_stock_qty,manage_stock,use_config_manage_stock,use_config_qty_increments,qty_increments,use_config_enable_qty_inc,enable_qty_increments,is_decimal_divided,website_id
        return array(
            \Amasty\Feed\Model\Export\Product::PREFIX_INVENTORY_ATTRIBUTE . '|qty' => 'Qty',
            \Amasty\Feed\Model\Export\Product::PREFIX_INVENTORY_ATTRIBUTE . '|is_in_stock' => 'Is In Stock',

//            \Amasty\Feed\Model\Export\Product::PREFIX_INVENTORY_ATTRIBUTE . '|backorders' => 'Allow Backorders',
//            \Amasty\Feed\Model\Export\Product::PREFIX_INVENTORY_ATTRIBUTE . '|min_qty' => 'Out Of Stock Qty',
//            \Amasty\Feed\Model\Export\Product::PREFIX_INVENTORY_ATTRIBUTE . '|min_sale_qty' => 'Min Cart Qty',
//            \Amasty\Feed\Model\Export\Product::PREFIX_INVENTORY_ATTRIBUTE . '|max_sale_qty' => 'Max Cart Qty',
//            \Amasty\Feed\Model\Export\Product::PREFIX_INVENTORY_ATTRIBUTE . '|notify_stock_qty' => 'Notify On Stock Below'
        );
    }

    public function getBasicAttributes(){
        return array(
            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE . '|sku' => 'SKU',
            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE . '|product_type' => 'Type',
            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE . '|product_websites' => 'Websites',
            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE . '|created_at' => 'Created',
            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE . '|updated_at' => 'Updated',

//            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE . '|product_id' => 'Product Id',
//            \Amasty\Feed\Model\Export\Product::PREFIX_BASIC_ATTRIBUTE . '|store_id' => 'Store Id',
        );
    }

    public function getCategoryAttributes(){
        $attr = array(
            \Amasty\Feed\Model\Export\Product::PREFIX_CATEGORY_ATTRIBUTE . '|category' => 'Default',
        );

        foreach($this->_category->getSortedCollection() as $category){
            $attr[\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_ATTRIBUTE . '|'.$category->getCode()] = $category->getName();
        }
        return $attr;
    }

    public function getCategoryPathsAttributes(){
        $attr = array(
            \Amasty\Feed\Model\Export\Product::PREFIX_CATEGORY_PATH_ATTRIBUTE . '|category' => 'Default',
        );

        foreach($this->_category->getSortedCollection() as $category){
            $attr[\Amasty\Feed\Model\Export\Product::PREFIX_MAPPED_CATEGORY_PATHS_ATTRIBUTE . '|'.$category->getCode()] = $category->getName();
        }
        return $attr;
    }

    public function getImageAttributes(){
        return array(
            \Amasty\Feed\Model\Export\Product::PREFIX_IMAGE_ATTRIBUTE . '|thumbnail' => 'Thumbnail',
            \Amasty\Feed\Model\Export\Product::PREFIX_IMAGE_ATTRIBUTE . '|image' => 'Base Image',
            \Amasty\Feed\Model\Export\Product::PREFIX_IMAGE_ATTRIBUTE . '|small_image' => 'Small Image',
        );
    }

    public function getGalleryAttributes(){
        return array(
            \Amasty\Feed\Model\Export\Product::PREFIX_GALLERY_ATTRIBUTE . '|image_1' => 'Image 1',
            \Amasty\Feed\Model\Export\Product::PREFIX_GALLERY_ATTRIBUTE . '|image_2' => 'Image 2',
            \Amasty\Feed\Model\Export\Product::PREFIX_GALLERY_ATTRIBUTE . '|image_3' => 'Image 3',
            \Amasty\Feed\Model\Export\Product::PREFIX_GALLERY_ATTRIBUTE . '|image_4' => 'Image 4',
            \Amasty\Feed\Model\Export\Product::PREFIX_GALLERY_ATTRIBUTE . '|image_5' => 'Image 5',
        );
    }

    public function getPriceAttributes(){
        return array(
            \Amasty\Feed\Model\Export\Product::PREFIX_PRICE_ATTRIBUTE . '|price' => 'Price',
            \Amasty\Feed\Model\Export\Product::PREFIX_PRICE_ATTRIBUTE . '|final_price' => 'Final Price',
            \Amasty\Feed\Model\Export\Product::PREFIX_PRICE_ATTRIBUTE . '|min_price' => 'Min Price',
            \Amasty\Feed\Model\Export\Product::PREFIX_PRICE_ATTRIBUTE . '|max_price' => 'Max Price',
            \Amasty\Feed\Model\Export\Product::PREFIX_PRICE_ATTRIBUTE . '|tax_price' => 'Price with TAX(VAT)',
        );
    }

    public function getUrlAttributes(){
        return array(
            \Amasty\Feed\Model\Export\Product::PREFIX_URL_ATTRIBUTE . '|short' => 'Short',
            \Amasty\Feed\Model\Export\Product::PREFIX_URL_ATTRIBUTE . '|with_category' => 'With Category',
        );
    }

    public function getProductAttributes()
    {
        $attributes = array();
        $codes = $this->_export->getExportAttrCodesList();
        foreach($codes as $code => $title){
            $attributes[\Amasty\Feed\Model\Export\Product::PREFIX_PRODUCT_ATTRIBUTE . "|" . $code] = $title;
        }
        return $attributes;
    }

    public function getModiftVars()
    {
        $ret = array(
            "strip_tags" => 'Strip Tags',
            "html_escape" => 'Html Escape',
            "lowercase" => 'Lowercase',
            "integer" => 'Integer',
            'length' => 'Length',
            "prepend" => 'Prepend',
            "append" => 'Append',
            "replace" => 'Replace'
        );
        return $ret;
    }

    public function getArgs()
    {
        $args = array(
            'replace' => array(
                __('From'),
                __('To'),
            ),
            "prepend" => array(
                __('Text'),
            ),
            "append" => array(
                __('Text'),
            ),
            'length' => array(
                __('Max Length'),
            ),
        );
        return $args;
    }
}