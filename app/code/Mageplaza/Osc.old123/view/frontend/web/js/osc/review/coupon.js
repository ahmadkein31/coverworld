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
define(["prototype", "validation"], function () {
    /* Review Coupon Class */
    MageplazaOscReviewCoupon = Class.create();
    MageplazaOscReviewCoupon.prototype = {
        initialize: function (config) {
            this.couponContainer = $$(config.couponContainer).first();
            this.couponInput = $(config.couponInput);
            this.applyCouponUrl = config.applyCouponUrl;
            this.isAppliedCoupon = config.isAppliedCoupon;
            this.showApplyButton = config.showApplyButton;
            this.applyCouponButton = $$(config.applyCouponButton).first();
            this.cancelCouponButton = $$(config.cancelCouponButton).first();
            this.messageContainer = $$(config.messageContainer).first();
            this.errorClass = config.errorClass;
            this.successClass = config.successClass;
            this.initObserver();
        },

        initObserver: function () {
            if (this.showApplyButton) {
                if (this.applyCouponButton) {
                    this.applyCouponButton.observe('click', function () {
                        this.processCoupon();
                    }.bind(this));
                    this.cancelCouponButton.observe('click', function () {
                        this.processCoupon();
                    }.bind(this));
                }
            }
            else {
                if (this.couponInput) {
                    this.couponInput.observe('change', function () {
                        this.processCoupon();
                    }.bind(this))
                }
            }
        },
        processCoupon: function () {
            var couponContainer = this.couponContainer;
            couponContainer.select('.validation-advice').each(function (adviceEl) {
                adviceEl.remove();
            });
            this._removeAllMessage();
            if (this.showApplyButton) {
                if (!this.isAppliedCoupon) {
                    this.couponInput.addClassName('required-entry');
                    var isValidated = Validation.validate(this.couponInput);
                    this.couponInput.removeClassName('required-entry');
                    if (!isValidated) {
                        return false;
                    }
                }
                else {
                    this.couponInput.setValue('');
                }
            }
            else {
                if (!this.couponInput.getValue() && !this.isAppliedCoupon) {
                    return false;
                }
            }
            var messageContainer = this.messageContainer;
            var successClass = this.successClass;
            var errorClass = this.errorClass;
            var self = this;
            var requestOptions = {
                method: 'post',
                parameters: {
                    coupon_code: this.couponInput.getValue()
                },
                onComplete: function (transport) {
                    try {
                        var response = transport.responseText.evalJSON();
                        self.isAppliedCoupon = response.coupon_applied;
                        if (response.success == true) {
                            MageplazaOsc.showMessage(messageContainer, response.messages, successClass);
                        }
                        else if (response.messages) {
                            MageplazaOsc.showMessage(messageContainer, response.messages, errorClass);
                        }
                    } catch (e) {
                    }
                    if (self.isAppliedCoupon) {
                        self.applyCouponButton.hide();
                        self.cancelCouponButton.show();
                    }
                    else {
                        self.applyCouponButton.show();
                        self.cancelCouponButton.hide();
                    }
                    var newHeight = MageplazaOsc.getHeightFromElement(messageContainer);
                    MageplazaOsc.applyMorphEffect(messageContainer, newHeight, 0.3, function () {
                        messageContainer.setStyle({
                            'display': ''
                        })
                    });
                }
            }
            MageplazaOsc.Request(this.applyCouponUrl, requestOptions);
        },
        _removeAllMessage: function () {
            MageplazaOsc.removeMessage(this.couponContainer, this.errorClass);
            MageplazaOsc.removeMessage(this.couponContainer, this.successClass);
        }
    };
    return MageplazaOscReviewCoupon;
});


