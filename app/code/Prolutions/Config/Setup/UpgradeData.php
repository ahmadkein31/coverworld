<?php

namespace Prolutions\Config\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * Construct
     *
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->_resourceConfig = $resourceConfig;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.0') < 0) {
            $this->_resourceConfig->saveConfig(
                'design/theme/theme_id',
                4,
                'default',
                0
            );
        }
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $this->_resourceConfig->saveConfig(
                'design/footer/copyright',
                'Copyright © 2010-2016 BuyMarineCovers.com, a Select Covers Store. All rights reserved.',
                'default',
                0
            );
        }
        if (version_compare($context->getVersion(), '1.0.2') < 0) {
            $this->_resourceConfig->saveConfig(
                'catalog/frontend/list_mode',
                'grid',
                'default',
                0
            );
        }
        if (version_compare($context->getVersion(), '1.0.3') < 0) {
            $this->_resourceConfig->saveConfig(
                'checkout/cart/redirect_to_cart',
                '1',
                'default',
                0
            );
        }
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            $this->_resourceConfig->saveConfig(
                'carriers/freeshipping/free_shipping_subtotal',
                '100',
                'default',
                0
            );
            $this->_resourceConfig->saveConfig(
                'carriers/freeshipping/active',
                '100',
                'default',
                0
            );
        }
        if (version_compare($context->getVersion(), '1.0.5') < 0) {
            $this->_resourceConfig->saveConfig(
                'osc/display_configuration/is_enabled_newsletter',
                '0',
                'default',
                0
            );
        }
        if (version_compare($context->getVersion(), '1.0.6') < 0) {
            $this->_resourceConfig->saveConfig(
                'persistent/options/enabled',
                '1',
                'default',
                0
            );
        }

        $setup->endSetup();
    }
}