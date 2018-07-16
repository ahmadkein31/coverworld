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
 * @copyright 	Copyright (c) 2017 Anowave (http://www.anowave.com/)
 * @license  	http://www.anowave.com/license-agreement/
 */

namespace Anowave\Ecopz\Block;

class Plugin extends \Anowave\Ec\Block\Plugin
{
	/**
	 * Block output modifier
	 *
	 * @param \Magento\Framework\View\Element\Template $block
	 * @param string $content
	 *
	 * @return string
	 */
	public function afterToHtml($block, $content)
	{
		/**
		 * Get parent content
		 */
		$content = parent::afterToHtml($block, $content);
		
		/**
		 * Do not employ code for base module
		 */
		if ('checkout.root' == $block->getNameInLayout())
		{
			return $content;
		}
		
		/**
		 * Call parent
		 */
		$content = parent::afterToHtml($block, $content);
		
		if ('checkout.cart' == $block->getNameInLayout())
		{
			return $this->augmentCartOscBlock($block, $content);
		}
		
		/**
		 * Add support for Mageplaza_Osc
		 */
		if ('mageplaza.osc.compatible-config' == $block->getNameInLayout())
		{
			return $this->augmentCheckoutOscBlock($block, $content);
		}
		
		return $content;
	}
	
	/**
	 * Modify cart output
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 *
	 * @return string
	 */
	protected function augmentCartOscBlock($block, $content)
	{
		return $content .= $block->getLayout()->createBlock('Anowave\Ec\Block\Track')->setTemplate('Anowave_Ecopz::cart.phtml')->setData
		(
			[
				'cart_push' => $this->_helper->getCheckoutPush($block, $this->_cart, $this->_coreRegistry)
			]
		)->toHtml();
	}
	
	/**
	 * Modify checkout output
	 *
	 * @param AbstractBlock $block
	 * @param string $content
	 *
	 * @return string
	 */
	protected function augmentCheckoutOscBlock($block, $content)
	{
		return $content .= $block->getLayout()->createBlock('Anowave\Ec\Block\Track')->setTemplate('Anowave_Ecopz::checkout.phtml')->setData
		(
			[
				'checkout_push' => $this->_helper->getCheckoutPush($block, $this->_cart, $this->_coreRegistry)
			]
		)->toHtml();
	}
}