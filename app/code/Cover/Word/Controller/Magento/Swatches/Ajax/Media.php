<?php

namespace Cover\Word\Controller\Magento\Swatches\Ajax;

use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\Product;

class Media extends \Magento\Swatches\Controller\Ajax\Media
{

	/**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModelFactory;


	public function execute()
	{
		$productMedia = [];
        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            $currentConfigurable = $this->productModelFactory->create()->load($productId);
            $attributes = (array)$this->getRequest()->getParam('attributes');
            if (!empty($attributes)) {
                $product = $this->getProductVariationWithMedia($currentConfigurable, $attributes);
            }
            if ((empty($product) || (!$product->getImage() || $product->getImage() == 'no_selection'))
                && isset($currentConfigurable)
            ) {
                $product = $currentConfigurable;
            }
            $productMedia = $this->swatchHelper->getProductMediaGallery($product);
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $gallar_arry  =   [];

        $_objectManager =   \Magento\Framework\App\ObjectManager::getInstance(); 
        $storeManager   =   $_objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
        $currentStore   =   $storeManager->getStore();
        $mediaUrl       =   $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        

        $currentproduct =   $_objectManager->create('Magento\Catalog\Model\Product')->load($productId);
        $moter_type     =   $currentproduct->getMotor();
        if(!empty($productMedia['gallery'])){
            foreach ($productMedia['gallery'] as $key => $value) {

                $full_image_path    =  $value['small'];
                
                $exp                =  explode("/", $full_image_path);
                $image_name         =  end($exp);

                $pieces = explode("-cover-", $image_name);

                $actual_image_name  =  isset($pieces[1])?$pieces[1]:'';

                if($actual_image_name){
                    $actual_image_name  =  substr($actual_image_name, 0, strpos($actual_image_name, "."));
                }

                $swatch_image       =  $mediaUrl."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.".jpg";
                $swatch_thumb_img   =  $mediaUrl."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.".jpg";
                $fit_img            =  $mediaUrl."catalog/product/default_gallary_image/Fit/fit-".$actual_image_name.".jpg";
                $fit_thumb_img      =  $mediaUrl."catalog/product/default_gallary_image/Fit/50x50/fit-".$actual_image_name.".jpg";
                $outboard_image     =  $mediaUrl."catalog/product/default_gallary_image/Outboard-Motor-Covers/outboard-cover-".$actual_image_name.".jpg";
                $outboard_thumb_img =  $mediaUrl."catalog/product/default_gallary_image/Outboard-Motor-Covers/outboard-cover-".$actual_image_name.".jpg";

                $gallar_arry[0]             =   $value;
                
                $i = 1;
                if((@fopen($swatch_image,"r") == true)){

                    $gallar_arry[$i]['large']    =   $swatch_image;
                    $gallar_arry[$i]['medium']   =   $swatch_image; 
                    $gallar_arry[$i]['small']    =   $swatch_thumb_img;
                    $i += $i;
                }

                if((@fopen($fit_img,"r") == true)){

                    $gallar_arry[$i]['large']    =   $fit_img;
                    $gallar_arry[$i]['medium']   =   $fit_img; 
                    $gallar_arry[$i]['small']    =   $fit_thumb_img;
                    $i += 1;
                }
                
               if($moter_type == 'Outboard'){
                    $gallar_arry[$i]['large']    =   $outboard_image;
                    $gallar_arry[$i]['medium']   =   $outboard_image; 
                    $gallar_arry[$i]['small']    =   $outboard_thumb_img;
                    $i += 1;
                } 
                //Custom code for the swatch images with  found
            $material_final_name = strtolower("-".$currentproduct->getMaterialFinalName());
            $full_image_path    =  $productMedia['small'];
            $exp                =  explode("/", $full_image_path);
            $image_name         =  end($exp);
            if(strpos($material_final_name,"jet ski cover")>0){ 
                $image_name_arr = explode('-',$image_name);
                $image_name_str = $image_name_arr[count($image_name_arr)-1];
                $actual_image_name  =   $image_name_str;
                if($actual_image_name){
                    $actual_image_name      =  substr($actual_image_name, 0, strpos($actual_image_name, "."));
                }
                
                $fileSystem = $_objectManager->create('\Magento\Framework\Filesystem');
                $mediaPath=$fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                
                //echo $mediaPath."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.'.jpg';
                if(!file_exists($mediaPath."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.'.jpg')){
                    $image_name_str = 'performance-'.$image_name_arr[count($image_name_arr)-1];
                }
                $actual_image_name  =   $image_name_str;
                if($actual_image_name){
                    $actual_image_name      =  substr($actual_image_name, 0, strpos($actual_image_name, "."));
                }
                if(!file_exists($mediaPath."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.'.jpg')){
                    $image_name_str = $image_name_arr[count($image_name_arr)-2].'-'.$image_name_arr[count($image_name_arr)-1];
                }
                $actual_image_name  =   $image_name_str;
                if($actual_image_name){
                    $actual_image_name      =  substr($actual_image_name, 0, strpos($actual_image_name, "."));
                }
                 if(!file_exists($mediaPath."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.'.jpg')){
                    $image_name_str = 'performance-'.$image_name_arr[count($image_name_arr)-2].'-'.$image_name_arr[count($image_name_arr)-1];
                }
                $actual_image_name  =   $image_name_str;
                if($actual_image_name){
                    $actual_image_name      =  substr($actual_image_name, 0, strpos($actual_image_name, "."));
                }
                $swatch_image               =  $mediaUrl."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.".jpg";
                $swatch_thumb_img           =  $mediaUrl."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.".jpg";
                $fit_img                    =  $mediaUrl."catalog/product/default_gallary_image/Fit/fit-".$actual_image_name.".jpg";
                $fit_thumb_img              =  $mediaUrl."catalog/product/default_gallary_image/Fit/50x50/fit-".$actual_image_name.".jpg";
                $outboard_image             =  $mediaUrl."catalog/product/default_gallary_image/Outboard-Motor-Covers/swatch-".$actual_image_name.".jpg";
                $outboard_thumb_img         =  $mediaUrl."catalog/product/default_gallary_image/Outboard-Motor-Covers/50x50/swatch-".$actual_image_name.".jpg";
                $gallar_arry[3]['large']    =   $swatch_thumb_img;
                $gallar_arry[3]['medium']      =   $swatch_image; 
                $gallar_arry[3]['small']     =   $swatch_image; 

            }
            //Custom code for the swatch images with  found end

                $gallar_arry[$i]['large']    =   $mediaUrl."catalog/product/default_gallary_image/product5_image.jpg";
                $gallar_arry[$i]['medium']   =   $mediaUrl."catalog/product/default_gallary_image/product5_image.jpg"; 
                $gallar_arry[$i]['small']    =   $mediaUrl."catalog/product/default_gallary_image/product5_thumbnail.jpg";

                $i += 1;
                $gallar_arry[$i]['large']    =   $mediaUrl."catalog/product/default_gallary_image/product6_image.jpg";
                $gallar_arry[$i]['medium']   =   $mediaUrl."catalog/product/default_gallary_image/product6_image.jpg"; 
                $gallar_arry[$i]['small']    =   $mediaUrl."catalog/product/default_gallary_image/product6_thumbnail.jpg";
            }
        }else{
            $gallar_arry[0]['large']        =   $productMedia['large'];
            $gallar_arry[0]['medium']       =   $productMedia['medium'];
            $gallar_arry[0]['small']        =   $productMedia['small'];
            //Custom code for the swatch images with not found
            $material_final_name = strtolower("-".$currentproduct->getMaterialFinalName());
            $full_image_path    =  $productMedia['small'];
            $exp                =  explode("/", $full_image_path);
            $image_name         =  end($exp);
            if(strpos($material_final_name,"jet ski cover")>0){ 
                $image_name_arr = explode('-',$image_name);
                //print_r($image_name_arr);
                $image_name_str = $image_name_arr[count($image_name_arr)-1];
                $actual_image_name  =   $image_name_str;
                if($actual_image_name){
                    $actual_image_name      =  substr($actual_image_name, 0, strpos($actual_image_name, "."));
                }
                
                
                $fileSystem = $_objectManager->create('\Magento\Framework\Filesystem');
                $mediaPath=$fileSystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA)->getAbsolutePath();
                
                //echo $mediaPath."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.'.jpg';
                if(!file_exists($mediaPath."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.'.jpg')){
                    $image_name_str = 'performance-'.$image_name_arr[count($image_name_arr)-1];
                }
                $actual_image_name  =   $image_name_str;
                if($actual_image_name){
                    $actual_image_name      =  substr($actual_image_name, 0, strpos($actual_image_name, "."));
                }
                if(!file_exists($mediaPath."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.'.jpg')){
                    $image_name_str = $image_name_arr[count($image_name_arr)-2].'-'.$image_name_arr[count($image_name_arr)-1];
                }
                $actual_image_name  =   $image_name_str;
                if($actual_image_name){
                    $actual_image_name      =  substr($actual_image_name, 0, strpos($actual_image_name, "."));
                }
                if(!file_exists($mediaPath."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.'.jpg')){
                    $image_name_str = 'performance-'.$image_name_arr[count($image_name_arr)-2].'-'.$image_name_arr[count($image_name_arr)-1];
                }
                $actual_image_name  =   $image_name_str;
                if($actual_image_name){
                    $actual_image_name      =  substr($actual_image_name, 0, strpos($actual_image_name, "."));
                }
                
                $swatch_image               =  $mediaUrl."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.".jpg";
                $swatch_thumb_img           =  $mediaUrl."catalog/product/default_gallary_image/Color-Swatches/swatch-".$actual_image_name.".jpg";
                
                $fit_img                    =  $mediaUrl."catalog/product/default_gallary_image/Fit/fit-".$actual_image_name.".jpg";
                $fit_thumb_img              =  $mediaUrl."catalog/product/default_gallary_image/Fit/50x50/fit-".$actual_image_name.".jpg";
                $outboard_image             =  $mediaUrl."catalog/product/default_gallary_image/Outboard-Motor-Covers/swatch-".$actual_image_name.".jpg";
                $outboard_thumb_img         =  $mediaUrl."catalog/product/default_gallary_image/Outboard-Motor-Covers/50x50/swatch-".$actual_image_name.".jpg";
                $gallar_arry[3]['large']    =   $swatch_thumb_img;
                $gallar_arry[3]['medium']      =   $swatch_image; 
                $gallar_arry[3]['small']     =   $swatch_image; 

            }
            //Custom code for the swatch images with not found end

            $gallar_arry[1]['large']        =   $mediaUrl."catalog/product/default_gallary_image/product5_image.jpg";
            $gallar_arry[1]['medium']       =   $mediaUrl."catalog/product/default_gallary_image/product5_image.jpg"; 
            $gallar_arry[1]['small']        =   $mediaUrl."catalog/product/default_gallary_image/product5_thumbnail.jpg";

            $gallar_arry[2]['large']        =   $mediaUrl."catalog/product/default_gallary_image/product6_image.jpg";
            $gallar_arry[2]['medium']       =   $mediaUrl."catalog/product/default_gallary_image/product6_image.jpg"; 
            $gallar_arry[2]['small']        =   $mediaUrl."catalog/product/default_gallary_image/product6_thumbnail.jpg";

        }
        $productMedia['gallery']        =   $gallar_arry;
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($productMedia);
            return $resultJson;
	}
	
}
	
	