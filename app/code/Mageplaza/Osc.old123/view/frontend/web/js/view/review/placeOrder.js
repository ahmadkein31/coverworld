/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Mageplaza_Osc/js/action/set-checkout-information'
    ],
    function (
        $,
        Component,
        additionalValidators,
        setCheckoutInformationAction
    ) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/container/review/place-order'
            },
            paymentButton: '#co-payment-form .payment-method._active button.action.primary.checkout',

            placeOrder: function () {
                var self = this;
                if (additionalValidators.validate()) {
                    $.when(setCheckoutInformationAction()).done(function () {
                        $("body").animate({ scrollTop: 0 }, "slow");
                        self._placeOrder();
                    });
                }

                return this;
            },

            _placeOrder: function () {
                var paymentButton = $(this.paymentButton);
                if (paymentButton.length) {
                    paymentButton.first().trigger('click');
                }
            },

            isPlaceOrderActionAllowed: function () {
                return true;
            }
        });
    }
);
