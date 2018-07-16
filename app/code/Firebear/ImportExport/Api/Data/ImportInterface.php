<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio GmbH. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Api\Data;

/**
 * Interface ImportInterface
 *
 * @package Firebear\ImportExport\Api\Data
 */
interface ImportInterface extends AbstractInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const IMPORT_SOURCE = 'import_source';

    const MAP = 'map';

    /**
     * @return string
     */
    public function getImportSource();

    /**
     * @param string $source
     *
     * @return ImportInterface
     */
    public function setImportSource($source);
}
