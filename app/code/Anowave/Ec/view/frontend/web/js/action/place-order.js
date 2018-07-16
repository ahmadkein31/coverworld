define(['jquery','mage/utils/wrapper'], function ($, wrapper) 
{
    'use strict';

    return function (placeOrderAction) 
    {
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) 
        {
        	if ('undefined' !== typeof data)
        	{
        		/**
        		 * Set step
        		 */
        		data.ecommerce.checkout.actionField.step = AEC.Const.CHECKOUT_STEP_ORDER;
        		
        		/**
        		 * Push step to dataLayer[] object
        		 */
        		dataLayer.push(data);
        	}

            return originalAction(paymentData, messageContainer);
        });
    };
});