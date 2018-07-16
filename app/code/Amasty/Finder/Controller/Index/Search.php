<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Finder
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Finder\Controller\Index;


use Magento\Framework\App\Action\Context;

class Search extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }


    public function execute()
    {
        $finderId = $this->getRequest()->getParam('finder_id');
        $finder = $this->_objectManager->create('Amasty\Finder\Model\Finder')->load($finderId);

        $dropdowns = $this->getRequest()->getParam('finder');
        if ($dropdowns){
            $finder->saveFilter($dropdowns, $this->getRequest()->getParam('category_id'));
        }

        $backUrl = $this->_objectManager->get('Magento\Framework\Url\Decoder')->decode($this->getRequest()->getParam('back_url'));

        $backUrl = $this->_getModifiedBackUrl($finder, $backUrl);


        if ($this->scopeConfig->getValue('amfinder/general/clear_other_conditions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){

            $finders = $finder->getCollection()->addFieldToFilter('finder_id', array('neq' => $finder->getId()));
            foreach ($finders as $f) {
                $f->resetFilter();
            }
        }

        if ($this->getRequest()->getParam('reset')){
            $finder->resetFilter();

            if ($this->scopeConfig->getValue('amfinder/general/reset_home', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
                $backUrl ='/';
            } else {
                $backUrl = $finder->removeGet($backUrl, 'find');
            }
        }

        $this->getResponse()->setRedirect($backUrl);
    }


    /**
     * Replaces or add find parameter in the search results url
     *
     * @param Amasty_Finder_Model_Finder $finder
     * @param string $backUrl
     * @return string new
     */
    protected function _getModifiedBackUrl($finder, $backUrl)
    {
        $path  = $backUrl;
        $query = array();

        if (strpos($backUrl, '?') !== false){
            list($path, $query) = explode('?', $backUrl, 2);
            if ($query){
                $query = explode('&', $query);
                $params = array();
                foreach ($query as $pair){
                    if (strpos($pair, '=') !== false){
                        $pair = explode('=', $pair);
                        $params[$pair[0]] = $pair[1];
                    }
                }
                $query = $params;
            }
        }

        $query['find'] = $finder->createUrlParam($this->scopeConfig);
        if (!$query['find'] || !$this->scopeConfig->getValue('amfinder/general/seo_urls', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
            $query['find'] = null;
        }

        $query = http_build_query($query);
        $query = str_replace('%2F', '/', $query);
        if ($query){
            $query = '?' . $query;
        }

        $backUrl = $path . $query;

        return $backUrl;
    }
}
