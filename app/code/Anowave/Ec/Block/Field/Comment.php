<?php
/**
 * Anowave Magento 2 Google Tag Manager Enhanced Ecommerce (UA) Tracking
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Anowave license that is
 * available through the world-wide-web at this URL:
 * http://www.anowave.com/license-agreement/
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category 	Anowave
 * @package 	Anowave_Ec
 * @copyright 	Copyright (c) 2018 Anowave (http://www.anowave.com/)
 * @license  	http://www.anowave.com/license-agreement/
 */
 
namespace Anowave\Ec\Block\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Comment extends \Magento\Config\Block\System\Config\Form\Field
{
	/**
	 * @var \Anowave\Ec\Model\Api
	 */
	protected $api = null;
	
	/**
	 * Block factory
	 * 
	 * @var \Magento\Framework\View\Element\BlockFactory
	 */
	protected $blockFactory;
	
	protected $request;
	
	/**
	 * Constructor 
	 * 
	 * @param \Magento\Framework\View\Element\BlockFactory $blockFactory
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Anowave\Ec\Model\Api $api
	 */
	public function __construct
	(
		\Magento\Framework\View\Element\BlockFactory $blockFactory,
		\Magento\Backend\Block\Template\Context $context,
		\Anowave\Ec\Model\Api $api
	)
	{
		/**
		 * @var  \Magento\Framework\App\Request\Http $request
		 */
		$this->request = $context->getRequest();
		
		/**
		 * Set block factory 
		 * 
		 * @var \Magento\Framework\View\Element\BlockFactory $blockFactory
		 */
		$this->blockFactory = $blockFactory;
		
		/**
		 * Set api 
		 * 
		 * @var \Anowave\Ec\Model\Api $api
		 */
		$this->api = $api;
		
		parent::__construct($context);
	}
	
	/**
	 * Get element HTML
	 * 
	 * {@inheritDoc}
	 * @see \Magento\Config\Block\System\Config\Form\Field::_getElementHtml()
	 */
	protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
		return parent::_getElementHtml($element) . $this->getCommentText('');
	}

    public function getCommentText($currentValue = '')
    {
    	$containers = array();
    	
    	$errors = [];
    	
    	try
    	{
    		foreach($this->getContainers() as $container)
    		{
    			$containers[] = "Container: <strong>$container->publicId</strong>,  Container ID: <strong>$container->containerId</strong>";
    		}
    	}
    	catch (\Exception $e)
    	{
    		$errors[] = $e->getMessage();
    	}
    	
    	if (!$errors)
    	{
    		if (!$this->getApi()->getClient()->isAccessTokenExpired())
    		{
    			return '<p class="note">' . nl2br(join(PHP_EOL, $containers)) . '</p>';
    		}
    	}
    	
    	return $this->blockFactory->createBlock('Anowave\Ec\Block\Comment')->setTemplate('comment.phtml')->setData(['errors' => $errors])->toHtml();
    }
    
    protected function getContainers()
    {
    	$account = $this->_scopeConfig->getValue('ec/api/google_gtm_account_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getCode());
    	
    	if ($account)
    	{
    		return $this->getApi()->getContainers($account);
    	}
    	
    	return array();
    }
    
    public function getStore()
    {
    	if ($this->request->getParam('store'))
    	{
    		return $this->_storeManager->getStore((int) $this->request->getParam('store'));
    	}
    	
    	return $this->_storeManager->getStore();
    }
    
    protected function getApi()
    {
    	return $this->api;
    }
}