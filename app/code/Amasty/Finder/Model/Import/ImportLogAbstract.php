<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */


/**
 * Copyright © 2015 Amasty. All rights reserved.
 */
namespace Amasty\Finder\Model\Import;

abstract class ImportLogAbstract extends \Magento\Framework\Model\AbstractModel
{
    abstract public function getFileState();

    abstract public function getFieldInErrorLog();

    public function getErrorsCollection()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $import = $om->create('Amasty\Finder\Model\Import');

        return $om->create('Amasty\Finder\Model\ImportErrors')->getCollection()->addFieldToFilter($this->getFieldInErrorLog(), $this->getId());
    }
}
