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
define(
    [
        "jquery",
        "Magento_Checkout/js/model/quote",
        "Mageplaza_Osc/js/model/payment/button-checkout-list",
        "mage/translate",
        "prototype",
        "varien/form"
    ],
    function (jq,
              quote,
              buttonCheckoutList,
              translate
    ) {

        MageplazaOscForm = Class.create();
        MageplazaOscForm.prototype = {
            initialize: function (config) {
                this.checkoutForm = new VarienForm(config.checkoutForm);
                //Shipping Method
                this.shippingMethodWrapper = config.shippingMethodWrapper;
                this.shippingMethodInput = config.shippingMethodInput;
                this.shippingMethodAdvice = config.shippingMethodAdvice;
                this.shippingValidationMessage = config.shippingValidationMessage;
                //Payment Method
                this.paymentMethodWrapper = config.paymentMethodWrapper;
                this.paymentMethodInput = config.paymentMethodInput;
                this.paymentMethodAdvice = config.paymentMethodAdvice;
                this.paymentValidationMessage = config.paymentValidationMessage;
                //Place Order
                this.reviewCartContainer = $$(config.reviewCartContainer).first();
                this.placeOrderButton = $(config.placeOrderButton);
                this.placeOrderUrl = config.placeOrderUrl;
                this.successUrl = config.successUrl;
                this.placeOrderButton = $(config.placeOrderButton);
                this.showGrandTotalAmount = config.showGrandTotalAmount;
                this.grandTotalAmount = this.placeOrderButton.select(config.grandTotalAmount).first();
                this.grandTotalAmountProcess = this.placeOrderButton.select(config.grandTotalAmountProcess).first();
                this.pleaseWaitNotice = $$(config.pleaseWaitNotice).first();
                this.disabledClassName = config.disabledClassName;
                this.overlayClassName = config.overlayClassName;
                Event.fire(document, 'mageplaza:form_init_before', {form: this});
                this.initObserver();
                Event.fire(document, 'mageplaza:form_init_after', {form: this});
            },
            initObserver: function () {
                if (this.placeOrderButton) {
                    this.placeOrderButton.observe('click', function () {
                        this.placeOrderProcess();
                    }.bind(this));
                }
            },
            placeOrderProcess: function () {
                if (this.validate()) {
                    if (quote.paymentMethod()) {
                        this.disablePlaceOrder();
                        this._placeOrderRequest();
                    } else {
                        alert(translate('Please select payment method.'))
                    }
                }
            },
            _placeOrderRequest: function () {
                var params = Form.serialize(this.checkoutForm.form, true);
                var requestOption = {
                    method: 'post',
                    parameters: params,
                    onComplete: function (transport) {
                        this.placeOrderComplete(transport);
                    }.bind(this)
                }

                new Ajax.Request(this.placeOrderUrl, requestOption);
            },
            evalToJson: function (data) {
                try {
                    data = data.evalJSON();
                } catch (e) {
                    data = {};
                }
                return data;
            },
            placeOrderComplete: function (transport) {
                if (transport && transport.responseText) {
                    var response = transport.responseText;
                    response = this.evalToJson(response);
                    this.placeOrderButton.fire('mageplaza:one_step_checkout_place_order_complete', {
                        object: this,
                        res: response
                    });
                    this.enablePlaceOrder();
                    var success = response.success;
                    if (success == true) {
                        buttonCheckoutList.clickButton(quote.paymentMethod().method);
                    }
                }
            },
            enablePlaceOrder: function () {
                this.hidePleaseWait();
                this.enablePlaceOrderButton();
                MageplazaOsc.removeOverlay(document.body, this.overlayClassName);
            },
            disablePlaceOrder: function () {
                MageplazaOsc.addOverlay(document.body, this.overlayClassName);
                this.showPleaseWait();
                this.disablePlaceOrderButton();
            },
            showPleaseWait: function () {
                this.pleaseWaitNotice.show();
                new Effect.Morph(this.pleaseWaitNotice, {
                    style: {
                        'marginTop': '10px'
                    },
                    'duration': 0.2
                });
            },
            hidePleaseWait: function () {
                this.pleaseWaitNotice.hide();
            },
            disablePlaceOrderButton: function () {
                this.placeOrderButton.addClassName(this.disabledClassName);
                this.placeOrderButton.disabled = true;
            },

            enablePlaceOrderButton: function () {
                this.placeOrderButton.removeClassName(this.disabledClassName);
                this.placeOrderButton.disabled = false;
            },
            _validateShippingMethod: function (formData) {
                var methodWrapper = $$(this.shippingMethodWrapper).first();
                var methodAdvice = $$(this.shippingMethodAdvice).first();
                var isValidated = true;
                if (methodWrapper && methodAdvice) {
                    var checkData = formData[this.shippingMethodInput];
                    if (!checkData) {
                        methodWrapper.addClassName('validation-failed');
                        methodAdvice.update(this.shippingValidationMessage).show();
                        isValidated = false;
                    } else {
                        methodWrapper.removeClassName('validation-failed');
                        methodAdvice.update('').hide();
                        isValidated = true;
                    }
                }
                return isValidated;
            },
            _validatePaymentMethod: function (formData) {
                var methodWrapper = $$(this.paymentMethodWrapper).first();
                var methodAdvice = $$(this.paymentMethodAdvice).first();
                var isValidated = true;
                if (methodWrapper && methodAdvice) {
                    var checkData = formData[this.paymentMethodInput];
                    if (!checkData) {
                        methodWrapper.addClassName('validation-failed');
                        methodAdvice.update(this.paymentValidationMessage).show();
                        isValidated = false;
                    } else {
                        methodWrapper.removeClassName('validation-failed');
                        methodAdvice.update('').hide();
                        isValidated = true;
                    }
                }

                return isValidated;
            },
            validate: function () {
                var formData = Form.serialize(this.checkoutForm.form, true);
                var result = this.checkoutForm.validator.validate();
                var isValidatedShipping = this._validateShippingMethod(formData);
                var shippingMethodWrapper = $$(this.shippingMethodWrapper).first();
                var isValidatedPayment = this._validatePaymentMethod(formData);
                var paymentMethodWrapper = $$(this.paymentMethodWrapper).first();
                if (result) {
                    if (!isValidatedShipping) {
                        shippingMethodWrapper.scrollTo();
                    }
                    else if (!isValidatedPayment) {
                        paymentMethodWrapper.scrollTo();
                    }
                }
                return (result && isValidatedShipping && isValidatedPayment);
            }
        };
        return MageplazaOscForm;
    });


