<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Finder\Block\Adminhtml\Finder\Edit\Tab\Import;

class HistoryGrid extends \Magento\Backend\Block\Widget\Grid
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        array $data
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $backendHelper, $data);

        /**
         * @var \Amasty\Finder\Model\Finder
         */
        $finder = $this->_coreRegistry->registry('current_amasty_finder_finder');
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Amasty\Finder\Model\Resource\ImportLog\Collection $collection */
        $collection = $om->create('Amasty\Finder\Model\Resource\ImportHistory\Collection');
        $collection->addFieldToFilter('finder_id', $finder->getId());
        $this->setCollection($collection);
    }
}
