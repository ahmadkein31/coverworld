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
define(["jquery", "mageplaza/osc/jquery/popup", "prototype"], function (jq) {
    /*Secure Checkout Popup*/
    var MageplazaOscPopup = Class.create();
    MageplazaOscPopup.prototype = {
        initialize: function (config) {
            this.selector = jq(config.selector);
            this.delegate = config.delegate ? config.delegate : 'a';
            this.initPopup();

        },
        initPopup: function () {
            this.selector.magnificPopup({
                delegate: this.delegate,
                removalDelay: 500, //delay removal by X to allow out-animation
                callbacks: {
                    beforeOpen: function () {
                        this.st.mainClass = this.st.el.attr('data-effect');
                        MageplazaOsc.loginPopup = true;
                    },
                    beforeClose: function () {
                        MageplazaOsc.loginPopup = false;
                    }
                },
                midClick: true // allow opening popup on middle mouse click. Always set it to true if you don't provide alternative source.
            }).instance;
        },
        hidePopup: function () {
            jq.magnificPopup.close();
        }

    };
    return MageplazaOscPopup;
});


