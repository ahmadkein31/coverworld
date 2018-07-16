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
    /*Payment Method Class*/
    MageplazaOscPaymentMethod = Class.create();
    MageplazaOscPaymentMethod.prototype = {
        initialize: function (config) {
            this.paymentMethodContainer = $$(config.paymentMethodContainer).first();
            this.paymentMethodWrapper = $$(config.paymentMethodWrapper).first();
            this.paymentMethodElements = $$(config.paymentMethodElements);
            this.paymentMethodAdvice = $$(config.paymentMethodAdvice).first();
            this.savePaymentMethodUrl = config.savePaymentMethodUrl;
            this.cvv = {
                container: $$(config.cvv.container).first(),
                showEl: $$(config.cvv.showEl).first(),
                hideEl: $$(config.cvv.hideEl).first()
            };
            this.initPayment();
            this.initObserver();
        },
        initPayment: function () {
            var me = this;
            this.paymentMethodElements.each(function (element) {
                var methodCode = element.value;
                var additionalInfoContainer = $('payment_form_' + methodCode);
                if (additionalInfoContainer) {
                    additionalInfoContainer.setStyle({'overflow': 'hidden', 'display': 'none'})
                }
                if (element.checked) {
                    this.changeVisible(methodCode, false);
                    this.currentMethod = methodCode;
                } else {
                    this.changeVisible(methodCode, true);
                }
            }.bind(this));
        },
        initObserver: function () {
            var self = this;
            this.paymentMethodElements.each(function (element) {
                element.observe('click', function () {
                    this.removeValidationAdvice();
                    this.switchMethod(element.value);
                }.bind(this));
                var block = 'payment_form_' + element.value;
                [block + '_before', block, block + '_after'].each(function (elementId) {
                    var element = $(elementId);
                    if (!element) {
                        return;
                    }
                    Form.getElements(element).each(function (formElement) {
                        formElement.observe('change', function (e) {
                            self.savePaymentMethod();
                            Validation.reset(formElement);
                        });
                    }.bind(this));
                });

            }.bind(this));
            var cvvShowEl = this.cvv.showEl;
            var cvvHideEl = this.cvv.hideEl;
            if (cvvShowEl) {
                cvvShowEl.observe('click', function (evt) {
                    this.toogleCvvTooltip(evt, true);
                }.bind(this));
            }
            if (cvvHideEl) {
                cvvHideEl.observe('click', function (evt) {
                    this.toogleCvvTooltip(evt, false);
                }.bind(this))
            }
            if (!this.paymentMethodWrapper.appendQueueAfterFinish) {
                this.paymentMethodWrapper.appendQueueAfterFinish = function () {
                    this.storeValues = {};
                    Form.getElements(this.paymentMethodWrapper).each(function (el) {
                        var elementId = el.id;
                        if (elementId in this.storeValues) {
                            el.setValue(this.storeValues[elementId]);
                        }

                    }.bind(this))
                }.bind(this);
            }
            if (!this.paymentMethodWrapper.removeQueueAfterFinish) {
                this.paymentMethodWrapper.removeQueueAfterFinish = function () {
                    Form.getElements(this.paymentMethodWrapper).each(function (el) {
                        var elementId = el.id;
                        if (elementId in this.storeValues) {
                            el.setValue(this.storeValues[elementId]);
                        }

                    }.bind(this));
                    this.storeValues = {};
                }.bind(this);
            }

        },
        removeValidationAdvice: function () {
            if (this.paymentMethodAdvice) {
                this.paymentMethodAdvice.update('').hide();
            }
        },
        toogleCvvTooltip: function (evt, isShow) {
            if (this.cvv.container) {
                if (isShow) {
                    this.cvv.container.setStyle({
                        display: '',
                        top: (Event.pointerY(evt)) + 40 + 'px'
                    });
                }
                else {
                    this.cvv.container.setStyle({
                        display: 'none'
                    });
                }
            }
            evt.stop();
        },

        switchMethod: function (methodCode) {
            var hideOldForm = true;
            if (methodCode === 'customercredit') {
                hideOldForm = false;
                var element = $('p_method_customercredit');
                if (!element || !element.checked) {
                    methodCode = '';
                }
            }

            if (hideOldForm && this.currentMethod && $('payment_form_' + this.currentMethod + '_preencrypt')) {
                this.changeVisible(this.currentMethod + '_preencrypt', true);
            }

            if (hideOldForm && this.currentMethod && $('payment_form_' + this.currentMethod)) {
                this.changeVisible(this.currentMethod, true);
            }

            if ($('payment_form_' + methodCode) || $('payment_form_' + methodCode + '_preencrypt')) {
                if ($('payment_form_' + methodCode)) {
                    this.changeVisible(methodCode, false)
                } else {
                    this.changeVisible(methodCode + '_preencrypt', false);
                }
            } else {
                //Event fix for payment methods without form like "Check / Money order"
                $(document.body).fire('payment-method:switched', {method_code: methodCode});
            }

            if (hideOldForm) {
                this.currentMethod = methodCode;
            }
            this.savePaymentMethod();
            if (typeof MultiFees !== 'undefined') {
                MultiFees.showPayment();
            }
        },
        changeVisible: function (methodCode, mode) {
            var block = 'payment_form_' + methodCode;
            [block + '_before', block, block + '_after'].each(function (el) {
                var element = $(el);
                if (element) {
                    element.setStyle({
                        'overflow': 'hidden'
                    });
                    if (!mode) {
                        element.setStyle({
                            'display': '',
                            'height': '0px'
                        })
                        var newHeight = MageplazaOsc.getHeightFromElement(element);
                        MageplazaOsc.applyMorphEffect(element, newHeight, 0.3, function () {
                            element.setStyle({
                                'height': ''
                            })
                        })
                    }
                    else {
                        MageplazaOsc.applyMorphEffect(element, 0, 0.5, function () {
                            element.setStyle({
                                'display': 'none'
                            })
                        })
                    }
                    element.select('input', 'select', 'textarea', 'button').each(function (field) {
                        field.disabled = mode;
                    });
                }
            });
        },
        savePaymentMethod: function () {
            var isValid = true;
            var block = 'payment_form_' + this.currentMethod;
            [block + '_before', block, block + '_after'].each(function (el) {
                var element = $(el);
                if (element) {
                    isValid = this.validateElement(element);
                }

            }.bind(this));
            if (!isValid) {
                return;
            }
            window.payment.currentMethod = this.currentMethod;
            //Save payment method request
            var params = Form.serialize(this.paymentMethodContainer, true);
            MageplazaOsc.Request(this.savePaymentMethodUrl, {
                method: 'post',
                parameters: params
            });
        },
        validateElement: function (element) {
            var isValid = true;
            Form.getElements(element).each(function (vElm) {
                var cn = $w(vElm.className);
                isValid = isValid && cn.all(function (name) {
                        var v = Validation.get(name);
                        var checked = true;
                        try {
                            if (Validation.isVisible(vElm) && !v.test($F(vElm), vElm)) {
                                checked = false;
                            } else {
                                checked = true;
                            }
                        } catch (e) {
                            checked = true;
                        }
                        return checked;
                    });
            })
            return isValid;
        }

    };
    return MageplazaOscPaymentMethod;
});


