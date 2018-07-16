/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'jquery',
        'Mageplaza_Osc/js/view/payment/iframe',
        'Magento_Checkout/js/action/set-payment-information',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Mageplaza_Osc/js/model/full-screen-loader',
        'mage/url',
    ],
    function ($, Component, setPaymentInformationAction, additionalValidators, fullScreenLoader, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/payment/authorizenet-directpost'
            },
            placeOrderHandler: null,
            validateHandler: null,

            setPlaceOrderHandler: function (handler) {
                this.placeOrderHandler = handler;
            },

            setValidateHandler: function (handler) {
                this.validateHandler = handler;
            },

            context: function () {
                return this;
            },

            isShowLegend: function () {
                return true;
            },

            getCode: function () {
                return 'authorizenet_directpost';
            },

            isActive: function () {
                return true;
            },

            /**
             * @override
             */
            placeOrder: function () {
                var self = this;

                if (this.validateHandler()) {
                    fullScreenLoader.startLoader();
                    this.isPlaceOrderActionAllowed(false);
                    $.when(setPaymentInformationAction(this.messageContainer, {
                        'method': self.getCode()
                    })).done(function (response) {
                        self.placeOrderHandler().fail(function () {
                            fullScreenLoader.stopLoader();
                        });
                        if (response) {
                            window.location.replace(url.build('checkout/onepage/success/'));
                        }
                        else {
                            window.location.replace(url.build('checkout/cart/'));
                        }
                    }).fail(function () {
                        fullScreenLoader.stopLoader();
                        self.isPlaceOrderActionAllowed(true);
                    }).always(function () {
                        fullScreenLoader.stopLoader();
                    });
                    ;
                }
            }
        });
    }
);
