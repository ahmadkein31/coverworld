<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Controller\Adminhtml\Export\Job;

use Firebear\ImportExport\Model\Job;
use Magento\Framework\App\Request\DataPersistorInterface;
use Firebear\ImportExport\Model\ExportJob;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends \Firebear\ImportExport\Controller\Adminhtml\Export\Job
{

    const SOURCE_DATA = 'source_data';

    const SOURCE_FILTER = 'source_filter';

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Firebear\ImportExport\Model\ExportJobFactory $exportJobFactory
     * @param \Firebear\ImportExport\Api\ExportJobRepositoryInterface $exportRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Firebear\ImportExport\Model\ExportJobFactory $exportJobFactory,
        \Firebear\ImportExport\Api\ExportJobRepositoryInterface $exportRepository,
        JsonFactory $jsonFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context, $coreRegistry, $exportJobFactory, $exportRepository);
    }

    /**
     * @return $this
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultJson = $this->jsonFactory->create();
        $data = $this->getRequest()->getPostValue();
        $behavior = $this->searchFields($data, 'behavior_');
        $exportSource = $this->searchFields($data, 'export_source_');
        $sourceData = $this->searchFields($data, self::SOURCE_DATA . '_');
        $sourceFilter = $this->searchFields($data, self::SOURCE_FILTER . '_');
        $data = $this->deleteFields($this->deleteFields($data, 'behavior_'), 'export_source_');
        $this->validSourceData($sourceData);
        $this->validSourceFilter($sourceFilter);
        $data['source_data'] = $this->jsonEncoder->encode($sourceData + $sourceFilter);
        $data['behavior_data'] = $this->jsonEncoder->encode($behavior);
        $data['export_source'] = $this->jsonEncoder->encode($exportSource);
        if ($data) {
            $id = $this->getRequest()->getParam('entity_id');
            if (empty($data['entity_id'])) {
                $data['entity_id'] = null;
            }
            if (!$id) {
                $model = $this->exportJobFactory->create();
            } else {
                if (empty($data['entity_id'])) {
                    $data['entity_id'] = $id;
                }
                $model = $this->exportRepository->getById($id);
                if (!$model->getId() && $id) {
                    if ($this->getRequest()->isAjax()) {
                        return $resultJson->setData(false);
                    }
                    $this->messageManager->addErrorMessage(__('This export no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            }
            $model->setData($data);
            try {
                $newModel = $this->exportRepository->save($model);
                if (!$this->getRequest()->isAjax()) {
                    $this->messageManager->addSuccessMessage(__('You saved the export.'));
                }
                $this->dataPersistor->clear('firebear_importexport_export');

                if ($this->getRequest()->isAjax()) {
                    return $resultJson->setData($newModel->getId());
                }

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['entity_id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the export.'));
            }

            $this->dataPersistor->set('firebear_importexport_export', $data);

            if ($this->getRequest()->isAjax()) {
                return $resultJson->setData(true);
            }

            return $resultRedirect->setPath(
                '*/*/edit',
                ['entity_id' => $this->getRequest()->getParam('entity_id')]
            );
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param $data
     * @param $expr
     *
     * @return array
     */
    protected function searchFields($data, $expr)
    {
        $array = [];
        foreach ($data as $key => $value) {
            if (strpos($key, $expr) !== false) {
                $array[$key] = $value;
            }
        }

        return $array;
    }

    /**
     * @param $data
     * @param $expr
     *
     * @return mixed
     */
    protected function deleteFields($data, $expr)
    {
        foreach ($data as $key => $value) {
            if (strpos($key, $expr) !== false) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @param $data
     */
    public function validSourceData(&$data)
    {
        $del = 0;
        if (isset($data[self::SOURCE_DATA . '_map'])) {
            foreach ($data[self::SOURCE_DATA . '_map'] as $item) {
                $id = $item['record_id'];
                if (isset($data[self::SOURCE_DATA . '_export']['delete'][$id])
                    && $data[self::SOURCE_DATA . '_export']['delete'][$id] == 1) {
                    $del = 1;
                    unset($data[self::SOURCE_DATA . '_map'][$id]);
                    unset($data[self::SOURCE_DATA . '_system']['value'][$id]);
                    unset($data[self::SOURCE_DATA . '_system']['entity'][$id]);
                    unset($data[self::SOURCE_DATA . '_export']['value'][$id]);
                    unset($data[self::SOURCE_DATA . '_export']['order'][$id]);
                    unset($data[self::SOURCE_DATA . '_export']['delete'][$id]);
                    unset($data[self::SOURCE_DATA . '_replace']['value'][$id]);
                }
            }
        }
        if ($del) {
            $data[self::SOURCE_DATA . '_map'] = array_merge([], $data[self::SOURCE_DATA . '_map']);
            foreach ($data[self::SOURCE_DATA . '_map'] as $key => &$item) {
                $item['record_id'] = $key;
            }
            $data[self::SOURCE_DATA . '_system']['value'] = array_merge(
                [],
                $data[self::SOURCE_DATA . '_system']['value']
            );
            $data[self::SOURCE_DATA . '_system']['entity'] = array_merge(
                [],
                $data[self::SOURCE_DATA . '_system']['entity']
            );
            $data[self::SOURCE_DATA . '_export']['value'] = array_merge(
                [],
                $data[self::SOURCE_DATA . '_export']['value']
            );
            $data[self::SOURCE_DATA . '_export']['order'] = array_merge(
                [],
                $data[self::SOURCE_DATA . '_export']['order']
            );
            $data[self::SOURCE_DATA . '_export']['delete'] = array_merge(
                [],
                $data[self::SOURCE_DATA . '_export']['delete']
            );
            $data[self::SOURCE_DATA . '_replace']['value'] = array_merge(
                [],
                $data[self::SOURCE_DATA . '_replace']['value']
            );
        }
    }

    /**
     * @param $data
     */
    public function validSourceFilter(&$data)
    {
        $del = 0;
        if (isset($data[self::SOURCE_FILTER . '_map'])) {
            foreach ($data[self::SOURCE_FILTER . '_map'] as $item) {
                $id = $item['record_id'];
                if (isset($data[self::SOURCE_FILTER . '_field']['delete'][$id])
                    && $data[self::SOURCE_FILTER . '_field']['delete'][$id] == 1) {
                    $del = 1;
                    unset($data[self::SOURCE_FILTER . '_map'][$id]);
                    unset($data[self::SOURCE_FILTER . '_field']['value'][$id]);
                    unset($data[self::SOURCE_FILTER . '_field']['entity'][$id]);
                    unset($data[self::SOURCE_FILTER . '_field']['order'][$id]);
                    unset($data[self::SOURCE_FILTER . '_filter']['value'][$id]);
                    unset($data[self::SOURCE_FILTER . '_field']['delete'][$id]);
                }
            }
        }
        if ($del) {
            $data[self::SOURCE_FILTER . '_map'] = array_merge([], $data[self::SOURCE_FILTER . '_map']);
            foreach ($data[self::SOURCE_FILTER . '_map'] as $key => &$item) {
                $item['record_id'] = $key;
            }
            $data[self::SOURCE_FILTER . '_field']['entity'] = array_merge(
                [],
                $data[self::SOURCE_FILTER . '_field']['entity']
            );
            $data[self::SOURCE_FILTER . '_field']['value'] = array_merge(
                [],
                $data[self::SOURCE_FILTER . '_field']['value']
            );
            $data[self::SOURCE_FILTER . '_field']['order'] = array_merge(
                [],
                $data[self::SOURCE_FILTER . '_field']['order']
            );
            if (isset($data[self::SOURCE_FILTER . '_filter']['value'])) {
                $data[self::SOURCE_FILTER . '_filter']['value'] = array_merge(
                    [],
                    $data[self::SOURCE_FILTER . '_filter']['value']
                );
            }
            $data[self::SOURCE_FILTER . '_field']['delete'] = array_merge(
                [],
                $data[self::SOURCE_FILTER . '_field']['delete']
            );
        }
    }
}
