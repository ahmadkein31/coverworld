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
    MageplazaOscReviewNewsletter = Class.create();
    MageplazaOscReviewNewsletter.prototype = {
        initialize: function (config) {
            this.newsletterCheckbox = $$(config.newsletterCheckbox).first();
            this.saveFormUrl = config.saveFormUrl;
            this.initObserver();
        },
        initObserver: function () {
            this.newsletterCheckbox.observe('change', function (evt) {
                var el = evt.target;
                var requestOptions = {
                    method: 'post',
                    parameters: {
                        is_subscribed: el.checked ? '1' : '0'
                    }
                }
                new Ajax.Request(this.saveFormUrl, requestOptions);
            }.bind(this));
        }
    }
    return MageplazaOscReviewNewsletter;
});


