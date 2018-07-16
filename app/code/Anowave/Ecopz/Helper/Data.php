<?php
/**
 * Anowave Magento 2 Onestepcheckout Add-on for GTM (UA) Tracking Mageplaza
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
 * @package 	Anowave_Ecopz
 * @copyright 	Copyright (c) 2018 Anowave (http://www.anowave.com/)
 * @license  	http://www.anowave.com/license-agreement/
 */

namespace Anowave\Ecopz\Helper;

use Anowave\Package\Helper\Package;

class Data extends \Anowave\Package\Helper\Package
{
	const CHECKOUT_STEP_CART 	 = 1;
	const CHECKOUT_STEP_CHECKOUT = 2;
	const CHECKOUT_STEP_SHIPPING = 3;
	const CHECKOUT_STEP_PAYMENT  = 4;
	const CHECKOUT_STEP_ORDER	 = 5;
	
	/**
	 * Package name
	 * @var string
	 */
	protected $package = 'MAGE2-ECOPZ';
	
	/**
	 * Config path 
	 * @var string
	 */
	protected $config = 'ecopz/general/license';
}