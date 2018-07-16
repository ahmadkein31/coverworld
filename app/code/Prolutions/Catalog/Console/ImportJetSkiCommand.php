<?php
/**
 * Copyright © 2015 Clounce. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Prolutions\Catalog\Console;

use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\App\State;
use Magento\Store\Model\StoreManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * THIS IS SCRIPT IS DESIGNED FOR A PARTICULAR CSV STRUCTURE
 */


class ImportJetSkiCommand extends Command
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        ObjectManagerFactory $objectManagerFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ){
        $params = $_SERVER;
        $params[StoreManager::PARAM_RUN_CODE] = 'admin';
        $params[StoreManager::PARAM_RUN_TYPE] = 'store';
        $this->objectManager = $objectManagerFactory->create($params);
        $this->_categoryFactory = $categoryFactory;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('prolutions:import:jetski')
            ->setDescription('Import Jet Ski products');
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return null|int null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        /**
         * @var \Magento\Framework\Registry
         */
        $registry = $this->objectManager->get('\Magento\Framework\Registry');
        $registry->register('isSecureArea', true);

        $state = $this->objectManager->get('Magento\Framework\App\State');
        $state->setAreaCode('frontend');

        $output->writeln('<info>Starting Jet Ski Import</info>');

        $file = fopen(BP ."/var/import/jet-ski-products.csv", "r");


        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/custom-y-m-m.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);


        $materialFeaturesSun = <<<HTML
        <p class="title bold">It comes with a wide range of features to protect your boat that include:</p>
    <ul>
<li>100% Solution Dyed Polyester (Sun-DURA™ Exclusive)</li>
<li>Made in the USA</li>
<li>7 Year Warranty</li>
<li>UV and Fade Resistant</li>
<li>1/4″ shock cord encased in hem for a snug fit against the hull.</li>
<li>Strong, reinforced tie-down loops sewn into cover.</li>
<li>Package includes (six) tie-down straps</li>
<li>Finished seams – no raw edges</li>
</ul>
HTML;

        $materialFeaturesPerformance = <<<HTML
        <p class="title bold">It comes with a wide range of features to protect your boat that include:</p>
    <ul>
<li>100% Marine-grade polyester (Performance Poly-Guard® Exclusive)</li>
<li>Made in the USA</li>
<li>8 oz Strong & Durable</li>
<li>5 Year Warranty</li>
<li>1/4″ shock cord encased in hem for a snug fit against the hull.</li>
<li>Strong, reinforced tie-down loops sewn into cover.</li>
<li>Package includes (six) tie-down straps</li>
<li>Finished seams – no raw edges</li>
</ul>
HTML;


        $materialsInfo = array(
            '7 oz. Sun-DURA' => array(
                'material_final_name' => 'Sun-DURA™ Boat Cover',
                'material_description' => '100% Solution Dyed Polyester means Sun-DURA™ colors are part of the polyester fiber itself, rather than the dye adhering only to the surface of the fibers. 7oz. marine grade polyester that offers superior resistance to harmful UV rays, fading, mildew, tearing and overall wear-and-tear. Our own unique finish provides exceptional water repellency while maintaining breathability for the health of the boat. Made in the USA.',
                'material_features' => $materialFeaturesSun,
                'warranty' => '7 Year',
                'rating_overall' => 'E4.5',
                'rating_strength' => 'D5',
                'rating_mildew' => 'F5',
                'rating_straps' => '',
                'rating_trailerable' => 'G5',
                'rating_sap_pollen' => 'H5',
                'rating_snow' => '',
                'rating_weather' => '',
                'rating_waterproof' => 'B4.5',
                'rating_breathable' => 'C4.5',
                'rating_uv_rays' => 'I5',
                'rating_fade_resistant' => 'A5',
                'rating_custom_fit' => 'J4.5',
            ),
            '8 oz. Performance Poly-Guard' => array(
                'material_final_name' => 'Performance Poly-Guard™ Boat Cover',
                'material_description' => '100% Marine-grade polyester. Highest tear strength available. 8 oz, strong and durable. UV and mildew resistant. Excellent resistance to fading. Extremely water repellant. Breathable to help allow interior moisture to escape. Made in the USA.',
                'material_features' => $materialFeaturesPerformance,
                'warranty' => '5 Year',
                'rating_overall' => 'E4',
                'rating_strength' => '',
                'rating_mildew' => 'F5',
                'rating_straps' => '',
                'rating_trailerable' => 'J4.5',
                'rating_sap_pollen' => 'G5',
                'rating_snow' => 'H5',
                'rating_weather' => 'I5',
                'rating_waterproof' => 'B5',
                'rating_breathable' => 'D4',
                'rating_uv_rays' => 'C5',
                'rating_fade_resistant' => 'A5',
                'rating_custom_fit' => '',
            ),
        );

        $materialWhatsIncluded = $this->objectManager->get('Magento\Cms\Model\BlockFactory')->create()->load('jet_ski_whats_included')->getBlockId();

        $eavConfig = $this->objectManager->get('Magento\Eav\Model\Config');
        $attribute = $eavConfig->getAttribute('catalog_product', 'color');
        $attrOptions = $attribute->getOptions();

        $attributeRepository = $this->objectManager->get('Magento\Catalog\Model\Product\Attribute\Repository');
        $materialOptions = $attributeRepository->get('material_select')->getOptions();
        $manufacturerOptions = $attributeRepository->get('manufacturer')->getOptions();
        $fitOptions = $attributeRepository->get('fit_select')->getOptions();

        $fitOptionId = '';
        foreach($fitOptions as $fitOption){
            if($fitOption['label'] == 'Styled-To-Fit Covers'){
                $fitOptionId = $fitOption['value'];
            }
        }

        $jetSki120CategoryId = $this->_categoryFactory->create()->getCollection()->addAttributeToFilter('url_key','1-seater-jet-ski')->getFirstItem()->getId();
        $output->writeln('<comment>Category Id: ' . $jetSki120CategoryId . '</comment>');
        $i=1;
        $processedSkus = array();
        $currentSku = 0;
        $attributeValues = array();
        $associatedProductIds = array();
        $lastConfigurableCreated = false;
        $manufacturerOptionId = 0;
        $repeated = false;
        $canStartSimpleProducts = false;

        $productModel = $this->objectManager->get('Magento\Catalog\Model\Product');

        while(!feof($file)) {
            $row = fgetcsv($file);

//            if($i > 300){
//                break;
//            }

            if($i > 1){ //Ignore headers row

                $rowColor = $row[9];
                $rowSku = $row[1];
                $rowMaterial = $row[4];
                $rowPrice = $row[14];
                $rowManufacturer = $row[16];

                if($rowColor && $canStartSimpleProducts){ //If color exists, it's a simple product
                    if(!$repeated) {
                        $colorNumber = 0;
                        $colorName = trim(substr($rowColor, 0, strpos($rowColor, '(')));
                        //                        $output->writeln('<comment>Color Name: ' . $colorName . '</comment>');
                        foreach ($attrOptions as $attrOption) {
                            if ($attrOption->getLabel() == $colorName) {
                                $colorNumber = $attrOption->getValue();
                                //                                $output->writeln('<comment>Color Number: ' . $colorNumber . '</comment>');
                            }
                        }

                        $productFactory = $this->objectManager->get('Magento\Catalog\Model\ProductFactory');
                        $product = $productFactory->create();

                        //set all material data
                        $product->setData($materialsInfo[$lastConfigurableCreated->getMaterialSelectLabel()]);

                        //set material option id
                        $materialOptionId = '';
                        foreach ($materialOptions as $materialOption) {
                            if ($materialOption['label'] == $lastConfigurableCreated->getMaterialSelectLabel()) {
                                $materialOptionId = $materialOption['value'];
                            }
                        }
                        $product->setMaterialSelect($materialOptionId);

                        //                        $product->setMaterialDescription($lastConfigurableCreated->getMaterialDescription()); //Set Material Description

                        $product->setFitSelect($fitOptionId);
                        $product->setFitFinalName('Precision Fit Jet Ski Covers');

                        $product->setManufacturer($manufacturerOptionId);

                        $product->setTypeId('simple')
                            ->setAttributeSetId(4)
                            ->setWebsiteIds([1])
                            ->setCategoryIds(array($jetSki120CategoryId))
                            ->setName($lastConfigurableCreated->getName() . ' - ' . $colorName)
                            ->setSku($lastConfigurableCreated->getSku() . '-' . $colorNumber)
                            ->setColor($colorNumber)
                            ->setPrice($lastConfigurableCreated->getPriceForChild())
                            ->setVisibility(1)
                            ->setStatus(1)
                            ->setMaterialWhatsIncluded($materialWhatsIncluded)
                            ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

                        //                        $output->writeln('<comment>Simple Product Info:</comment>');
                        //                        foreach ($product->getData() as $code => $value) {
                        //                            if (!is_array($value)) {
                        //                                $output->writeln('<info>' . $code . ' - ' . $value . '</info>');
                        //                            }
                        //                        }

                        $product->save();
                        $output->writeln('['.date('Y-m-d H:i:s').'] <question>Simple Product Saved - '.$product->getName().'</question>');

                        /** @var \Magento\CatalogInventory\Model\Stock\Item $stockItem */
                        $stockItem = $this->objectManager->get('Magento\CatalogInventory\Model\Stock\Item');
                        $stockItem->load($product->getId(), 'product_id');

                        if (!$stockItem->getProductId()) {
                            $stockItem->setProductId($product->getId());
                        }

                        $stockItem->setUseConfigManageStock(1);
                        $stockItem->setQty(1000);
                        $stockItem->setIsQtyDecimal(0);
                        $stockItem->setIsInStock(1);
                        $stockItem->save();
                        //                        $output->writeln('<comment>Stock Item Saved</comment>');

                        //                        $output->writeln('<comment>Simple Product Stock Item Info:</comment>');
                        //                        foreach ($stockItem->getData() as $code => $value) {
                        //                            if (!is_array($value)) {
                        //                                $output->writeln('<info>' . $code . ' - ' . $value . '</info>');
                        //                            }
                        //                        }

                        $attributeValues[] = [
                            'label' => 'test',
                            'attribute_id' => $attribute->getId(),
                            'value_index' => $colorName,
                        ];
                        $associatedProductIds[] = $product->getId();
                    } else {
//                        $output->writeln('<question>Skipping Child</question>');
                    }
                } else {
                    if($rowSku){
                        if($lastConfigurableCreated && $lastConfigurableCreated->getSku()){
                            $productId = $lastConfigurableCreated->getId(); // Configurable Product Id
//                            $output->writeln('<comment>ProductId:'.$productId.'</comment>');
                            $attributeModel = $this->objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute');
                            $position = 0;
                            $attributes = array($attribute->getId()); // Super Attribute Ids Used To Create Configurable Product
//                            $output->writeln('<comment>Attribute Id:'.$attribute->getId().'</comment>');
                            foreach ($attributes as $attributeId) {
                                $data = array('attribute_id' => $attributeId, 'product_id' => $productId, 'position' => $position);
//                                $output->writeln('<comment>Saving attribute:'.$attributeId.'-'.$productId.'-'.$position.'</comment>');
                                $position++;
                                $attributeModel->setData($data)->save();
                            }
                            $lastConfigurableCreated->setAffectConfigurableProductAttributes(4);
                            $this->objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->setUsedProductAttributeIds($attributes, $lastConfigurableCreated);
                            $lastConfigurableCreated->setNewVariationsAttributeSetId(4); // Setting Attribute Set Id
                            $lastConfigurableCreated->setAssociatedProductIds($associatedProductIds);// Setting Associated Products
                            $lastConfigurableCreated->setCanSaveConfigurableAttributes(true);
//                            $output->writeln('<comment>Saving configurable product</comment>');
                            $lastConfigurableCreated->save();
                            $output->writeln('['.date('Y-m-d H:i:s').'] <comment>Associated childs to '.$lastConfigurableCreated->getName().'</comment>');

                            $associatedProductIds = array();
                            $processedSkus[] = $lastConfigurableCreated->getSku();
                            $lastConfigurableCreated = false;
                        }

                        $currentSku = $rowSku;

                        if($productModel->getIdBySku($currentSku)){
                            $canStartSimpleProducts = false;
                            continue;
                        }

                        if(!in_array($currentSku, $processedSkus)) {

                            $productFactory = $this->objectManager->get('Magento\Catalog\Model\ProductFactory');
                            $product = $productFactory->create();
                            //set all material data
                            $product->setData($materialsInfo[$rowMaterial]);

                            //set material option id
                            $materialOptionId = '';
                            foreach ($materialOptions as $materialOption) {
                                if ($materialOption['label'] == $rowMaterial) {
                                    $materialOptionId = $materialOption['value'];
                                }
                            }
                            $product->setMaterialSelect($materialOptionId);
                            $product->setMaterialSelectLabel($rowMaterial);

                            //                            $product->setMaterialDescription($row[4]); //Set Material Description

                            $product->setPrice(substr($rowPrice, 1));
                            $product->setPriceForChild(substr($rowPrice, 1));

                            $product->setFitSelect($fitOptionId);
                            $product->setFitFinalName('Precision Fit Jet Ski Covers');

                            $product->setManufacturer($manufacturerOptionId);

                            $product->setTypeId('configurable')
                                ->setAttributeSetId(4)
                                ->setWebsiteIds([1])
                                ->setCategoryIds(array($jetSki120CategoryId))
                                ->setName($currentSku . ' - ' . $row[4])
                                ->setSku($currentSku)
                                ->setVisibility(4)
                                ->setStatus(1)
                                ->setMaterialWhatsIncluded($materialWhatsIncluded)
                                ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);

                            //                            $output->writeln('<comment>Configurable Product Info:</comment>');
                            //                            foreach ($product->getData() as $code => $value) {
                            //                                if (!is_array($value)) {
                            //                                    $output->writeln('<info>' . $code . ' - ' . $value . '</info>');
                            //                                }
                            //                            }

                            $product->save();
                            $output->writeln('['.date('Y-m-d H:i:s').'] <comment>Configurable Product Saved - '.$product->getName().'</comment>');

                            $lastConfigurableCreated = $product;
                            $canStartSimpleProducts = true;
                            $repeated = false;
                        } else {
                            $repeated = true;
                            $output->writeln('<info>SKU REPEATED - '.$currentSku.'</info>');
                        }
                    } else {
                        foreach($manufacturerOptions as $manufacturerOption){
                            if($manufacturerOption['label'] == $rowManufacturer){
                                $manufacturerOptionId = $manufacturerOption['value'];
//                                $output->writeln('<comment>Manufacturer Selected: '.$manufacturerOption['label'].' - '.$manufacturerOptionId.'</comment>');
                            }
                        }
                        if($lastConfigurableCreated && $lastConfigurableCreated->getSku()){
                            $productId = $lastConfigurableCreated->getId(); // Configurable Product Id
//                            $output->writeln('<comment>ProductId:'.$productId.'</comment>');
                            $attributeModel = $this->objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute');
                            $position = 0;
                            $attributes = array($attribute->getId()); // Super Attribute Ids Used To Create Configurable Product
//                            $output->writeln('<comment>Attribute Id:'.$attribute->getId().'</comment>');
                            foreach ($attributes as $attributeId) {
                                $data = array('attribute_id' => $attributeId, 'product_id' => $productId, 'position' => $position);
//                                $output->writeln('<comment>Saving attribute:'.$attributeId.'-'.$productId.'-'.$position.'</comment>');
                                $position++;
                                $attributeModel->setData($data)->save();
                            }
                            $lastConfigurableCreated->setAffectConfigurableProductAttributes(4);
                            $this->objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->setUsedProductAttributeIds($attributes, $lastConfigurableCreated);
                            $lastConfigurableCreated->setNewVariationsAttributeSetId(4); // Setting Attribute Set Id
                            $lastConfigurableCreated->setAssociatedProductIds($associatedProductIds);// Setting Associated Products
                            $lastConfigurableCreated->setCanSaveConfigurableAttributes(true);
//                            $output->writeln('<comment>Saving configurable product</comment>');
                            $lastConfigurableCreated->save();
                            $output->writeln('['.date('Y-m-d H:i:s').'] <comment>Associated childs to '.$lastConfigurableCreated->getName().'</comment>');

                            $associatedProductIds = array();
                            $processedSkus[] = $lastConfigurableCreated->getSku();
                            $lastConfigurableCreated = false;
                        }
                    }
                }
            }
            $i++;
        }


        fclose($file);

//        $output->writeln('<comment>-------- Last Step --------</comment>');
        if($lastConfigurableCreated && $lastConfigurableCreated->getSku()) {
            $productId = $lastConfigurableCreated->getId(); // Configurable Product Id
//            $output->writeln('<comment>ProductId:' . $productId . '</comment>');
            $attributeModel = $this->objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute');
            $position = 0;
            $attributes = array($attribute->getId()); // Super Attribute Ids Used To Create Configurable Product
//            $output->writeln('<comment>Attribute Id:' . $attribute->getId() . '</comment>');
            foreach ($attributes as $attributeId) {
                $data = array('attribute_id' => $attributeId, 'product_id' => $productId, 'position' => $position);
//                $output->writeln('<comment>Saving attribute:' . $attributeId . '-' . $productId . '-' . $position . '</comment>');
                $position++;
                $attributeModel->setData($data)->save();
            }
            $lastConfigurableCreated->setAffectConfigurableProductAttributes(4);
            $this->objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable')->setUsedProductAttributeIds($attributes, $lastConfigurableCreated);
            $lastConfigurableCreated->setNewVariationsAttributeSetId(4); // Setting Attribute Set Id
            $lastConfigurableCreated->setAssociatedProductIds($associatedProductIds);// Setting Associated Products
            $lastConfigurableCreated->setCanSaveConfigurableAttributes(true);
//            $output->writeln('<comment>Saving configurable product</comment>');
            $lastConfigurableCreated->save();
            $output->writeln('['.date('Y-m-d H:i:s').'] <comment>Associated childs to '.$lastConfigurableCreated->getName().'</comment>');
        }

        return 0;
    }
}