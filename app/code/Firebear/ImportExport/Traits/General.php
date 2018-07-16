<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Traits;

use Symfony\Component\Console\Output\OutputInterface;

trait General
{
    /**
     * @param $debugData
     * @param OutputInterface|null $output
     * @param null $type
     * @return $this
     */
    public function addLogWriteln($debugData, OutputInterface $output = null, $type = null)
    {
        
        if ($debugData instanceof \Magento\Framework\Phrase) {
            $this->_logger->info($debugData->__toString());
        } else {
            $this->_logger->info($debugData);
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
     * @return mixed
     */
    public function getDuplicateFields()
    {
        return $this->duplicateFields;
    }

    /**
     * @param $logger
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->_logger = $logger;

        return $this;
    }
}
