<?php

namespace Summa\EnhancedNavigation\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /** @var \Magento\Eav\Setup\EavSetupFactory  */
    protected $eavSetupFactory;

    /**
     * InstallData constructor.
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Eav\Setup\EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'navigation_banner',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Navigation Banner',
                'input' => 'select',
                'class' => '',
                'source' => 'Magento\Catalog\Model\Category\Attribute\Source\Page',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'required' => false,
                'group' => 'Display Settings',
                'sort_order' => 200
            ]
        );
        $eavSetup->addAttributeToSet(\Magento\Catalog\Model\Category::ENTITY, 'Default', 'General', 'navigation_banner');

        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Category::ENTITY,
            'icon_image',
            [
                'type' => 'varchar',
                'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
                'frontend' => '',
                'label' => 'Icon Image',
                'input' => 'image',
                'class' => '',
                'source' => '',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'required' => false,
                'group' => 'Display Settings',
                'sort_order' => 210
            ]
        );
        $eavSetup->addAttributeToSet(\Magento\Catalog\Model\Category::ENTITY, 'Default', 'General', 'icon_image');

        $setup->endSetup();
    }
}
