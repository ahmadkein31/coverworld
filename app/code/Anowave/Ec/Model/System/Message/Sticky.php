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

namespace Anowave\Ec\Model\System\Message;

class Sticky implements \Magento\Framework\Notification\MessageInterface
{
	/**
	 * @var \Anowave\Ec\Helper\Data
	 */
	protected $helper = null;
	
	/**
	 * Constructor 
	 * 
	 * @param \Anowave\Ec\Helper\Data $helper
	 */
	public function __construct
	(
		\Anowave\Ec\Helper\Data $helper
	)
	{
		$this->helper = $helper;
	}
	public function getIdentity()
	{
		return 'ec';
	}
	
	public function isDisplayed()
	{
		return '' !== (string) $this->helper->getConfig('ec/general/code') ? true : false;
	}
	
	public function getText()
	{
		return 'It seems you are using older version of GTM snippet. Please update using the splitted version provided by Google Tag Manager otherwise tracking may not work.';
	}
	
	public function getSeverity()
	{
		return self::SEVERITY_MAJOR;
	}
}