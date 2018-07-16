<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Controller\Adminhtml\Feed;

class Export extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;
        parent::__construct($context, $coreRegistry, $resultLayoutFactory);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Amasty\Feed\Model\Feed');

        if ($id) {
            $model->load($id);
            if (!$model->getEntityId()) {
                $this->messageManager->addError(__('This feed no longer exists.'));
                $this->_redirect('amfeed/*');
                return;
            }

            try {
                $this->fileFactory->create(
                    $model->getFilename(),
                    $model->export(),
                    \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR,
                    $model->getContentType()
                );
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while export feed data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            }

            $this->_redirect('amfeed/*/edit', ['id' => $id]);
        }
    }
}