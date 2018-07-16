<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Console\Command;

use Firebear\ImportExport\Model\Job\Processor;
use Firebear\ImportExport\Model\JobFactory;
use Firebear\ImportExport\Model\JobRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Psr\Log\LoggerInterface;

/**
 * Command prints list of available currencies
 */
class ImportJobAbstractCommand extends Command
{
    const JOB_ARGUMENT_NAME = 'job';

    /**
     * @var JobFactory
     */
    protected $factory;

    /**
     * @var JobRepository
     */
    protected $repository;

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    protected $debugMode;

    protected $helper;

    /**
     * ImportJobAbstractCommand constructor.
     * @param JobFactory $factory
     * @param JobRepository $repository
     * @param LoggerInterface $logger
     * @param Processor $importProcessor
     * @param \Firebear\ImportExport\Helper\Data $helper
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        JobFactory $factory,
        JobRepository $repository,
        \Psr\Log\LoggerInterface $logger,
        Processor $importProcessor,
        \Firebear\ImportExport\Helper\Data $helper,
        \Magento\Framework\App\State $state
    ) {
        parent::__construct();
        $this->factory = $factory;
        $this->repository = $repository;
        $this->processor = $importProcessor;
        $this->state = $state;
        $this->logger = $logger;
        $this->helper = $helper;
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
                    $debugData = '<error>' . $debugData .'</error>';
                    break;
                case 'info':
                    $debugData = '<info>' . $debugData .'</info>';
                    break;
                default:
                    $debugData = '<comment>' . $debugData .'</comment>';
                    break;
            }

            $output->writeln($debugData);
        }

        return $this;
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }
}
