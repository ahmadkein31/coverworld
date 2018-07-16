<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Feed;

use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Download constructor.
     *
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem                    $filesystem
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $filename = $this->getRequest()->getParam('filename');

        $model = $this->_objectManager->create('Amasty\Feed\Model\Feed');

        $model->load($filename, 'filename');

        $dirRead = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR);

        if ($model->getEntityId()) {
            $fileName = $model->getFilename();

            if ($dirRead->isExist($fileName)) {
                $this->fileFactory->create(
                    $model->getFilename(),
                    $dirRead->readFile($fileName),
                    DirectoryList::VAR_DIR,
                    $model->getContentType()
                );
            }
        }
    }
}
