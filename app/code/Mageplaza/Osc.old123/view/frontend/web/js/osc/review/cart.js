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
define(["jquery", "Magento_Customer/js/customer-data", "prototype", "validation"], function (jq, customerData) {
    /* Review Cart Class */
    MageplazaOscReviewCart = Class.create();
    MageplazaOscReviewCart.prototype = {
        initialize: function (config) {
            this.removeProductLinks = $$(config.removeProductLinks);
            this.removeProductConfirmMessage = config.removeProductConfirmMessage;
            this.plusProductLinks = $$(config.plusProductLinks);
            this.minusProductLinks = $$(config.minusProductLinks);
            this.updateProductInput = $$(config.updateProductInput);
            this.ajaxCartItemUrl = config.ajaxCartItemUrl;
            this.initObserver();
        },

        initObserver: function () {
            this.removeProductLinks.each(function (link) {
                link.observe('click', function (evt) {
                    this.onClickRemoveProductLink(evt);
                }.bind(this))
            }.bind(this));
            this.plusProductLinks.each(function (link) {
                link.observe('click', function (evt) {
                    this.onClickPlusProductLink(evt);
                }.bind(this))
            }.bind(this));
            this.minusProductLinks.each(function (link) {
                link.observe('click', function (evt) {
                    this.onClickMinusProductLink(evt);
                }.bind(this))
            }.bind(this));
            this.updateProductInput.each(function (link) {
                link.observe('change', function (evt) {
                    this.onChangeProductInput(evt);
                }.bind(this))
            }.bind(this));

        },
        onClickMinusProductLink: function (evt) {
            var itemId = evt.target.id;
            this._processCartItem('minus', itemId);
            evt.stop;
        },
        onClickPlusProductLink: function (evt) {
            var itemId = evt.target.id;
            this._processCartItem('plus', itemId);
            evt.stop;
        },
        onChangeProductInput: function (evt) {
            var itemId = evt.target.id;
            var qty = evt.target.value;
            this._processCartItem('update', itemId, qty);
            evt.stop;
        },
        onClickRemoveProductLink: function (evt) {
            if (!confirm(this.removeProductConfirmMessage)) {
                return false;
            }
            var itemId = evt.target.id;
            this._processCartItem('remove', itemId);
            evt.stop();
        },
        _processCartItem: function (action, itemId, qty) {
            var self = this;
            var requestOptions = {
                method: 'post',
                parameters: {
                    action: action,
                    id: itemId,
                    qty: qty
                },
                onComplete: function (transport) {
                    try {
                        var response = transport.responseText.evalJSON();
                        if (!response.success && response.error) {
                            alert(response.error);
                        }
                        else {
                            self.updateMiniCart();
                        }
                    } catch (e) {
                    }
                }
            };
            MageplazaOsc.Request(this.ajaxCartItemUrl, requestOptions);
        },
        updateMiniCart: function () {
            var miniCart = jq('[data-block="minicart"]');
            miniCart.trigger('contentLoading');
            customerData.reload('cart', true);
            miniCart.trigger('contentUpdated');
        }

    };
    return MageplazaOscReviewCart;
});


