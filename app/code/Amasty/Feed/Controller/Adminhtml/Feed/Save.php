<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */

namespace Amasty\Feed\Controller\Adminhtml\Feed;

class Save extends \Amasty\Feed\Controller\Adminhtml\Feed
{
    protected function _save(){

        $model = $this->_objectManager->create('Amasty\Feed\Model\Feed');

        if ($this->getRequest()->getPostValue()) {

            $data = $this->getRequest()->getPostValue();

            $id = $this->getRequest()->getParam('feed_id');

            if ($id) {
                $model->load($id);
                if ($id != $model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('The wrong feed is specified.'));
                }
            }

            $session = $this->_objectManager->get('Magento\Backend\Model\Session');
            
            if (isset($data['feed_entity_id'])){
                $data['entity_id'] = $data['feed_entity_id'];
            }

            if (isset($data['store_ids'])) {
                $data['store_ids'] = implode(",", $data['store_ids']);
            }

            if (isset($data['cron_time'])) {
                $data['cron_time'] = implode(",", $data['cron_time']);
            }

            if (isset($data['csv_field'])) {
                $data['csv_field'] = serialize($data['csv_field']);
            }

            if (isset($data['rule']) && isset($data['rule']['conditions'])) {
                $data['conditions'] = $data['rule']['conditions'];

                unset($data['rule']);

                $rule = $this->_objectManager->create('Amasty\Feed\Model\Rule');
                $rule->loadPost($data);

                $data['conditions_serialized'] = serialize($rule->getConditions()->asArray());
                unset($data['conditions']);
            }

            $model->setData($data);

            $session->setPageData($model->getData());

            $model->save();

            $session->setPageData(false);
        }

        return $model;

    }

    public function execute()
    {
        try {
            $data = $this->getRequest()->getPostValue();

            $model = $this->_save();
            $this->messageManager->addSuccess(__('You saved the feed.'));

            if ($this->getRequest()->getParam('back')) {
                $this->_redirect('amfeed/feed/edit', ['id' => $model->getId()]);
                return;
            } else if ($this->getRequest()->getParam('auto_apply')) {
                $this->_redirect('amfeed/feed/export', ['id' => $model->getId()]);
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
            $this->_redirect('amfeed/*/edit', ['id' => $this->getRequest()->getParam('feed_id')]);
            return;
        }

        $this->_redirect('amfeed/*/');
    }
}