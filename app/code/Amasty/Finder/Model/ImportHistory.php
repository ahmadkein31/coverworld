<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Finder\Model;


class ImportHistory extends \Amasty\Finder\Model\Import\ImportLogAbstract
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Finder\Model\Resource\ImportHistory');

    }

    public function getFileState()
    {
        return \Amasty\Finder\Helper\Data::FILE_STATE_ARCHIVE;
    }

    public function getFieldInErrorLog()
    {
        return 'import_file_log_history_id';
    }


    public function clearArchive()
    {
        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();

        $lifetime = $om->create("Amasty\Finder\Helper\Data")->getArchiveLifetime();
        $date = strftime('%Y-%m-%d %H:%M:%S', strtotime("-{$lifetime} days"));
        $list = $this->getCollection()->addFieldToFilter('ended_at', array("lteq" => $date));
        $list->walk('delete');
    }

    public function afterDelete()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $file = $om->create("Amasty\Finder\Helper\Data")->getImportArchiveDir().$this->getId().'.csv';

        if(is_file($file)) {
            unlink($file);
        }
        return parent::afterDelete();
    }
}
