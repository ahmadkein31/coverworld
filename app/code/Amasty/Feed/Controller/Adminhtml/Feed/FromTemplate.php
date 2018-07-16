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

class FromTemplate extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    protected $feedCopier;
    protected $storeManager;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Amasty\Feed\Model\Feed\Copier $feedCopier,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->feedCopier = $feedCopier;
        $this->storeManager = $storeManager;

        parent::__construct($context, $coreRegistry, $resultLayoutFactory);
    }

    /**
     * Create product duplicate
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        try {
            $storeId = $this->storeManager->getStore()->getId();

            $model = $this->_objectManager->create('Amasty\Feed\Model\Feed');

            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addError(__('This feed no longer exists.'));
                $this->_redirect('amfeed/*');
                return;
            }

            $newModel = $this->feedCopier->fromTemplate($model, $storeId);
            $this->messageManager->addSuccess(__('Feed %1 created', $model->getName()));

        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Something went wrong while export feed data. Please review the error log.')
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        $this->_redirect('amfeed/*/edit', array(
            'id' => $id
        ));

    }
}