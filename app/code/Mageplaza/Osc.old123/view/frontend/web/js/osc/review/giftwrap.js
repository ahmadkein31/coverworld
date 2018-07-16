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
    /* Gift Wrap Class */
    MageplazaOscReviewGiftwrap = Class.create();
    MageplazaOscReviewGiftwrap.prototype = {
        initialize: function (config) {
            this.useGiftWrapCheckbox = $$(config.useGiftWrapCheckbox).first();
            this.addGiftWrapUrl = config.addGiftWrapUrl;
            this.initObserver();
        },
        initObserver: function () {
            if (this.useGiftWrapCheckbox) {
                this.useGiftWrapCheckbox.observe('change', this.applyGiftWrap.bind(this))
            }
        },
        applyGiftWrap: function () {
            var requestOptions = {
                method: 'post',
                parameters: {
                    is_used_giftwrap: this.useGiftWrapCheckbox.getValue()
                }
            };
            MageplazaOsc.Request(this.addGiftWrapUrl, requestOptions);
        }
    };
    return MageplazaOscReviewGiftwrap;
});


