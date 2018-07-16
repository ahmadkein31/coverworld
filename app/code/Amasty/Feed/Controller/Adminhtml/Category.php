<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Controller\Adminhtml;

abstract class Category extends \Magento\Backend\App\Action
{
    protected $_coreRegistry = null;

    protected $_resultLayoutFactory = null;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Feed::feed');
    }
}
