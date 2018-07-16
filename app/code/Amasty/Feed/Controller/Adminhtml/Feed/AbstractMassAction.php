<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Amasty\Feed\Controller\Adminhtml\Feed;

use Magento\Backend\App\Action;
use Amasty\Feed\Controller\Adminhtml\Feed;

abstract class AbstractMassAction extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    protected $feedCopier;
    protected $filter;
    protected $collectionFactory;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Amasty\Feed\Model\Feed\Copier $feedCopier,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\Feed\Model\Resource\Feed\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory);
        $this->feedCopier = $feedCopier;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;

    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            $this->massAction($collection);
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Something went wrong while export feed data. Please review the error log.')
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        $this->_redirect('amfeed/*/index');
    }

    abstract protected function massAction($collection);
}
