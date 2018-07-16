<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model\Job;

use Firebear\ImportExport\Ui\Component\Grid\Column\JobActions;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Framework\Json\DecoderInterface;
use Magento\ImportExport\Model\History;
use Magento\ImportExport\Model\Import;
use Firebear\ImportExport\Model\Import\Adapter;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\RequestInterface;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregator;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Firebear\ImportExport\Model\Source\Type\File\Config;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Controller\Adminhtml\ImportResult;

/**
 * Import Job Processor.
 * Validate & import jobs launched by cron or by cli command
 */
class Processor
{
    /**
     * @var \Firebear\ImportExport\Model\JobFactory
     */
    protected $jobFactory;

    /**
     * @var \Magento\ImportExport\Model\HistoryFactory
     */
    protected $importHistoryFactory;

    /**
     * @var \Firebear\ImportExport\Model\ImportFactory
     */
    protected $importFactory;

    /**
     * @var ObjectManagerFactory
     */
    protected $objectManagerFactory;

    /**
     * @var \Magento\Framework\FilesystemFactory
     */
    protected $filesystemFactory;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var DecoderInterface $jsonDecoder
     */
    protected $jsonDecoder;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var Import
     */
    protected $importModel;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StoreManagerInterface
     */
    protected $storeResolver;

    /**
     * @var \Firebear\ImportExport\Model\Job
     */
    protected $job;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var
     */
    protected $strategy;

    /**
     * @var History
     */
    protected $importHistoryModel;

    protected $source;

    protected $typeSource;

    /**
     * @var Config
     */
    protected $typeConfig;

    public $debugMode;

    /**
     * Processor constructor.
     * @param \Firebear\ImportExport\Model\JobFactory $jobFactory
     * @param \Firebear\ImportExport\Model\ImportFactory $importFactory
     * @param \Magento\Framework\FilesystemFactory $filesystemFactory
     * @param \Magento\ImportExport\Model\HistoryFactory $historyFactory
     * @param DecoderInterface $jsonDecoder
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param RequestInterface $request
     * @param LoggerInterface $logger
     * @param UrlInterface $backendUrl
     * @param ConsoleOutput $output
     */
    public function __construct(
        \Firebear\ImportExport\Model\JobFactory $jobFactory,
        \Firebear\ImportExport\Model\ImportFactory $importFactory,
        \Magento\Framework\FilesystemFactory $filesystemFactory,
        \Magento\ImportExport\Model\HistoryFactory $historyFactory,
        DecoderInterface $jsonDecoder,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        RequestInterface $request,
        LoggerInterface $logger,
        UrlInterface $backendUrl,
        ConsoleOutput $output,
        Config $typeConfig
    ) {
        $this->jobFactory = $jobFactory;
        $this->importFactory = $importFactory;
        $this->importHistoryFactory = $historyFactory;
        $this->filesystemFactory = $filesystemFactory;
        $this->jsonDecoder = $jsonDecoder;
        $this->logger = $logger;
        $this->request = $request;
        $this->backendUrl = $backendUrl;
        $this->timezone = $timezone;
        $this->output = $output;
        $this->typeConfig = $typeConfig;
    }

    /**
     * Get current timezone object.
     * We can't define timezone in constructor according to db lock timeout
     * when run job from console.
     *
     * @return \Magento\Framework\Stdlib\DateTime\Timezone|mixed
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Get import model
     *
     * @return Import|mixed
     */
    public function getImportModel()
    {
        if (!$this->importModel) {
            $this->importModel = $this->importFactory->create();
        }

        return $this->importModel;
    }

    /**
     * Get import history model
     *
     * @return History|mixed
     */
    public function getImportHistoryModel()
    {
        if (!$this->importHistoryModel) {
            $this->importHistoryModel = $this->importHistoryFactory->create();
        }

        return $this->importHistoryModel;
    }

    /**
     * Prepare import job object. Load behavior & source data.
     *
     * @param int $jobId
     *
     * @return array
     */
    public function prepareJob($jobId)
    {
        $this->getImportModel()->setLogger($this->logger);
        $this->job = $this->jobFactory->create();
        $this->job->load($jobId);
        $data = [];

        if ($this->job->getId()) {
            $behaviorData = $this->jsonDecoder->decode($this->job->getBehaviorData());
            $sourceData = $this->jsonDecoder->decode($this->job->getSourceData());
            $mapAttributesData = [];
            foreach ($this->job->getMap() as $map) {
                $mapAttributesData[$map->getId()] = [
                    'system' => $map->getAttributeId() ? $map->getAttributeId() : $map->getSpecialAttribute(),
                    'import' => $map->getImportCode(),
                    'default' => $map->getDefaultValue()
                ];
            }
            $this->addLogComment(__('Entity %1', $this->job->getEntity()), $this->output, 'info');

            $data = array_merge(
                ['entity' => $this->job->getEntity()],
                $behaviorData,
                ['import_source' => $this->job->getImportSource()],
                $sourceData,
                [$this->job->getImportSource() . '_file_path' => $sourceData['file_path']],
                ['map' => $mapAttributesData]
            );
        }
        if (isset($data['import_images_file_dir']) && !($data['import_images_file_dir'])) {
            unset($data['import_images_file_dir']);
        }

        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function prepareDataFromAjax($data)
    {

        $mapAttributesData = [];
        foreach ($data['records'] as $map) {
            $mapAttributesData[] = [
                'system' => $map['source_data_system'],
                'import' => $map['source_data_import'],
                'default' => $map['source_data_replace']
            ];
        }
        $data['records'] = $mapAttributesData;

        return $data;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function validate(array $data)
    {
        /* @var $import Import */
        $this->getImportModel()->setData($data);
        $this->getImportModel()->setJobId($this->job->getId());

        $lastModifiedAt = strtotime($this->job->getFileUpdatedAt());
        $modified = $this->checkModified($this->getImportModel(), $lastModifiedAt);
        if (!$modified) {
            //    return false;
        }

        if ($data['import_source'] != 'file') {
            $source = Adapter::findAdapterFor(
                $this->getTypeClass(),
                $this->getImportModel()->uploadSource(),
                $this->filesystemFactory->create()->getDirectoryWrite(DirectoryList::ROOT),
                $data[Import::FIELD_FIELD_SEPARATOR]
            );
        } else {
            $source = Adapter::findAdapterFor(
                $this->getTypeClass(),
                $data['file_path'],
                $this->filesystemFactory->create()->getDirectoryWrite(DirectoryList::ROOT),
                $data[Import::FIELD_FIELD_SEPARATOR]
            );
        }
        $validationResult = $this->getImportModel()->validateSource($source);
        if (!$this->getImportModel()->getProcessedRowsCount()) {
            if (!$this->getImportModel()->getErrorAggregator()->getErrorsCount()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('This file is empty. Please try another one.')
                );
            } else {
                $errors = '';
                foreach ($this->getImportModel()->getErrorAggregator()->getAllErrors() as $error) {
                    $errors .= $error->getErrorMessage() . ' ';
                }
                throw new \Exception(
                    $errors
                );
            }
        } else {
            if (!$validationResult) {
                throw new \Exception(
                    __('Data validation is failed. Please fix errors and re-upload the file..')
                );
            } else {
                if ($this->getImportModel()->isImportAllowed()) {
                    return true;
                } else {
                    throw new \Exception(
                        __('The file is valid, but we can\'t import it for some reason.')
                    );
                }
            }
        }

        return true;
    }

    /**
     * Check file modified date.
     *
     * @param Import $importModel
     * @param        $modifiedAt
     *
     * @return bool
     */
    public function checkModified(Import $importModel, $modifiedAt)
    {
        if ($importModel->getSource()) {
            return $importModel->getSource()->checkModified($modifiedAt);
        }

        return true;
    }

    /**
     * @param $jobId
     *
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process($jobId)
    {
        $totalTime = 0;
        $result = false;
        try {
            $timeStart = time();
            $data = $this->prepareJob($jobId);
            $this->strategy = $data['validation_strategy'];
            if (isset($data['type_file'])) {
                $this->setTypeSource($data['type_file']);
            }
            $validationResult = $this->dataValidate($data, $jobId);
            if ($validationResult || $this->strategy != ProcessingErrorAggregator::VALIDATION_STRATEGY_STOP_ON_ERROR) {
                if ($this->strategy != ProcessingErrorAggregator::VALIDATION_STRATEGY_STOP_ON_ERROR) {
                    $this->scopeMessages(1);
                    $this->getImportModel()->getErrorAggregator()->clear();
                }
                $this->importModel = $this->importFactory->create();
                $this->getImportModel()->setLogger($this->logger);
                $this->getImportModel()->setData($data);
                $this->getImportModel()->setJobId($jobId);
                $this->getImportModel()->importSource();
                $modified = $this->checkModified($this->getImportModel(), $this->job->getFileUpdatedAt());
                if (is_int($modified)) {
                    $this->job->setFileUpdatedAt($modified)->save();
                }

                $this->scopeMessages();

                $this->getImportModel()->invalidateIndex();
            }

            $timeFinish = time();
            $totalTime = $timeFinish - $timeStart;
            $counter = 0;
            if ($this->getImportModel()) {
                $errorAggregator = $this->getImportModel()->getErrorAggregator();
                $messages = [];
                $rowMessages = $errorAggregator->getRowsGroupedByErrorCode(
                    [],
                    [AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]
                );
                foreach ($rowMessages as $errorCode => $rows) {
                    $messages[] = $errorCode . ' ' . __('in rows:') . ' ' . implode(', ', $rows);
                }

                foreach ($messages as $error) {
                    ++$counter;
                    $this->addLogComment($counter . '. ' . $error, $this->output, 'error');

                    if ($counter >= ImportResult::LIMIT_ERRORS_MESSAGE) {
                        break;
                    }
                }
                if ($errorAggregator->hasFatalExceptions()) {
                    $errorsByCode = $errorAggregator->getErrorsByCode(
                        [AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION]
                    );
                    foreach ($errorsByCode as $error) {
                        $this->addLogComment(
                            $error->getErrorMessage(),
                            $this->output,
                            'error'
                        );
                        $this->addLogComment(
                            $error->getErrorDescription(),
                            $this->output,
                            'error'
                        );
                    }
                } else {
                    $result = true;
                }
            }
        } catch (\Exception $e) {
            $this->addLogComment(
                'Job #' . $jobId . ' can\'t be imported. Check if job exist',
                $this->output,
                'error'
            );
            $this->addLogComment(
                $e->getMessage(),
                $this->output,
                'error'
            );
        }
        if ($totalTime) {
            $this->addLogComment(
                'Job #' . $jobId . ' was generated successfully in ' . $totalTime . ' seconds',
                $this->output,
                'info'
            );
        }

        return $result;
    }

    /**
     * @param $data
     * @param $jobId
     *
     * @return bool|int
     */
    public function dataValidate($data, $jobId)
    {
        $validationResult = 0;
        try {
            $validationResult = $this->validate($data);
        } catch (\Exception $e) {
            $this->getImportModel()->getErrorAggregator()->addError(
                $e->getCode(),
                ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                $e->getMessage()
            );
            $this->getImportModel()->addLogComment($e->getMessage());
            $summary = '<b>' . $e->getMessage() . '</b><br />';
            $importHistoryModel = $this->getImportHistoryModel();
            $importHistoryModel->load($importHistoryModel->getLastItemId());
            $date = $this->getTimezone()->formatDateTime(
                $this->getTimezone()->date(),
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::MEDIUM,
                null,
                null
            );
            $summary .= '<i>' . $date . '</i><br />';
            $summary .= 'Job: #' . $jobId;
            $importHistoryModel->setSummary($summary);
            $importHistoryModel->setExecutionTime(History::IMPORT_FAILED);
            $importHistoryModel->save();
            $validationResult = false;
        }

        return $validationResult;
    }

    /**
     * @param int $skip
     *
     * @throws \Exception
     */
    protected function scopeMessages($skip = 0)
    {
        if ($this->getImportModel()->getErrorAggregator()->hasToBeTerminated()) {
            $messages = [
                __('Maximum error count has been reached or system error is occurred!')
            ];
            foreach ($this->getImportModel()->getErrorAggregator()->getAllErrors() as $error) {
                $messages[] = $error->getErrorMessage();
                if ($skip) {
                    $this->addLogComment($error->getErrorMessage(), $this->output, 'error');
                }
            }
            if (!$skip) {
                throw new \Exception(
                    implode(PHP_EOL, $messages)
                );
            }
        }
    }

    /**
     * Get columns names from first row.
     *
     * @param \Firebear\ImportExport\Model\Job $job
     *
     * @return array
     */
    public function getCsvColumns($job)
    {
        $errorMessage = [];
        if (is_object($job) && (!$job->getId() || $job->getEntity() != 'catalog_product')) {
            return [];
        }
        $data = is_object($job) ? $this->prepareJob($job->getId()) : $job;
        $directory = $this->filesystemFactory->create()->getDirectoryWrite(DirectoryList::ROOT);

        if ($data['import_source'] != 'file') {
            $this->getImportModel()->setImportSource($data['import_source']);
            $this->getImportModel()->setData($data);
            $this->getImportModel()->getSource()->setData($data);
            $this->getImportModel()->setLogger($this->logger);
            $result = null;
            $source = $this->getImportModel()->getSource();
            try {
                $result = $source->uploadSource();
            } catch (\Exception $e) {
                $errorMessage = __($e->getMessage());
                if (strpos($errorMessage, 'ftp_get()') !== false) {
                    $errorMessage = __('Unable to open your file. Please make sure File Path is correct.');
                }
            }
            if ($result) {
                $source = Adapter::findAdapterFor(
                    $this->getTypeClass(),
                    $this->getImportModel()->uploadSource(),
                    $directory,
                    $data[Import::FIELD_FIELD_SEPARATOR]
                );
            } else {
                $this->source = $source;

                return is_array($job) ? $errorMessage : [];
            }
        } else {
            $source = Adapter::findAdapterFor(
                $this->getTypeClass(),
                $data['file_path'],
                $this->filesystemFactory->create()
                    ->getDirectoryWrite(DirectoryList::ROOT),
                $data[Import::FIELD_FIELD_SEPARATOR]
            );
        }

        $this->source = $source;

        return $source->getColNames();
    }

    public function correctData($data)
    {
        $errorMessage = [];

        $data = $this->prepareDataFromAjax($data);

        if ($data['import_source'] != 'file') {
            $this->getImportModel()->setImportSource($data['import_source']);
            $this->getImportModel()->setData($data);
            $this->getImportModel()->getSource()->setData($data);
            $result = null;
            $source = $this->getImportModel()->getSource();
            try {
                $result = $source->uploadSource();
            } catch (\Exception $e) {
                $errorMessage = __($e->getMessage());
                if (strpos($errorMessage, 'ftp_get()') !== false) {
                    $errorMessage = __('Unable to open your CSV file. Please make sure File Path is correct.');
                }
            }

            if ($result) {
                $source = Adapter::findAdapterFor(
                    $this->getTypeClass(),
                    $this->getImportModel()->uploadSource(),
                    $this->filesystemFactory->create()->getDirectoryWrite(DirectoryList::ROOT),
                    $data[Import::FIELD_FIELD_SEPARATOR]
                );
            } else {
                $this->source = $source;
            }
        } else {
            $source = Adapter::findAdapterFor(
                $this->getTypeClass(),
                $data['file_path'],
                $this->filesystemFactory->create()
                    ->getDirectoryWrite(DirectoryList::ROOT),
                $data[Import::FIELD_FIELD_SEPARATOR]
            );
        }

        $this->source = $source;
        $this->source->setMap($data['records']);
        return true;
    }

    public function validateFile()
    {
        $source = $this->getTypeSource();
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function processValidate($data)
    {
        $messages = "";
        if ($this->source !== null) {
            $this->getImportModel()->setData($data);
            $validationResult = $this->getImportModel()->validateSource($this->source);
            $import = $this->getImportModel();
            $errorAggregator = $this->getImportModel()->getErrorAggregator();
            foreach ($errorAggregator->getAllErrors() as $error) {
                $messages[] = $error->getErrorMessage();
            }
        }

        return $messages;
    }

    /**
     * @param $debugData
     * @param OutputInterface|null $output
     * @param null $type
     * @return $this
     */
    public function addLogComment($debugData, OutputInterface $output = null, $type = null)
    {

        if ($this->debugMode) {
            $this->logger->debug($debugData);
        }

        if ($output) {
            switch ($type) {
                case 'error':
                    $debugData = '<error>' . $debugData . '</error>';
                    break;
                case 'info':
                    $debugData = '<info>' . $debugData . '</info>';
                    break;
                default:
                    $debugData = '<comment>' . $debugData . '</comment>';
                    break;
            }

            $output->writeln($debugData);
        }

        return $this;
    }

    /**
     * @param $type
     * @return $this
     */
    public function setTypeSource($type)
    {
        $this->typeSource = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTypeSource()
    {
        return $this->typeSource;
    }

    /**
     * @return mixed
     */
    public function getTypeClass()
    {
        $data = $this->typeConfig->get();
        $types = $data['import'];
        $value = current($types);
        $model = $value['model'];
        if (isset($types[$this->getTypeSource()])) {
            $model = $types[$this->getTypeSource()]['model'];
        }

        return $model;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
