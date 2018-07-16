<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Cron;

use Firebear\ImportExport\Model\Job\Processor;

/**
 * Sales entity grids indexing observer.
 *
 * Performs handling cron jobs related to indexing
 * of Order, Invoice, Shipment and Creditmemo grids.
 */
class RunImportJobs
{
    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var \Firebear\ImportExport\Helper\Data
     */
    protected $helper;

    /**
     * RunImportJobs constructor.
     *
     * @param Processor $importProcessor
     */
    public function __construct(
        Processor $importProcessor,
        \Firebear\ImportExport\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->processor = $importProcessor;
    }

    /**
     * @param $schedule
     *
     * @return bool
     */
    public function execute($schedule)
    {
        $jobCode = $schedule->getJobCode();

        preg_match('/_id_([0-9]+)/', $jobCode, $matches);

        if (isset($matches[1]) && (int)$matches[1] > 0) {
            $jobId = (int)$matches[1];
            $file = $this->helper->beforeRun($jobId);
            $history = $this->helper->createHistory($jobId, $file, 'cron');
            $this->processor->debugMode = $this->helper->getDebugMode();
            $this->processor->setLogger($this->helper->getLogger());
            $this->processor->process($jobId);
            $this->helper->saveFinishHistory($history);

            return true;
        }

        return false;
    }
}
