define(['jquery','mage/utils/wrapper'], function($, wrapper)
{
    'use strict';

    return function(checkEmailAvailability) 
    {
        return wrapper.wrap(checkEmailAvailability, function (originalAction, deferred, email) 
        {
        	if ('undefined' !== typeof AEC.Checkout.osc && 'undefined' !== typeof data)
        	{
        		/**
        		 * Set step
        		 */
        		data.ecommerce.checkout.actionField.step = AEC.Const.CHECKOUT_STEP_CHECKOUT;
        		
        		/**
        		 * Push step to dataLayer[] object
        		 */
        		dataLayer.push(data);
        	}
        	
            return originalAction(deferred, email);
        });
    };
});