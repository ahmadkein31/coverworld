<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Controller\Adminhtml\Category;

class Save extends \Amasty\Feed\Controller\Adminhtml\Category
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();

            try {
                $model = $this->_objectManager->create('Amasty\Feed\Model\Category');

                $id = $this->getRequest()->getParam('feed_category_id');

                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong category is specified.'));
                    }
                }

                $session = $this->_objectManager->get('Magento\Backend\Model\Session');

                $model->setData($data);

                $session->setPageData($model->getData());

                $model->save();

                $model->saveCategoriesMapping();

                $this->messageManager->addSuccess(__('You saved the feed.'));

                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amfeed/*/edit', ['id' => $model->getId()]);
                    return;
                } else if ($this->getRequest()->getParam('auto_apply')) {
                    $this->_redirect('amfeed/*/export', ['id' => $model->getId()]);
                    return;
                }
                $this->_redirect('amfeed/*/');
                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('feed_id');
                if (!empty($id)) {
                    $this->_redirect('amfeed/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amfeed/*/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the feed data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_objectManager->get('Magento\Backend\Model\Session')->setPageData($data);
                $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('feed_category_id')]);
                return;
            }
        }
        $this->_redirect('amfeed/*/');
    }
}