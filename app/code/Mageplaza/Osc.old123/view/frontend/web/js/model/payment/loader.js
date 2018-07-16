/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define(
    ['prototype'],
    function () {
        'use strict';

        var container = $('one-step-checkout-payment-method');
        var loadingClass = 'one-step-checkout-ajax-loading';
        return {

            /**
             * Start full page loader action
             */
            startLoader: function () {
                this.appendLoading(container);
            },

            /**
             * Stop full page loader action
             */
            stopLoader: function () {
                this.removeLoading(container);
            },
            appendLoading: function (block) {
                var ajaxLoading = new Element('div');
                ajaxLoading.addClassName(loadingClass);
                block.setStyle({
                    'position': 'relative'
                })
                block.insertBefore(ajaxLoading, block.down());
            },
            removeLoading: function (block) {
                var selector = "." + loadingClass;
                block.setStyle({
                    'position': ''
                })
                block.select(selector).each(function (el) {
                    el.remove();
                });
            }
        };
    }
);
