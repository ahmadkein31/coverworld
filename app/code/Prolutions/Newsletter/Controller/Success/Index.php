<?php

namespace Prolutions\Newsletter\Controller\Success;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Show Newsletter Success page
     *
     * @return void
     */
    public function execute()
    {  
        $jsonResp['incache'] = false;
        $params = $this->getRequest()->getParams();    
        if(empty($params['cachecheeck']) == false) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $sessionManager = $objectManager->create('\Magento\Framework\Session\SessionManagerInterface');
            $jsonFactory = $objectManager->create('\Magento\Framework\Controller\Result\JsonFactory');
            $isInCache = $sessionManager->getNotInCache();
            if($isInCache) {
                $sessionManager->unsNotInCache();
                $jsonResp['incache'] = true;   
            }
            $result = $jsonFactory->create();
		    return $result->setData($jsonResp);
            
        } else {
            $this->_view->loadLayout();
            $this->_view->renderLayout();
        }
    }
}