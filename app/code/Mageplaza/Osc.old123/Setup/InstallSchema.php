<?php
namespace Mageplaza\Osc\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'mc_order_comment',
            'text NULL'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'mc_giftwrap_amount',
            'DECIMAL(12,4)'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'mc_giftwrap_base_amount',
            'DECIMAL(12,4)'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'mc_giftwrap_amount',
            'DECIMAL(12,4)'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order_item'),
            'mc_giftwrap_base_amount',
            'DECIMAL(12,4)'
        );
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'mc_giftwrap_amount',
            'DECIMAL(12,4)'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice'),
            'mc_giftwrap_base_amount',
            'DECIMAL(12,4)'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice_item'),
            'mc_giftwrap_amount',
            'DECIMAL(12,4)'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_invoice_item'),
            'mc_giftwrap_base_amount',
            'DECIMAL(12,4)'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'mc_giftwrap_amount',
            'DECIMAL(12,4)'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo'),
            'mc_giftwrap_base_amount',
            'DECIMAL(12,4)'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo_item'),
            'mc_giftwrap_amount',
            'DECIMAL(12,4)'
        );

        $setup->getConnection()->addColumn(
            $setup->getTable('sales_creditmemo_item'),
            'mc_giftwrap_base_amount',
            'DECIMAL(12,4)'
        );
        $installer->endSetup();
    }
}
