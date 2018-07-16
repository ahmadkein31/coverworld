<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Model;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;
use Symfony\Component\Console\Output\ConsoleOutput;

class Import extends \Magento\ImportExport\Model\Import
{

    use \Firebear\ImportExport\Traits\General;

    /**
     * Limit displayed errors on Import History page.
     */
    const LIMIT_VISIBLE_ERRORS = 5;

    const CREATE_ATTRIBUTES_CONF_PATH = 'firebear_importexport/general/create_attributes';

    /**
     * @var \Firebear\ImportExport\Model\Source\ConfigInterface
     */
    protected $config;

    /**
     * @var \Firebear\ImportExport\Helper\Data
     */
    protected $helper;

    /**
     * @var \Firebear\ImportExport\Helper\Additional
     */
    protected $additional;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $timezone;

    /**
     * @var \Firebear\ImportExport\Model\Source\Type\AbstractType
     */
    protected $source;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    /**
     * @var array
     */
    protected $errorMessages;

    /**
     * @var \Firebear\ImportExport\Model\Source\Factory
     */
    protected $factory;

    /**
     * @var array|mixed|null
     */
    protected $platforms;

    /**
     * Import constructor.
     * @param Source\ConfigInterface $config
     * @param \Firebear\ImportExport\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param Source\Import\Config $importConfig
     * @param \Magento\ImportExport\Model\Import\Entity\Factory $entityFactory
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\ImportExport\Model\Export\Adapter\CsvFactory $csvFactory
     * @param \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory
     * @param \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry
     * @param \Magento\ImportExport\Model\History $importHistoryModel
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $localeDate
     * @param ConsoleOutput $output
     * @param array $data
     */
    public function __construct(
        \Firebear\ImportExport\Model\Source\ConfigInterface $config,
        \Firebear\ImportExport\Helper\Data $helper,
        \Firebear\ImportExport\Helper\Additional $additional,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Firebear\ImportExport\Model\Source\Import\Config $importConfig,
        \Magento\ImportExport\Model\Import\Entity\Factory $entityFactory,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\ImportExport\Model\Export\Adapter\CsvFactory $csvFactory,
        \Magento\Framework\HTTP\Adapter\FileTransferFactory $httpFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\ImportExport\Model\Source\Import\Behavior\Factory $behaviorFactory,
        \Magento\Framework\Indexer\IndexerRegistry $indexerRegistry,
        \Magento\ImportExport\Model\History $importHistoryModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $localeDate,
        \Firebear\ImportExport\Model\Source\Factory $factory,
        \Firebear\ImportExport\Model\Source\Platform\Config $configPlatforms,
        ConsoleOutput $output,
        array $data = []
    ) {
        $this->config = $config;
        $this->helper = $helper;
        $this->additional = $additional;
        $this->timezone = $timezone;
        $this->output = $output;
        $this->factory = $factory;
        $this->platforms = $configPlatforms->get();

        parent::__construct(
            $logger,
            $filesystem,
            $importExportData,
            $coreConfig,
            $importConfig,
            $entityFactory,
            $importData,
            $csvFactory,
            $httpFactory,
            $uploaderFactory,
            $behaviorFactory,
            $indexerRegistry,
            $importHistoryModel,
            $localeDate,
            $data
        );

        $this->_debugMode = $helper->getDebugMode();
    }

    /**
     * Check if remote file was modified since the last import
     *
     * @param $timestamp
     *
     * @return bool
     */
    public function checkModified($timestamp)
    {
        if ($this->getSource()) {
            return $this->getSource()->checkModified($timestamp);
        }

        return true;
    }

    /**
     * Download remote source file to temporary directory
     *
     * @TODO change the code to show exceptions on frontend instead of 503 error.
     * @return null|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadSource()
    {
        $result = null;

        if ($this->getImportSource() && $this->getImportSource() != 'file') {
            $source = $this->getSource();
            try {
                $result = $source->uploadSource();
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }
        if ($result) {
            $sourceFileRelative = $this->_varDirectory->getRelativePath($result);
            $entity = $this->getEntity();
           // $this->createHistoryReport($sourceFileRelative, $entity);

            return $result;
        }

        return parent::uploadSource();
    }

    /**
     * Validates source file and returns validation result.
     *
     * @param \Magento\ImportExport\Model\Import\AbstractSource $source
     *
     * @return bool
     */
    public function validateSource(\Magento\ImportExport\Model\Import\AbstractSource $source)
    {
        $platformModel = null;
        $this->addLogWriteln(__('Begin data validation'), $this->output, 'comment');
        if (isset($this->platforms[$this->getData('platforms')]['model'])) {
            $platformModel = $this->factory->create($this->platforms[$this->getData('platforms')]['model']);
        }
        $source->setPlatform($platformModel);

        try {
            if (!$source->getMap()) {
                $source->setMap($this->getData('map'));
            }
            $adapter = $this->_getEntityAdapter()->setSource($source);
            $adapter->setLogger($this->_logger);
            $errorAggregator = $adapter->validateData();
        } catch (\Exception $e) {
            $errorAggregator = $this->getErrorAggregator();
            $this->addLogWriteln($e->getMessage(), $this->output, 'error');
            $errorAggregator->addError(
                \Magento\ImportExport\Model\Import\Entity\AbstractEntity::ERROR_CODE_SYSTEM_EXCEPTION . '. '
                . $e->getMessage(),
                ProcessingError::ERROR_LEVEL_CRITICAL,
                null,
                null,
                null,
                $e->getMessage()
            );
        }

        $messages = $this->getOperationResultMessages($errorAggregator);
        $this->serErrorMessages($messages);
        foreach ($messages as $message) {
            $this->addLogWriteln($message, $this->output, 'info');
        }

        $result = !$errorAggregator->getErrorsCount([ProcessingError::ERROR_LEVEL_CRITICAL]);
        if ($result) {
            $this->addLogWriteln(__('Import data validation is complete.'), $this->output, 'info');
        } else {
            if ($this->isReportEntityType()) {
                $this->getImportHistoryModel()->load($this->getImportHistoryModel()->getLastItemId());
                $summary = '';
                if ($errorAggregator->getErrorsCount() > self::LIMIT_VISIBLE_ERRORS) {
                    $summary = __('Too many errors. Please check your debug log file.') . '<br />';
                    $this->addLogWriteln($summary, $this->output, 'error');
                } else {
                    if ($this->getJobId()) {
                        $summary = __('Import job #' . $this->getJobId() . ' failed.') . '<br />';
                        $this->addLogWriteln(
                            __('Import job #' . $this->getJobId() . ' failed.'),
                            $this->output,
                            'error'
                        );
                    }

                    foreach ($errorAggregator->getRowsGroupedByErrorCode() as $errorMessage => $rows) {
                        $error = $errorMessage . ' ' . __('in rows') . ': ' . implode(', ', $rows);
                        $this->addLogWriteln($error, $this->output, 'error');
                        $summary .= $error . '<br />';
                    }
                }
                $date = $this->timezone->formatDateTime(
                    $this->timezone->date(),
                    \IntlDateFormatter::MEDIUM,
                    \IntlDateFormatter::MEDIUM,
                    null,
                    null
                );
                $summary .= '<i>' . $date . '</i>';
                $this->addLogWriteln($date, $this->output, 'info');
                $this->getImportHistoryModel()->setSummary($summary);
                $this->getImportHistoryModel()->setExecutionTime(\Magento\ImportExport\Model\History::IMPORT_FAILED);
                $this->getImportHistoryModel()->save();
            }
        }

        return $result;
    }

    /**
     * @return Source\Type\AbstractType
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSource()
    {
        if (!$this->source) {
            $sourceType = $this->getImportSource();
            try {
                $this->source = $this->additional->getSourceModelByType($sourceType);
                $this->source->setData($this->getData());
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }

        return $this->source;
    }

    /**
     * @return mixed
     */
    public function getImportHistoryModel()
    {
        return $this->importHistoryModel;
    }

    /**
     * @return mixed
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @param $messages
     */
    public function serErrorMessages($messages)
    {
        $this->errorMessages = $messages;
    }

    /**
     * @param mixed $debugData
     * @return $this
     */
    public function addLogComment($debugData)
    {
        
        if (is_array($debugData)) {
            $this->_logTrace = array_merge($this->_logTrace, $debugData);
        } else {
            $this->_logTrace[] = $debugData;
        }
        
        if (is_scalar($debugData)) {
            $this->_logger->debug($debugData);
            $this->output->writeln($debugData);
        } else {
            foreach ($debugData as $message) {
                if ($message instanceof \Magento\Framework\Phrase) {
                    $this->output->writeln($message->__toString());
                    $this->_logger->debug($message->__toString());
                } else {
                    $this->output->writeln($message);
                    $this->_logger->debug($message);
                }
            }
        }
        
        return $this;
    }

    /**
     * @return \Magento\ImportExport\Model\Import\AbstractEntity|\Magento\ImportExport\Model\Import\Entity\AbstractEntity
     */
    protected function _getEntityAdapter()
    {
                    
        $adapter = parent::_getEntityAdapter();
        $adapter->setLogger($this->_logger);

        return $adapter;
    }
}
