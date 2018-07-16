<?php

/**
 * Product:       Xtento_OrderExport (2.4.9)
 * ID:            %!uniqueid!%
 * Packaged:      %!packaged!%
 * Last Modified: 2015-08-17T13:41:32+00:00
 * File:          app/code/Xtento/OrderExport/Model/Output/OutputInterface.php
 * Copyright:     Copyright (c) 2018 XTENTO GmbH & Co. KG <info@xtento.com> / All rights reserved.
 */

namespace Xtento\OrderExport\Model\Output;

interface OutputInterface
{
    public function convertData($exportArray);
}