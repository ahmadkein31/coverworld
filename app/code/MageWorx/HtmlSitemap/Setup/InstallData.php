<?php
/**
 * Copyright © 2015 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\HtmlSitemap\Setup;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var Default value fot "in_html_sitemap" attribute
     */
    const IN_SITEMAP_HTML_DEFAULT_VALUE = 1;

    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $catalogSetup */
        $attributeCode = 'in_html_sitemap';
        $catalogSetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            $attributeCode,
            [
                'group' => 'Search Engine Optimization',
                'type' => 'int',
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Boolean',
                'frontend' => '',
                'label' => 'Include in HTML Sitemap',
                'input' => 'select',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => self::IN_SITEMAP_HTML_DEFAULT_VALUE,
                'apply_to' => '',
                'visible_on_front' => false,
                'note' => 'This setting was added by MageWorx HTML Sitemap'
            ]
        );

        $productTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
        $selectProductAttribute = $setup->getConnection()->select();

        $selectProductAttribute
            ->from(
                ['ea' => $setup->getTable('eav_attribute')],
                ['attribute_id']
            )
            ->where("`entity_type_id` = '" . $productTypeId . "'")
            ->where("attribute_code = ?", $attributeCode);

        $productAttributeId = $setup->getConnection()->fetchOne($selectProductAttribute);
        if (is_numeric($productAttributeId)) {
            $productAttributeValueInsert = $setup->getConnection()->select()->from(
                ['e1' => $setup->getTable('catalog_product_entity')],
                [
                    'value_id' => new \Zend_Db_Expr('NULL'),
                    'attribute_id' => new \Zend_Db_Expr($productAttributeId),
                    'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                    'entity_id' => 'e1.entity_id',
                    'value'     => new \Zend_Db_Expr(self::IN_SITEMAP_HTML_DEFAULT_VALUE),
                ]
            )
            ->where('e1.entity_id NOT IN('. new \Zend_Db_Expr(
                "SELECT `entity_id` FROM " . $setup->getTable('catalog_product_entity_int') .
                " WHERE `store_id` = 0 AND `attribute_id` = " . $productAttributeId . ")"
            ))
            ->order(['e1.entity_id'], \Zend_Db_Select::SQL_ASC)
            ->insertFromSelect(
                $setup->getTable('catalog_product_entity_int')
            );
            $setup->run($productAttributeValueInsert);
        }

        $catalogSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            $attributeCode,
            [
                'group' => 'General Information',
                'type' => 'int',
                'backend' => 'Magento\Catalog\Model\Product\Attribute\Backend\Boolean',
                'frontend' => '',
                'label' => 'Include in HTML Sitemap',
                'input' => 'select',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Catalog\Model\ResourceModel\Eav\Attribute::SCOPE_STORE,
                'visible' => true,
                'required' => false,
                'user_defined' => false,
                'default' => self::IN_SITEMAP_HTML_DEFAULT_VALUE,
                'apply_to' => '',
                'visible_on_front' => false,
                'sort_order' => 9,
                'note' => 'This setting was added by MageWorx HTML Sitemap'
            ]
        );

        $categoryTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $selectCategoryAttribute = $setup->getConnection()->select();

        $selectCategoryAttribute
            ->from(
                ['ea' => $setup->getTable('eav_attribute')],
                ['attribute_id']
            )
            ->where("`entity_type_id` = '" . $categoryTypeId . "'")
            ->where("attribute_code = ?", $attributeCode);

        $categoryAttributeId = $setup->getConnection()->fetchOne($selectCategoryAttribute);

        if (is_numeric($categoryAttributeId)) {
            $itemsInsert = $setup->getConnection()->select()->from(
                ['e1' => $setup->getTable('catalog_category_entity')],
                [
                    'value_id' => new \Zend_Db_Expr('NULL'),
                    'attribute_id' => new \Zend_Db_Expr($categoryAttributeId),
                    'store_id' => new \Zend_Db_Expr(\Magento\Store\Model\Store::DEFAULT_STORE_ID),
                    'entity_id' => 'e1.entity_id',
                    'value'     => new \Zend_Db_Expr(self::IN_SITEMAP_HTML_DEFAULT_VALUE),
                ]
            )
            ->where('e1.entity_id NOT IN('. new \Zend_Db_Expr(
                "SELECT `entity_id` FROM " . $setup->getTable('catalog_category_entity_int') .
                " WHERE `store_id` = 0 AND `attribute_id` = " . $categoryAttributeId . ")"
            ))
            ->order(['e1.entity_id'], \Zend_Db_Select::SQL_ASC)
            ->insertFromSelect(
                $setup->getTable('catalog_category_entity_int')
            );
            $setup->run($itemsInsert);
        }
    }
}
