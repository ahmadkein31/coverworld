<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Helper;

use Firebear\ImportExport\Model\Source\Factory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Firebear\ImportExport\Model\Source\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Firebear\ImportExport\Api\HistoryRepositoryInterface;
use Firebear\ImportExport\Api\ExHistoryRepositoryInterface;
use Firebear\ImportExport\Model\Job\Processor;
use Firebear\ImportExport\Model\ExportJob\Processor as ExportProcessor;
use Firebear\ImportExport\Model\Import\HistoryFactory;
use Firebear\ImportExport\Model\Export\HistoryFactory as ExportFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Controller\Adminhtml\ImportResult;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Firebear\ImportExport\Model\Source\Menu\Config as MenuConfig;
/**
 * Class Data
 *
 * @package Firebear\ImportExport\Helper
 */
class Data extends AbstractHelper
{
    const GENERAL_DEBUG = 'firebear_importexport/general/debug';

    /**
     * @var Factory
     */
    protected $sourceFactory;

    /**
     * @var Config
     */
    protected $configSource;

    protected $typeInt = [
        'int',
        'smallint',
        'tinyint',
        'mediumint',
        'bigint',
        'bit',
        'float',
        'double',
        'decimal'
    ];

    protected $typeText = [
        'char',
        'varchar',
        'tinytext',
        'text',
        'mediumtext',
        'longtext',
        'json'
    ];

    protected $typeDate = [
        'date',
        'time',
        'year',
        'datetime',
        'timestamp'
    ];

    /**
     * @var ScopeConfigInterface
     */
    protected $coreConfig;

    /**
     * @var \Firebear\ImportExport\Logger\Logger
     */
    protected $logger;

    /**
     * @var HistoryRepositoryInterface
     */
    protected $historyRepository;

    /**
     * @var ExHistoryRepositoryInterface
     */
    protected $historyExRepository;

    /**
     * @var HistoryFactory
     */
    protected $historyFactory;

    /**
     * @var ExportFactory
     */
    protected $exportFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timeZone;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var ExportProcessor
     */
    protected $exProcessor;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $directory;

    protected $resultProcess;

    /**
     * Data constructor.
     * @param Context $context
     * @param Factory $sourceFactory
     * @param Config $configSource
     * @param \Firebear\ImportExport\Logger\Logger $logger
     * @param HistoryRepositoryInterface $historyRepository
     * @param ExHistoryRepositoryInterface $historyExRepository
     * @param HistoryFactory $historyFactory
     * @param ExportFactory $exportFactory
     * @param Processor $processor
     * @param TimezoneInterface $timezone
     * @param Filesystem $filesystem
     */
    public function __construct(
        Context $context,
        Factory $sourceFactory,
        Config $configSource,
        \Firebear\ImportExport\Logger\Logger $logger,
        HistoryRepositoryInterface $historyRepository,
        ExHistoryRepositoryInterface $historyExRepository,
        HistoryFactory $historyFactory,
        ExportFactory $exportFactory,
        Processor $processor,
        ExportProcessor $exProcessor,
        TimezoneInterface $timezone,
        Filesystem $filesystem,
        MenuConfig $menuConfig
    ) {
        $this->sourceFactory = $sourceFactory;
        $this->configSource = $configSource;
        $this->coreConfig = $context->getScopeConfig();
        $this->historyRepository = $historyRepository;
        $this->historyExRepository = $historyExRepository;
        $this->historyFactory = $historyFactory;
        $this->exportFactory = $exportFactory;
        $this->timeZone = $timezone;
        $this->processor = $processor;
        $this->exProcessor = $exProcessor;
        $this->logger = $logger;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::LOG);

        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getConfigFields()
    {
        $list = [];
        $types = $this->configSource->get();
        foreach ($types as $typeName => $type) {
            foreach ($type['fields'] as $name => $values) {
                if (!isset($list[$name])) {
                    $list[] = $name;
                }
            }
        }

        return array_unique($list);
    }

    /**
     * @param $type
     * @return string
     */
    public function convertTypesTables($type)
    {
        $changed = 0;
        if (in_array($type, $this->typeInt)) {
            $type = 'int';
            $changed = 1;
        }
        if (in_array($type, $this->typeText)) {
            $type = 'text';
            $changed = 1;
        }
        if (in_array($type, $this->typeDate)) {
            $type = 'date';
            $changed = 1;
        }
        if (!$changed) {
            $type = 'not';
        }

        return $type;
    }

    /**
     * @return bool
     */
    public function getDebugMode()
    {
        return (bool)$this->coreConfig->getValue(
            self::GENERAL_DEBUG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $id
     * @return string
     */
    public function beforeRun($id)
    {
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();

        return $id . "-" . $timeStamp;
    }

    /**
     * @param $id
     * @param $file
     * @return array
     */
    public function runImport($id, $file)
    {
        try {
            $timeStart = time();
            $history = $this->createHistory($id, $file, 'admin');
            $this->processor->debugMode = $this->getDebugMode();
            $this->processor->setLogger($this->logger);
            $result =  $this->processor->process($id);
            $date = $this->timeZone->date();
            $timeStamp = $date->getTimestamp();
            $history->setFinishedAt($timeStamp);
            $this->setResultProcessor($result);
            $this->historyRepository->save($history);
        } catch (\Exception $e) {
            $this->addLogComment(
                'Job #' . $id . ' can\'t be imported. Check if job exist',
                'error'
            );
            $this->addLogComment(
                $e->getMessage(),
                'error'
            );
        }

        return true;
    }

    /**
     * @param $id
     * @param $file
     * @return array
     */
    public function runExport($id, $file)
    {
        try {
            $history = $this->createExportHistory($id, $file, 'admin');
            $this->exProcessor->debugMode = $this->getDebugMode();
            $this->exProcessor->setLogger($this->logger);
            $result = $this->exProcessor->process($id);
            $date = $this->timeZone->date();
            $timeStamp = $date->getTimestamp();
            $history->setFinishedAt($timeStamp);
            $this->setResultProcessor($result);
            $this->historyExRepository->save($history);
        } catch (\Exception $e) {
            $this->addLogComment(
                'Job #' . $id . ' can\'t be exported. Check if job exist',
                'error'
            );
            $this->addLogComment(
                $e->getMessage(),
                'error'
            );
        }

        return $this->exProcessor->getExportFile();
    }

    /**
     * @param $debugData
     * @param null $type
     * @return $this
     */
    protected function addLogComment($debugData, $type = null)
    {
        $this->logger->info($debugData);

        return $this;
    }

    /**
     * @param $id
     * @return $this
     */
    public function createHistory($id, $file, $type)
    {
        $history = $this->historyFactory->create();
        $history->setJobId($id);
        $history->setType($type);
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();
        $history->setStartedAt($timeStamp);
        $this->logger->setFileName($file);
        $history->setFile($file);
        $history = $this->historyRepository->save($history);

        return $history;
    }

    /**
     * @param $id
     * @return $this
     */
    public function createExportHistory($id, $file, $type)
    {
        $history = $this->exportFactory->create();
        $history->setJobId($id);
        $history->setType($type);
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();
        $history->setStartedAt($timeStamp);
        $this->logger->setFileName($file);
        $history->setFile($file);
        $history = $this->historyExRepository->save($history);

        return $history;
    }

    /**
     * @param $file
     * @return array
     */
    public function scopeRun($file)
    {
        if ($this->directory->isFile("/firebear/" . $file . ".log")) {
            return explode(PHP_EOL, $this->directory->readFile("/firebear/" . $file . ".log"));
        }

        return false;
    }

    /**
     * @param $history
     * @return $this
     */
    public function saveFinishHistory($history)
    {
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();
        $history->setFinishedAt($timeStamp);
        $this->historyRepository->save($history);

        return $this;
    }

    /**
     * @param $history
     * @return $this
     */
    public function saveFinishExHistory($history)
    {
        $date = $this->timeZone->date();
        $timeStamp = $date->getTimestamp();
        $history->setFinishedAt($timeStamp);
        $this->historyExRepository->save($history);

        return $this;
    }

    /**
     * @return \Firebear\ImportExport\Logger\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @return mixed
     */
    public function getResultProcessor()
    {
       return $this->resultProcess;
    }

    public function setResultProcessor($result)
    {
        $this->resultProcess = $result;
    }
}
