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
        'underscore',
        'jquery',
        'Magento_Checkout/js/view/summary/item/details',
        'Magento_Checkout/js/model/quote',
        'Mageplaza_Osc/js/action/update-item',
        'mage/url'
    ],
    function (_,
              $,
              Component,
              quote,
              updateItemAction,
              url) {
        "use strict";

        var products = window.checkoutConfig.quoteItemData;

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/container/summary/item/details'
            },

            getProductUrl: function (parent) {
                var item = _.find(products, function (product) {
                    return product.item_id == parent.item_id;
                });

                if (item && item.hasOwnProperty('product') &&
                    item.product.hasOwnProperty('request_path') && item.product.request_path) {
                    return url.build(item.product.request_path);
                }

                return false;
            },

            /**
             * Plus item qty
             *
             * @param id
             * @param event
             */
            plusQty: function (id, event) {
                var target = $(event.target).prev().children(".item_qty");
                var qty = parseInt(target.val()) + 1;
                var itemId = parseInt(target.attr("id"));
                //target.val(qty);
                this.updateItem(itemId, qty);
            },

            /**
             * Minus item qty
             *
             * @param item
             * @param event
             */
            minusQty: function (item, event) {
                var target = $(event.target).next().children(".item_qty");
                var qty = parseInt(target.val()) - 1;
                var itemId = parseInt(target.attr("id"));
                //target.val(qty);
                this.updateItem(itemId, qty);
            },

            /**
             * Change item qty in input box
             *
             * @param item
             * @param event
             */
            changeQty: function (item, event) {
                var target = $(event.target);
                var qty = parseInt(target.val());
                var itemId = parseInt(target.attr("id"));

                this.updateItem(itemId, qty);
            },

            /**
             * Remove item by id
             *
             * @param itemId
             */
            removeItem: function (itemId) {
                this.updateItem(itemId);
            },

            /**
             * Send request update item
             *
             * @param itemId
             * @param itemQty
             * @returns {*}
             */
            updateItem: function (itemId, itemQty) {
                var payload = {
                    item_id: itemId
                };

                if (typeof itemQty !== 'undefined') {
                    payload['item_qty'] = itemQty;
                }

                updateItemAction(payload);

                return this;
            }
        });
    }
);
