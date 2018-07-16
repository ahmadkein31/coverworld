<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ImportExport\Traits\Export;

trait Products
{
    use Entity;
    
    /**
     * @return mixed
     */
    public function getFieldsForExport()
    {
        $productIds = $this->_getEntityCollection()->getAllIds(100);
        $stockItemRows = $this->prepareCatalogInventory($productIds);
        $multirawData = $this->collectMultirawData();
        $this->rowCustomizer->prepareData($this->_getEntityCollection(), $productIds);
        $this->setHeaderColumns($multirawData['customOptionsData'], $stockItemRows);
        $this->_headerColumns = $this->rowCustomizer->addHeaderColumns($this->_headerColumns);

        return array_unique($this->_headerColumns);
    }
}
