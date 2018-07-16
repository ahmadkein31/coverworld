<?php
/**
 * Copyright © 2015 CommerceExtensions. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace CommerceExtensions\CategoriesImportExport\Controller\Adminhtml\Data;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class ExportPost extends \CommerceExtensions\CategoriesImportExport\Controller\Adminhtml\Data
{
    /**
     * Export action from import/export categories
     *
     * @return ResponseInterface
     */
    public function execute()
    {
        /** start csv content and set template */
		$params = $this->getRequest()->getParams();
		$_resource = $this->_objectManager->create('Magento\Framework\App\ResourceConnection');
		$connection = $_resource->getConnection();
		$_catalog_category_product = $_resource->getTableName('catalog_category_product');
		
		if($params['export_delimiter'] != "") {
			$delimiter = $params['export_delimiter'];
		} else {
			$delimiter = ",";
		}
		if($params['export_enclose'] != "") {
			$enclose = $params['export_enclose'];
		} else {
			$enclose = "\"";
		}
		
        $headers = new \Magento\Framework\DataObject(
            [
                'rootid' => __('rootid'),
                'store' => __('store'),
                'category_id' => __('category_id'),
                'name' => __('name'),
                'categories' => __('categories'),
                'path' => __('path'),
                'level' => __('level'),
                'position' => __('position'),
                'description' => __('description'),
                'url_key' => __('url_key'),
                'is_active' => __('is_active'),
                'display_mode' => __('display_mode'),
                'page_layout' => __('page_layout'),
                'cms_block' => 'cms_block',
                'is_anchor' => __('is_anchor'),
                'meta_title' => __('meta_title'),
                'meta_keywords' => __('meta_keywords'),
                'meta_description' => __('meta_description'),
                'image' => __('category_image'),
                'include_in_menu' => __('include_in_menu'),
                'custom_layout_update' => __('custom_layout_update'),
                'custom_design' => __('custom_design'),
                'custom_use_parent_settings' => __('custom_use_parent_settings'),
                'custom_apply_to_products' => __('custom_apply_to_products'),
                'category_products' => __('category_products'),
            ]
        );
        $template = ''.$enclose.'{{rootid}}'.$enclose.''.$delimiter.''.$enclose.'{{store}}'.$enclose.''.$delimiter.''.$enclose.'{{category_id}}'.$enclose.''.$delimiter.''.$enclose.'{{name}}'.$enclose.''.$delimiter.''.$enclose.'{{categories}}'.$enclose.''.$delimiter.''.$enclose.'{{path}}'.$enclose.''.$delimiter.''.$enclose.'{{level}}'.$enclose.''.$delimiter.''.$enclose.'{{position}}'.$enclose.''.$delimiter.''.$enclose.'{{description}}'.$enclose.''.$delimiter.''.$enclose.'{{url_key}}'.$enclose.''.$delimiter.''.$enclose.'{{is_active}}'.$enclose.''.$delimiter.''.$enclose.'{{display_mode}}'.$enclose.''.$delimiter.''.$enclose.'{{page_layout}}'.$enclose.''.$delimiter.''.$enclose.'{{cms_block}}'.$enclose.''.$delimiter.''.$enclose.'{{is_anchor}}'.$enclose.''.$delimiter.''.$enclose.'{{meta_title}}'.$enclose.''.$delimiter.''.$enclose.'{{meta_keywords}}'.$enclose.''.$delimiter.''.$enclose.'{{meta_description}}'.$enclose.''.$delimiter.''.$enclose.'{{image}}'.$enclose.''.$delimiter.''.$enclose.'{{include_in_menu}}'.$enclose.''.$delimiter.''.$enclose.'{{custom_layout_update}}'.$enclose.''.$delimiter.''.$enclose.'{{custom_design}}'.$enclose.''.$delimiter.''.$enclose.'{{custom_use_parent_settings}}'.$enclose.''.$delimiter.''.$enclose.'{{custom_apply_to_products}}'.$enclose.''.$delimiter.''.$enclose.'{{category_products}}'.$enclose.'';
        $content = $headers->toString($template);
		
		$content .= "\n";
        $storeTemplate = [];
		
		/*
  		if (!$categoryId) {
            $store = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore($storeId);
            $this->getRequest()->setParam('id', (int)$store->getRootCategoryId());
        }
		*/
        foreach ($this->_objectManager->create(
            'Magento\Store\Model\Store'
        )->getCollection()->setLoadDefault(
            false
        ) as $store) {
           // $storeTitle = 'title_' . $store->getId();
           // $content .= ',"' . $store->getCode() . '"';
           // $template .= ',"{{' . $storeTitle . '}}"';
		    #echo "STORE: " . $store->getCode();
		 	$storeTemplate['rootid'] = (int)$store->getRootCategoryId();
            $storeTemplate['store'] = $store->getCode();
			$storeId = $store->getId();
       
			unset($store);
	
			
			
			if($storeTemplate['rootid'] !="") { $rootId = $storeTemplate['rootid']; } else { $rootId = 2; }
		   
			$categories = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($rootId);
			$collection = $categories->getCollection();
			#print_r($categories->getData());
			$collection->addAttributeToSelect(
					'name'
				)->addAttributeToSelect(
					'path'
				)->addAttributeToSelect(
					'position'
				)->addAttributeToSelect(
					'description'
				)->addAttributeToSelect(
					'is_active'
				)->addAttributeToSelect(
					'display_mode'
				)->addAttributeToSelect(
					'page_layout'
				)->addAttributeToSelect(
					'landing_page'
				)->addAttributeToSelect(
					'url_key'
				)->addAttributeToSelect(
					'is_anchor'
				)->addAttributeToSelect(
					'meta_title'
				)->addAttributeToSelect(
					'meta_keywords'
				)->addAttributeToSelect(
					'meta_description'
				)->addAttributeToSelect(
					'image'
				)->addAttributeToSelect(
					'include_in_menu'
				)->addAttributeToSelect(
					'custom_layout_update'
				)->addAttributeToSelect(
					'custom_design'
				)->addAttributeToSelect(
					'custom_use_parent_settings'
				)->addAttributeToSelect(
					'custom_apply_to_products'
				)->setProductStoreId(
					$storeId
				)->setStoreId(
					$storeId
				);
				
			foreach ($collection as $category) {
				#echo "T: " . $category->getData('entity_id');;
				#print_r($category->getData());
				if($category->getData('entity_id') > '2') {
					$storeTemplate['category_id'] = $category->getData('entity_id');
					$storeTemplate['cms_block'] = $category->getData('landing_page');
					
					//Export Category Paths as Names
					$pathArr = explode('/', $category->getPath());
					$namePath = '';
					for ($i=2, $l=sizeof($pathArr); $i<$l; $i++) {
					   if($pathArr[$i] != "") {
							   $name = $collection->getItemById($pathArr[$i])->getName();
							   $namePath .= (empty($namePath) ? '' : '/').trim($name);
					   }
					}
					$storeTemplate['categories'] = $namePath;
					
					//START CUSTOM CODE CATEGORY PRODUCT EXPORT
					if($params['export_products_for_categories'] == "true") {
						$category_products_export = "";
						$select_qry = "SELECT product_id,position FROM ".$_catalog_category_product." WHERE category_id ='".$category->getData('entity_id')."'";
						$catrows = $connection->fetchAll($select_qry);
						foreach($catrows as $catproductdata)
						{ 
							$productCollection = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection')
														->addFieldToFilter('entity_id', array(array($catproductdata['product_id'])))
														->addAttributeToSelect(array('sku'));
							$sku = $productCollection->getFirstItem()->getSku();
							
							if($params['export_product_position'] == "true") {
								$category_products_export .= $sku . ":" . $catproductdata['position'] . ",";
							} else {
								$category_products_export .= $sku . ",";
							}
						}
						$storeTemplate['category_products'] = substr_replace($category_products_export,"",-1);
					}
					//END CUSTOM CODE CATEGORY PRODUCT EXPORT
					$category->addData($storeTemplate);
					$content .= $category->toString($template) . "\n";
				}
				
			}
		}
		#exit;
        //$content .= $template . "\n";
        
        return $this->fileFactory->create('export_categories.csv', $content, DirectoryList::VAR_DIR);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'CommerceExtensions_CategoriesImportExport::import_export'
        );

    }
}
