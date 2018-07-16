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

    /* Secure Checkout Authentication Class*/
    var MageplazaOscAuthentication = Class.create();
    MageplazaOscAuthentication.prototype = {
        initialize: function (config) {
            this.popupContainer = $(config.popupContainer);
            this.loginContainer = $(config.loginContainer);
            this.forgotContainer = $(config.forgotContainer);
            this.forgotLink = $(config.forgotLink);
            this.backLink = $(config.backLink);
            this.loginUrl = config.loginUrl;
            this.forgotUrl = config.forgotUrl;
            this.loginSubmitBtn = $(config.loginSubmitBtn);
            this.forgotSubmitBtn = $(config.forgotSubmitBtn);
            this.loadingClass = config.loadingClass;
            this.errorClass = config.errorClass;
            this.successClass = config.successClass;
            this.mode = 'form_login';
            this.initObserver();

        },
        initObserver: function () {
            this.forgotLink.observe('click', function () {
                this.loginContainer.hide();
                this.forgotContainer.show();
                this.mode = 'form_forgot';

            }.bind(this));
            this.backLink.observe('click', function () {
                this.forgotContainer.hide();
                this.loginContainer.show();
                this.mode = 'form_login';

            }.bind(this));
            this.loginSubmitBtn.observe('click', function () {
                this._processLogin();

            }.bind(this));
            this.forgotSubmitBtn.observe('click', function () {
                this._processForgotPw();

            }.bind(this));
            document.observe('keypress', this.keypressHandler.bind(this));
        },
        keypressHandler: function (e) {
            if (!MageplazaOsc.loginPopup)
                return false;
            var code = e.keyCode || e.which;
            if (code == 13) {
                if (this.mode == 'form_login') {
                    this._processLogin();
                } else if (this.mode == 'form_forgot') {
                    this._processForgotPw();
                }
                return;
            }
        },
        _processLogin: function () {
            var self = this;
            var loginForm = this.loginContainer.down('form');
            var successClass = this.successClass;
            var errorClass = this.errorClass;
            loginForm.validator = new Validation(loginForm);
            loginForm.select('.validation-advice').each(function (adviceEl) {
                adviceEl.remove();
            });
            if (loginForm.validator.validate()) {
                this._removeAllMessage();
                MageplazaOsc.appendLoading(loginForm, this.loadingClass);
                var requestOption = {
                    parameters: loginForm.serialize(true),
                    onComplete: function (transport) {
                        try {
                            var response = transport.responseText.evalJSON();
                            if (response.success === true) {
                                MageplazaOsc.showMessage(loginForm, response.message, successClass);
                                document.location.reload();
                            }
                            else if (response.error) {
                                MageplazaOsc.removeLoading(loginForm, self.loadingClass);
                                MageplazaOsc.showMessage(loginForm, response.message, errorClass);
                            }
                        } catch (e) {
                        }
                    }
                };
                MageplazaOsc.Request(this.loginUrl, requestOption);
            }
        },
        _processForgotPw: function () {
            var self = this;
            var forgotForm = this.forgotContainer.down('form');
            var successClass = this.successClass;
            var errorClass = this.errorClass;
            forgotForm.validator = new Validation(forgotForm);
            forgotForm.select('.validation-advice').each(function (adviceEl) {
                adviceEl.remove();
            });
            if (forgotForm.validator.validate()) {
                this._removeAllMessage();
                MageplazaOsc.appendLoading(forgotForm, this.loadingClass);
                var requestOption = {
                    parameters: forgotForm.serialize(true),
                    onComplete: function (transport) {
                        try {
                            var response = transport.responseText.evalJSON();
                            if (response.success === true) {
                                MageplazaOsc.showMessage(forgotForm, response.message, successClass);
                            }
                            else if (response.error) {
                                MageplazaOsc.showMessage(forgotForm, response.message, errorClass);
                            }
                            MageplazaOsc.removeLoading(forgotForm, self.loadingClass);
                        } catch (e) {
                        }
                    }
                };
                MageplazaOsc.Request(this.forgotUrl, requestOption);
            }
        },
        _removeAllMessage: function () {
            MageplazaOsc.removeMessage(this.loginContainer, this.errorClass);
            MageplazaOsc.removeMessage(this.loginContainer, this.successClass);
            MageplazaOsc.removeMessage(this.forgotContainer, this.errorClass);
            MageplazaOsc.removeMessage(this.forgotContainer, this.successClass);
        }

    };
    return MageplazaOscAuthentication;
});


