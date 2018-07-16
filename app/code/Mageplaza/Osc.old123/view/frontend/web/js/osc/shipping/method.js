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
 * @copyright   Copyright (c) 2016 Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
define(["prototype"], function () {
    var MageplazaOscShippingMethod = new Class.create();
    MageplazaOscShippingMethod.prototype = {
        initialize: function (config) {
            this.shipingMethodContainer = $$(config.shipingMethodContainer).first();
            this.shippingMethodElements = $$(config.shippingMethodElements);
            this.shippingMethodAdvice = $$(config.shippingMethodAdvice).first();
            this.saveShippingMethodUrl = config.saveShippingMethodUrl;
            window.shippingMethod = {};
            window.shippingMethod.validator = null;
            this.initObserver();
        },
        initObserver: function () {
            this.shippingMethodElements.each(function (element) {
                element.observe('click', function () {
                    this.removeValidationAdvice();
                    this.saveShippingMethod(element.value);
                }.bind(this));
            }.bind(this))
        },

        saveShippingMethod: function (methodCode) {
            if (this.currentMethod !== methodCode) {
                var params = Form.serialize(this.shipingMethodContainer, true);
                MageplazaOsc.Request(this.saveShippingMethodUrl, {
                    method: 'post',
                    parameters: params
                });
                this.currentMethod = methodCode;
            }
        },
        removeValidationAdvice: function () {
            if (this.shippingMethodAdvice) {
                this.shippingMethodAdvice.update('').hide();
            }
        }
    };
    return MageplazaOscShippingMethod;
});


