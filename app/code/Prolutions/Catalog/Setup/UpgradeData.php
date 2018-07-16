<?php

namespace Prolutions\Catalog\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;
    /** @var \Magento\Eav\Setup\EavSetupFactory  */
    protected $eavSetupFactory;

    /** @var \Prolutions\Catalog\Helper\Attribute  */
    protected $attributeHelper;

    /** @var \Magento\Catalog\Model\CategoryRepository  */
    protected $categoryRepository;

    /** @var \Magento\Framework\Registry  */
    protected $_registry;

    /**
     * Construct
     *
     * @param \\Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Prolutions\Catalog\Helper\Attribute $attributeHelper,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeHelper = $attributeHelper;
        $this->categoryRepository = $categoryRepository;
        $this->_registry = $registry;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $rootNodeId = 2;

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $repository = $objectManager->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class);

            //Boat Covers
            $category = $objectManager->create('Magento\Catalog\Model\Category', ['data' =>[
                "parent_id" => $rootNodeId,
                "name" => "Boat Covers",
                "is_active" => true,
                "position" => 10,
                "include_in_menu" => true,
            ]]);
            $category->setCustomAttributes([
                "display_mode"=> "PRODUCTS",
                "is_anchor"=> "1",
                "custom_use_parent_settings"=> "0",
                "custom_apply_to_products"=> "0",
                "url_key"=> "boat-covers",
                "automatic_sorting"=> "0",
            ]);

            $repository->save($category);

            //Pontoon Covers
            $category = $objectManager->create('Magento\Catalog\Model\Category', ['data' =>[
                "parent_id" => $rootNodeId,
                "name" => "Pontoon Covers",
                "is_active" => true,
                "position" => 10,
                "include_in_menu" => true,
            ]]);
            $category->setCustomAttributes([
                "display_mode"=> "PRODUCTS",
                "is_anchor"=> "1",
                "custom_use_parent_settings"=> "0",
                "custom_apply_to_products"=> "0",
                "url_key"=> "pontoon-covers",
                "automatic_sorting"=> "0",
            ]);

            $repository->save($category);

            //Jet Ski Covers
            $category = $objectManager->create('Magento\Catalog\Model\Category', ['data' =>[
                "parent_id" => $rootNodeId,
                "name" => "Jet Ski Covers",
                "is_active" => true,
                "position" => 10,
                "include_in_menu" => true,
            ]]);
            $category->setCustomAttributes([
                "display_mode"=> "PRODUCTS",
                "is_anchor"=> "1",
                "custom_use_parent_settings"=> "0",
                "custom_apply_to_products"=> "0",
                "url_key"=> "jet-ski-covers",
                "automatic_sorting"=> "0",
            ]);

            $repository->save($category);

            //Bimini Tops
            $category = $objectManager->create('Magento\Catalog\Model\Category', ['data' =>[
                "parent_id" => $rootNodeId,
                "name" => "Bimini Tops",
                "is_active" => true,
                "position" => 10,
                "include_in_menu" => true,
            ]]);
            $category->setCustomAttributes([
                "display_mode"=> "PRODUCTS",
                "is_anchor"=> "1",
                "custom_use_parent_settings"=> "0",
                "custom_apply_to_products"=> "0",
                "url_key"=> "bimini-tops",
                "automatic_sorting"=> "0",
            ]);

            $repository->save($category);


            //Fishing Boat Covers
            $category = $objectManager->create('Magento\Catalog\Model\Category', ['data' =>[
                "parent_id" => $rootNodeId,
                "name" => "Fishing Boat Covers",
                "is_active" => true,
                "position" => 10,
                "include_in_menu" => false,
            ]]);
            $category->setCustomAttributes([
                "display_mode"=> "PRODUCTS",
                "is_anchor"=> "1",
                "custom_use_parent_settings"=> "0",
                "custom_apply_to_products"=> "0",
                "url_key"=> "fishing-boat-covers",
                "automatic_sorting"=> "0",
            ]);

            $repository->save($category);
        }

        if (version_compare($context->getVersion(), '1.0.1') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'content_title',
                [
                    'type' => 'varchar',
                    'label' => 'Page Title',
                    'input' => 'text',
                    'required' => false,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Display Settings',
                    'sort_order' => 220,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                ]
            );
            $eavSetup->addAttributeToSet(\Magento\Catalog\Model\Category::ENTITY, 'Default', 'General', 'content_title');
        }

        if (version_compare($context->getVersion(), '1.0.2') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'content_subtitle',
                [
                    'type' => 'varchar',
                    'label' => 'Page Subtitle',
                    'input' => 'text',
                    'required' => false,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'group' => 'Display Settings',
                    'sort_order' => 230,
                    'is_used_in_grid' => false,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => false,
                ]
            );
            $eavSetup->addAttributeToSet(\Magento\Catalog\Model\Category::ENTITY, 'Default', 'General', 'content_subtitle');
        }

        if (version_compare($context->getVersion(), '1.0.3') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'description',
                [
                    'is_visible_on_front' => 1,
                    'used_in_product_listing' => 1,
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.4') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $qualifications = array(
                'rating_overall' => 'Overall',
                'rating_strength' => 'Strength',
                'rating_mildew' => 'Mildew',
                'rating_straps' => 'Straps',
                'rating_trailerable' => 'Trailerable',
                'rating_sap_pollen' => 'Sap/Pollen',
                'rating_snow' => 'Snow',
                'rating_weather' => 'Weather',
                'rating_waterproof' => 'Waterproof',
                'rating_breathable' => 'Breathable',
                'rating_uv_rays' => 'UV Rays',
                'rating_fade_resistant' => 'Fade Resistant',
                'rating_custom_fit' => 'Custom Fit',
            );

            foreach($qualifications as $code => $name) {
                $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $code);
                $eavSetup->addAttribute(
                    \Magento\Catalog\Model\Product::ENTITY,
                    $code,
                    [
                        'type' => 'text',
                        'backend' => '',
                        'frontend' => '',
                        'label' => $name,
                        'input' => 'text',
                        'class' => '',
                        'source' => '',
                        'global' => 1,
                        'visible' => true,
                        'required' => false,
                        'user_defined' => false,
                        'default' => '',
                        'searchable' => false,
                        'filterable' => false,
                        'comparable' => false,
                        'visible_on_front' => true,
                        'used_in_product_listing' => true,
                        'unique' => false,
                        'apply_to' => ''
                    ]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.5') < 0) {

            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'material_select');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'material_select',/* Custom Attribute Code */
                [
                    'group' => 'Product Details',/* Group name in which you want
                                              to display your custom attribute */
                    'type' => 'int',/* Data type in which formate your value save in database*/
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Material', /* lablel of your attribute*/
                    'input' => 'select',
                    'class' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    /*Scope of your attribute */
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false
                ]
            );

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'material_final_name');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'material_final_name',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Material Final Name',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'material_description');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'material_description',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Material Description',
                    'input' => 'textarea',
                    'class' => '',
                    'source' => '',
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'fit_select');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'fit_select',/* Custom Attribute Code */
                [
                    'group' => 'Product Details',/* Group name in which you want
                                              to display your custom attribute */
                    'type' => 'int',/* Data type in which formate your value save in database*/
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Fit', /* lablel of your attribute*/
                    'input' => 'select',
                    'class' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                    /*Scope of your attribute */
                    'visible' => true,
                    'required' => false,
                    'user_defined' => true,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false
                ]
            );

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'fit_final_name');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'fit_final_name',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Fit Final',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'warranty');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'warranty',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Warranty',
                    'input' => 'text',
                    'class' => '',
                    'source' => '',
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.6') < 0) {

            $manufacturerId = $this->attributeHelper->createOrGetId('manufacturer', 'ARCTIC CAT - PERSONAL WATERCRAFT');
            $manufacturerId = $this->attributeHelper->createOrGetId('manufacturer', 'HONDA - PERSONAL WATERCRAFT');
            $manufacturerId = $this->attributeHelper->createOrGetId('manufacturer', 'KAWASAKI - PERSONAL WATERCRAFT');
            $manufacturerId = $this->attributeHelper->createOrGetId('manufacturer', 'POLARIS - PERSONAL WATERCRAFT');
            $manufacturerId = $this->attributeHelper->createOrGetId('manufacturer', 'SEA DOO - PERSONAL WATERCRAFT');
            $manufacturerId = $this->attributeHelper->createOrGetId('manufacturer', 'YAMAHA - PERSONAL WATERCRAFT');

            $manufacturerId = $this->attributeHelper->createOrGetId('fit_select', 'Custom Fit Covers');
            $manufacturerId = $this->attributeHelper->createOrGetId('fit_select', 'Flex-Fit Covers');
            $manufacturerId = $this->attributeHelper->createOrGetId('fit_select', 'Styled-To-Fit Covers');

            $manufacturerId = $this->attributeHelper->createOrGetId('material_select', '11 oz. Poly-Cotton');
            $manufacturerId = $this->attributeHelper->createOrGetId('material_select', '7 oz. Sun-DURA');
            $manufacturerId = $this->attributeHelper->createOrGetId('material_select', '8 oz. Performance Poly-Guard');
            $manufacturerId = $this->attributeHelper->createOrGetId('material_select', '9.25 oz. Sunbrella Acrylic');
            $manufacturerId = $this->attributeHelper->createOrGetId('material_select', '7 oz. Poly-Flex');
            $manufacturerId = $this->attributeHelper->createOrGetId('material_select', '10 oz. Cotton Duck');
            $manufacturerId = $this->attributeHelper->createOrGetId('material_select', '8 oz. Camouflage - BC');

        }

        if (version_compare($context->getVersion(), '1.0.7') < 0) {
            $jetSkiCategory = $this->_categoryFactory->create()->getCollection()->addAttributeToFilter('name','Jet Ski Covers')->getFirstItem();

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $repository = $objectManager->get(\Magento\Catalog\Api\CategoryRepositoryInterface::class);

            //Jet Ski Subcategories
            $category = $objectManager->create('Magento\Catalog\Model\Category', ['data' =>[
                "parent_id" => $jetSkiCategory->getId(),
                "name" => "Up to 120'' Long",
                "is_active" => true,
                "position" => 10,
                "include_in_menu" => false,
            ]]);
            $category->setCustomAttributes([
                "display_mode"=> "PRODUCTS",
                "is_anchor"=> "1",
                "custom_use_parent_settings"=> "0",
                "custom_apply_to_products"=> "0",
                "url_key"=> "up-to-120-long",
                "automatic_sorting"=> "0",
            ]);
            $repository->save($category);

            $category = $objectManager->create('Magento\Catalog\Model\Category', ['data' =>[
                "parent_id" => $jetSkiCategory->getId(),
                "name" => "Up to 128'' Long",
                "is_active" => true,
                "position" => 10,
                "include_in_menu" => false,
            ]]);
            $category->setCustomAttributes([
                "display_mode"=> "PRODUCTS",
                "is_anchor"=> "1",
                "custom_use_parent_settings"=> "0",
                "custom_apply_to_products"=> "0",
                "url_key"=> "up-to-128-long",
                "automatic_sorting"=> "0",
            ]);
            $repository->save($category);

            $category = $objectManager->create('Magento\Catalog\Model\Category', ['data' =>[
                "parent_id" => $jetSkiCategory->getId(),
                "name" => "Up to 135'' Long",
                "is_active" => true,
                "position" => 10,
                "include_in_menu" => false,
            ]]);
            $category->setCustomAttributes([
                "display_mode"=> "PRODUCTS",
                "is_anchor"=> "1",
                "custom_use_parent_settings"=> "0",
                "custom_apply_to_products"=> "0",
                "url_key"=> "up-to-135-long",
                "automatic_sorting"=> "0",
            ]);
            $repository->save($category);

            $category = $objectManager->create('Magento\Catalog\Model\Category', ['data' =>[
                "parent_id" => $jetSkiCategory->getId(),
                "name" => "Up to 162'' Long",
                "is_active" => true,
                "position" => 10,
                "include_in_menu" => false,
            ]]);
            $category->setCustomAttributes([
                "display_mode"=> "PRODUCTS",
                "is_anchor"=> "1",
                "custom_use_parent_settings"=> "0",
                "custom_apply_to_products"=> "0",
                "url_key"=> "up-to-162-long",
                "automatic_sorting"=> "0",
            ]);

            $repository->save($category);
        }

        if (version_compare($context->getVersion(), '1.0.8') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Category::ENTITY, 'grid_columns');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Category::ENTITY,
                'grid_columns',
                [
                    'type' => 'int',
                    'frontend' => '',
                    'label' => 'Grid Columns',
                    'input' => 'select',
                    'class' => '',
                    'source' => '',
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'required' => false,
                    'group' => 'General',
                    'sort_order' => 210,
                    'options' => array(3,4)
                ]
            );
            $eavSetup->addAttributeToSet(\Magento\Catalog\Model\Category::ENTITY, 'Default', 'General', 'grid_columns');
        }

        if (version_compare($context->getVersion(), '1.0.9') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'material_features');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'material_features',
                [
                    'type' => 'text',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Material Features',
                    'input' => 'textarea',
                    'class' => '',
                    'source' => '',
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }

        if (version_compare($context->getVersion(), '1.0.10') < 0) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

            $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'material_whats_included');
            $eavSetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                'material_whats_included',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'What\'s Included Block',
                    'input' => 'select',
                    'class' => '',
                    'source' => 'Magento\Catalog\Model\Category\Attribute\Source\Page',
                    'global' => 1,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                    'apply_to' => ''
                ]
            );
        }
        if (version_compare($context->getVersion(), '1.0.11') < 0) {
            #$setup->getConnection()->query("UPDATE amasty_finder_value SET value = REPLACE (value, ' - PERSONAL WATERCRAFT', '') WHERE  value LIKE '%PERSONAL WATERCRAFT%'");
        }
        if (version_compare($context->getVersion(), '1.0.12') < 0) {
            $setup->getConnection()->query("UPDATE amasty_finder_value SET name = REPLACE (name, ' - PERSONAL WATERCRAFT', '') WHERE  name LIKE '%PERSONAL WATERCRAFT%'");
            $setup->getConnection()->query("UPDATE amasty_finder_value SET name = REPLACE (name, '-PERSONAL WATERCRAFT', '') WHERE  name LIKE '%PERSONAL WATERCRAFT%'");
            $setup->getConnection()->query("UPDATE amasty_finder_value SET name = REPLACE (name, ' PERSONAL WATERCRAFT', '') WHERE  name LIKE '%PERSONAL WATERCRAFT%'");
        }
        if (version_compare($context->getVersion(), '1.0.13') < 0) {
        	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
			$defaultId = $eavSetup->getDefaultAttributeSetId(\Magento\Catalog\Model\Product::ENTITY);
		    $model = $objectManager->create('Magento\Eav\Api\Data\AttributeSetInterface')
		    ->setId(null)
		    ->setEntityTypeId(4)
		    ->setAttributeSetName('Jet Ski');

		    $objectManager
		    ->create('Magento\Eav\Api\AttributeSetManagementInterface')
		    ->create(\Magento\Catalog\Model\Product::ENTITY, $model, $defaultId)
		    ->save();

		    $attrSetId = $setup->getConnection()->fetchOne("SELECT attribute_set_id FROM eav_attribute_set WHERE  attribute_set_name = 'Jet Ski'");

		    $setup->getConnection()->query("UPDATE catalog_product_entity SET attribute_set_id = ".$attrSetId." WHERE attribute_set_id = 4");

        }

        if (version_compare($context->getVersion(), '1.0.13') < 0) {
            $this->_registry->register('isSecureArea', true);

            $jetSkiCategory = $this->_categoryFactory->create()->getCollection()->addAttributeToFilter('url_key','jet-ski-covers')->getFirstItem();

            $subcategories = $jetSkiCategory->getChildrenCategories();
            foreach($subcategories as $subcategory) {
                $this->categoryRepository->delete($subcategory);
            }

            $childCategories = array(
                array(
                    'name' => 'Stand-up/1 Seater Jet Ski',
                    'url-key' => '1-seater-jet-ski'
                ),
                array(
                    'name' => '1-2 Seater Jet Ski',
                    'url-key' => '1-2-seater-jet-ski'
                ),
                array(
                    'name' => '2 Seater Jet Ski',
                    'url-key' => '2-seater-jet-ski'
                ),
                array(
                    'name' => '2-3 Seater Jet Ski',
                    'url-key' => '2-3-seater-jet-ski'
                ),
                array(
                    'name' => '3-4 Seater Jet Ski',
                    'url-key' => '3-4-seater-jet-ski'
                ),
                array(
                    'name' => '4-5 Seater Jet Ski',
                    'url-key' => '4-5-seater-jet-ski'
                )
            );

            $position = 10;

            foreach($childCategories as $childCategory){


                $category = $this->_categoryFactory->create(['data' =>[
                    "parent_id" => $jetSkiCategory->getId(),
                    "name" => $childCategory['name'],
                    "is_active" => true,
                    "position" => $position,
                    "include_in_menu" => false,
                ]]);
                $category->setCustomAttributes([
                    "display_mode"=> "PRODUCTS",
                    "is_anchor"=> "1",
                    "custom_use_parent_settings"=> "0",
                    "custom_apply_to_products"=> "0",
                    "url_key"=> $childCategory['url-key'],
                    "automatic_sorting"=> "0",
                ]);
                $this->categoryRepository->save($category);

                $position += 10;
            }

        }

        $setup->endSetup();
    }
}
