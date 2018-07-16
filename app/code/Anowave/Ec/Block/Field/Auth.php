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
use Magento\Backend\Block\Template;

class Auth extends \Magento\Config\Block\System\Config\Form\Field
{
	/**
	 * @var \Anowave\Ec\Model\Api
	 */
	protected $api;
	
	/**
	 * Constructor 
	 * 
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Anowave\Ec\Model\Api $api
	 * @param array $data
	 */
    public function __construct
    (
    	\Magento\Backend\Block\Template\Context $context,
    	\Anowave\Ec\Model\Api $api,
    	array $data = [])
    {
    	parent::__construct($context, $data);
    	
    	/**
    	 * Set API 
    	 * 
    	 * @var \Anowave\Ec\Model\Api
    	 */
    	$this->api = $api;
    }
	
	/**
	 * Get element content
	 * 
	 * @see \Magento\Config\Block\System\Config\Form\Field::_getElementHtml()
	 */
	protected function _getElementHtml(AbstractElement $element)
	{
		return $this->getLayout()->createBlock('Anowave\Ec\Block\Comment')->setTemplate('auth.phtml')->setData
		(
			array
			(
				'api' => $this->api
			)
		)->toHtml();
	}
}