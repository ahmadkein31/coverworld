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

class Finder extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    protected $objectManager;


    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();;
        $this->_init('Amasty\Finder\Model\Resource\Finder');

    }

    public function getDropdowns()
    {

        /** @var \Magento\Framework\ObjectManagerInterface $om */
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        /** @var \Amasty\Finder\Model\Dropdown $dropdown */
        $dropdown = $om->create('Amasty\Finder\Model\Dropdown');
        $collection = $dropdown->getResourceCollection()->addFieldToFilter('finder_id', $this->getId());
        $collection->getSelect()->order('pos');

        return $collection;
    }

    public function saveFilter($dropdowns, $categoryId = 0)
    {
        /**
         * @var $session \Amasty\Finder\Model\Session
         */
        $session = $this->objectManager->get('Amasty\Finder\Model\Session');
        $name    = 'amfinder_' . $this->getId();

        if (!$dropdowns)
            return false;

        if (!is_array($dropdowns))
            return false;

        $safeValues = array();
        $id      = 0;
        $current = 0;
        foreach ($this->getDropdowns() as $d){
            $id = $d->getId();
            $safeValues[$id] = isset($dropdowns[$id]) ? $dropdowns[$id] : 0;
            if  (isset($dropdowns[$id]) && ($dropdowns[$id])){
                $current = $dropdowns[$id];
            }
        }

        if ($id) {
            $safeValues['last']    = $safeValues[$id];
            $safeValues['current'] = $current;
        }

        $safeValues['filter_category_id'] = $categoryId;
        $session->setData($name, $safeValues);

        return true;
    }

    public function resetFilter()
    {
        /**
         * @var $session \Amasty\Finder\Model\Session
         */
        $session = $this->objectManager->get('Amasty\Finder\Model\Session');
        $name    = 'amfinder_' . $this->getId();

        $session->setData($name, null);
        return true;
    }

    public function applyFilter(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Catalog\Model\Layer $layer, $isUniversal, $isUniversalLast)
    {
        $id = $this->getSavedValue('current');
        if (!$id){
            return false;
        }

        if (!$this->isAllowedInCategory($scopeConfig, $layer->getCurrentCategory()->getId())){
            return false;
        }

        $finderId = $this->getId();



        $collection = $layer->getProductCollection();
        $cnt = $this->countEmptyDropdowns();
        $this->getResource()->addConditionToProductCollection($collection, $id, $cnt, $finderId, $isUniversal, $isUniversalLast);

        return true;
    }


    public function getSavedValue($dropdownId)
    {
        /**
         * @var $session \Amasty\Finder\Model\Session
         */
        $session = $this->objectManager->get('Amasty\Finder\Model\Session');
        $name    = 'amfinder_' . $this->getId();

        $values = $session->getData($name);

        if (!is_array($values))
            return 0;

        if (empty($values[$dropdownId]))
            return 0;

        return $values[$dropdownId];
    }

    public function importUniversal($file)
    {
        return $this->getResource()->importUniversal($this, $file);
    }

    public function updateLinks()
    {
        return $this->getResource()->updateLinks();
    }

    public function deleteMapRow($id)
    {
        return $this->getResource()->deleteMapRow($id);
    }

    public function isDeletable($id)
    {
        return $this->getResource()->isDeletable($id);
    }

    public function newSetterId($id)
    {
        return $this->getResource()->newSetterId($id);
    }

    public function countEmptyDropdowns()
    {
        $num = 0;

        $session = $this->objectManager->get('Amasty\Finder\Model\Session');
        $name    = 'amfinder_' . $this->getId();

        // we assume the values are always exist.
        $values = $session->getData($name);
        foreach ($values as $k=>$dropdown){
            if (is_numeric($k) && !$dropdown){
                $num++;
            }
        }

        return $num;
    }

    public function getDropdownsByCurrent($current)
    {
        $dropdowns = array();
        while ($current){
            $valueModel = $this->objectManager->create('Amasty\Finder\Model\Value')->load($current);
            $dropdowns[$valueModel->getDropdownId()]= $current;
            $current = $valueModel->getParentId();
        }

        return $dropdowns;
    }


    /**
     * For current finder creates his description for URL
     *
     * @return string like year-make-model-number.html
     */
    public function createUrlParam(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $sep    = $scopeConfig->getValue('amfinder/general/separator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $suffix = $scopeConfig->getValue('amfinder/general/suffix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);


        $urlParam = '';

        /**
         * @var $session \Amasty\Finder\Model\Session
         */
        $session = $this->objectManager->get('Amasty\Finder\Model\Session');
        $name    = 'amfinder_' . $this->getId();

        $values = $session->getData($name);
        if (!is_array($values)){
            $values = array();
        }

        foreach ($values as $k => $value) {
            if ('current' == $k) {
                $urlParam .= $value . $suffix;
                break;
            }

            if (!empty($value) && is_numeric($k)){
                $valueModel =  $this->objectManager->create('Amasty\Finder\Model\Value')->load($value);
                if ($valueModel->getId()){
                    $urlParam .= strtolower(preg_replace('/[^\da-zA-Z]/', '-', $valueModel->getName())) . $sep;
                }
            }
        }

        return $urlParam;
    }

    /**
     *  Get last `number` part from the year-make-model-number.html string
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param string $param like year-make-model-number.html
     *
     * @return string like number
     */
    public function parseUrlParam(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, $param)
    {
        $sep    = $scopeConfig->getValue('amfinder/general/separator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $suffix = $scopeConfig->getValue('amfinder/general/suffix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $param = explode($sep, $param);
        $param = str_replace($suffix, '', $param[count($param)-1]);

        return $param;
    }



    public function removeGet($url, $name, $amp = true) {
        $url = str_replace("&amp;", "&", $url);
        list($urlPart, $qsPart) = array_pad(explode("?", $url), 2, "");
        parse_str($qsPart, $qsVars);
        unset($qsVars[$name]);

        if (count($qsVars) > 0) {
            $url = $urlPart."?".http_build_query($qsVars);
            if ($amp)
                $url = str_replace("&", "&amp;", $url);
        }
        else {
            $url = $urlPart;
        }
        return $url;
    }

    public function getInitialCategoryId()
    {
        /**
         * @var $session \Amasty\Finder\Model\Session
         */
        $session = $this->objectManager->get('Amasty\Finder\Model\Session');
        $name    = 'amfinder_' . $this->getId();
        $value = $session->getData($name);

        return $value['filter_category_id'];
    }

    public function isAllowedInCategory(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, $currentCategoryId)
    {
        $res = $scopeConfig->getValue('amfinder/general/category_search', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$res){
            return true;
        }

        if (!$this->getInitialCategoryId()){
            return true;
        }

        return ($this->getInitialCategoryId() == $currentCategoryId);
    }


    public function afterDelete()
    {
        $this->objectManager->create('Amasty\Finder\Model\Import')->afterDeleteFinder($this->getId());
        return parent::afterDelete();
    }
}
