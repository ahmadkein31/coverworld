<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Magento\Backend\App\Area\FrontNameResolver;

/**
 * Command prints list of available currencies
 */
class ExportJobRunCommand extends ExportJobAbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('export:job:run')
            ->setDescription('Generate Firebear Export Jobs')
            ->setDefinition(
                [
                    new InputArgument(
                        self::JOB_ARGUMENT_NAME,
                        InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                        'Space-separated list of import job ids or omit to generate all jobs.'
                    )
                ]
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(FrontNameResolver::AREA_CODE);
        $timeStart = time();
        $requestedIds = $input->getArgument(self::JOB_ARGUMENT_NAME);
        $requestedIds = array_filter(array_map('trim', $requestedIds), 'strlen');
        $jobCollection = $this->factory->create()->getCollection();
        $jobCollection->addFieldToFilter('is_active', 1);

        if ($requestedIds) {
            $jobCollection->addFieldToFilter('entity_id', ['in' => $requestedIds]);
        }

        if ($jobCollection->getSize()) {
            foreach ($jobCollection as $job) {
                $id = (int)$job->getEntityId();
                try {
                    $file = $this->helper->beforeRun($id);
                    $history = $this->helper->createExportHistory($id, $file, 'console');
                    $this->processor->debugMode = $this->debugMode = $this->helper->getDebugMode();
                    $this->processor->setLogger($this->helper->getLogger());
                    $result = $this->processor->process($id);
                    $timeFinish = time();
                    $totalTime = $timeFinish - $timeStart;
                    if ($result === true) {
                        $this->addLogComment(
                            'Job #' . $id . ' was generated successfully in ' . $totalTime . ' seconds',
                            $output,
                            'info'
                        );
                    } else {
                        $this->addLogComment(
                            $result,
                            $output,
                            'error'
                        );
                    }
                    $this->helper->saveFinishExHistory($history);
                } catch (\Exception $e) {
                    $this->addLogComment(
                        'Job #' . $id . ' can\'t be exported. Check if job exist',
                        $output,
                        'error'
                    );
                    $this->addLogComment(
                        $e->getMessage(),
                        $output,
                        'error'
                    );
                }
            }
        } else {
            $this->addLogComment(
                'No jobs found',
                $output,
                'error'
            );
        }
    }
}