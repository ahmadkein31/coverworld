<?php
/**
 * Copyright © 2018 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Mageplaza\Osc\Plugin;

use Magento\Checkout\Model\Session;

class Customshipping
{
	 public function aroundCollectRates(
		\Magento\OfflineShipping\Model\Carrier\Freeshipping $subject ,
		\Closure $proceed ,
		\Magento\Quote\Model\Quote\Address\RateRequest $request
	)
	{

		if ($request->getDestRegionCode() == 'AK' || $request->getDestRegionCode() == 'HI' || $request->getDestRegionCode() == 'PR') { // add your state validation here
			return false;
		}
		return  $proceed($request);
		
	}
}