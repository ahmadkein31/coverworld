<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Amasty\Feed\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup;

class UpgradeData implements UpgradeDataInterface
{
    protected $executor;

    protected $updater;


    public function __construct(Setup\SampleData\Executor $executor, Updater $updater)
    {
        $this->executor = $executor;
        $this->updater = $updater;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if ($context->getVersion()
            && version_compare($context->getVersion(), '1.0.1') < 0
        ) {
            $this->updater->setTemplates(['bing']);
            $this->executor->exec($this->updater);
        }
        $setup->endSetup();
    }
}
