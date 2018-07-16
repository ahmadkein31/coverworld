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
        'ko',
        'uiComponent',
        'Mageplaza_Osc/js/model/osc-data',
        'jquery/ui',
        'jquery/jquery-ui-timepicker-addon'
    ],
    function ($, ko, Component, oscData) {
        'use strict';
        var cacheKey = 'deliveryTime';

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/container/delivery-time'
            },
            deliveryTimeValue: ko.observable(),
            initialize: function () {
                this._super();
                ko.bindingHandlers.datepicker = {
                    init: function (element) {
                        var dateFormat = window.checkoutConfig.oscConfig.deliveryTimeOptions.deliveryTimeFormat,
                            daysOff = window.checkoutConfig.oscConfig.deliveryTimeOptions.deliveryTimeOff,
                            options = {
                                minDate: 0,
                                showButtonPanel: false,
                                dateFormat: dateFormat,
                                showOn: 'both',
                                buttonText: '',
                                beforeShowDay: function (date) {
                                    if (!daysOff)
                                        return [true];

                                    return [daysOff.indexOf(date.getDay()) === -1];
                                }
                            };
                        $(element).datetimepicker(options);
                    }
                };
                this.deliveryTimeValue(oscData.getData(cacheKey));
                this.deliveryTimeValue.subscribe(function (newValue) {
                    oscData.setData(cacheKey, newValue);
                });
                return this;
            }
        });
    }
);
