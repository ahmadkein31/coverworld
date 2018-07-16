<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;
use Magento\Backend\App\Action;
use Amasty\Feed\Controller\Adminhtml\Feed;

class MassDelete extends \Amasty\Feed\Controller\Adminhtml\Feed\AbstractMassAction
{

    protected function massAction($collection)
    {
        foreach($collection as $model)
        {
            $model->delete();
            $this->messageManager->addSuccess(__('Feed %1 was deleted', $model->getName()));
        }
    }
}