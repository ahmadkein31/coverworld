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
    /*Gift Message Class*/
    MageplazaOscReviewGiftmessage = Class.create();
    MageplazaOscReviewGiftmessage.prototype = {
        initialize: function (config) {
            this.giftMessageUrl = config.giftMessageUrl;
            this.giftMessageContainer = $$(config.giftMessageContainer).first();
            this.giftMessageForm = $$(config.giftMessageForm).first();
            this.useGiftmessageCheckbox = $$(config.useGiftmessageCheckbox).first();
            this.isUsedGiftMessage = $$(config.isUsedGiftMessage).first();
            this.inputElementIds = config.inputElementIds;
            this.initGiftMessage();
            this.initObserver();
        },
        initGiftMessage: function () {
            if (this.useGiftmessageCheckbox &&this.useGiftmessageCheckbox.checked) {
                this.processGiftMessage(this.useGiftmessageCheckbox);
            }
        },
        initObserver: function () {
            if (this.useGiftmessageCheckbox) {
                this.useGiftmessageCheckbox.observe('change', function (evt) {
                    this.processGiftMessage(evt);
                }.bind(this));
            }
            this.inputElementIds.each(function (el) {
                var element = $(el);
                if (element) {
                    element.observe('change', function () {
                        this.saveFormData();
                    }.bind(this))
                }
            }.bind(this))
        },
        saveFormData: function () {
            var params = Form.serialize(this.giftMessageContainer, true);
            var requestOptions = {
                method: 'post',
                parameters: params
            }
            MageplazaOsc.Request(this.giftMessageUrl, requestOptions);
        },
        processGiftMessage: function (evt) {
            if (typeof(evt) === 'object') {
                var element;
                if (evt.target) {
                    element = evt.target;
                }
                else {
                    element = evt;
                }
                if (element.checked) {
                    this.isUsedGiftMessage.value = 1;
                    this.showGiftMessage();
                }
                else {
                    this.isUsedGiftMessage.value = 0;
                    this.hideGiftMessage();
                }
                this.saveFormData();
            }
        },
        showGiftMessage: function () {
            var giftMessageForm = this.giftMessageForm;
            giftMessageForm.setStyle({'display': ''});
            var newHeight = MageplazaOsc.getHeightFromElement(this.giftMessageForm);
            MageplazaOsc.applyMorphEffect(this.giftMessageForm, newHeight, 0.3, function () {
                giftMessageForm.setStyle({'height': ''});
            })
        },
        hideGiftMessage: function () {
            var giftMessageForm = this.giftMessageForm;
            MageplazaOsc.applyMorphEffect(this.giftMessageForm, 0, 0.5, function () {
                giftMessageForm.setStyle({'display': 'none'});
            });
            this.clearInput();
        },
        clearInput: function () {
            this.inputElementIds.each(function (el) {
                if ($(el)) {
                    $(el).setValue('');
                }
            })
        }
    };
    return MageplazaOscReviewGiftmessage;
});


