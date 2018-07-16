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
define([
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/model/address-converter',
    'prototype'
], function
    (quote,
     customer,
     selectBillingAddress,
     addressConverter) {
    var MageplazaOscAddress = Class.create();
    MageplazaOscAddress.prototype = {
        initialize: function (config) {
            this.addressContainer = $$(config.addressContainer).first();
            this.saveAddressUrl = config.saveAddressUrl;
            this.billingAddress = {};
            this.shippingAddress = {};
            this.billingData = {};
            this.shippingData = {};
            this.setBillingAddress(config.billingAddress);
            this.setShippingAddress(config.shippingAddress);
            this.billingAddress.countryRegionData = {};
            this.shippingAddress.countryRegionData = {};
            this.initAddress();
            this.initObserver();
        },
        setBillingAddress: function (config) {
            this.billingAddress.container = $$(config.container).first();
            this.billingAddress.emailAddress = $$(config.emailAddress).first();
            this.billingAddress.addressSelect = $$(config.addressSelect).first();
            this.billingAddress.newAddressContainer = $$(config.newAddressContainer).first();
            this.billingAddress.createAccountCheckbox = $$(config.createAccountCheckbox).first();
            this.billingAddress.passwordContainer = $$(config.passwordContainer).first();
            this.billingAddress.useSameAddressCheckbox = $$(config.useSameAddressCheckbox).first();
            this.billingAddress.triggerElements = config.triggerElements;
            this.billingAddress.countryRegionElements = config.countryRegionElements;
            this.billingAddress.countrySelect = $(this.billingAddress.countryRegionElements.countrySelect);
            this.billingAddress.regionSelect = $(this.billingAddress.countryRegionElements.regionSelect);
            this.billingAddress.regionInput = $(this.billingAddress.countryRegionElements.regionInput);
        },
        setShippingAddress: function (config) {
            this.shippingAddress.container = $$(config.container).first();
            this.shippingAddress.addressSelect = $$(config.addressSelect).first();
            this.shippingAddress.newAddressContainer = $$(config.newAddressContainer).first();
            this.shippingAddress.triggerElements = config.triggerElements;
            this.shippingAddress.countryRegionElements = config.countryRegionElements;
            this.shippingAddress.countrySelect = $(this.billingAddress.countryRegionElements.countrySelect);
            this.shippingAddress.regionSelect = $(this.billingAddress.countryRegionElements.regionSelect);
            this.shippingAddress.regionInput = $(this.billingAddress.countryRegionElements.regionInput);
        },
        initAddress: function () {
            this._sameAddressProcess(this.billingAddress.useSameAddressCheckbox);
            this._processAddressSelect(this.billingAddress);
            this._processAddressSelect(this.shippingAddress);
            this.applyBilling();
        },
        initObserver: function () {
            //Billing
            var useSameAddressCheckbox = this.billingAddress.useSameAddressCheckbox;
            var emailAddress = this.billingAddress.emailAddress;
            var createAccountCheckbox = this.billingAddress.createAccountCheckbox;
            var billingCountrySelect = this.billingAddress.countrySelect;
            var billingRegionSelect = this.billingAddress.regionSelect;
            var billingRegionInput = this.billingAddress.regionInput;
            var billingTriggerElements = this.billingAddress.triggerElements;
            var billingAddressSelect = this.billingAddress.addressSelect;
            if (emailAddress) {
                emailAddress.observe('change', function () {
                    if (!customer.isLoggedIn()) {
                        quote.guestEmail = emailAddress.getValue();
                    }
                }.bind(this))
            }
            if (useSameAddressCheckbox) {
                useSameAddressCheckbox.observe('change', function (evt) {
                    this._sameAddressProcess(evt);
                    this.changeAddressFinish();
                    this._processAddressSelect(this.shippingAddress);
                }.bind(this))
            }
            if (createAccountCheckbox) {
                createAccountCheckbox.observe('change', function (evt) {
                    this._createAccountProcess(evt);
                }.bind(this))
            }
            if (billingCountrySelect) {
                billingCountrySelect.observe('change', function () {
                    this.countrySelectChanged(this.billingAddress);
                }.bind(this))
            }
            if (billingRegionSelect) {
                billingRegionSelect.observe('change', function () {
                    this.regionSelectChanged(this.billingAddress);
                }.bind(this))
            }
            if (billingRegionInput) {
                billingRegionInput.observe('change', function () {
                    this.regionInputChanged(this.billingAddress);
                }.bind(this))
            }
            if (billingTriggerElements) {
                billingTriggerElements.each(function (id) {
                    var element = $(id);
                    if (element) {
                        element.observe('change', function () {
                            this.triggerElementChanged(this.billingAddress);
                        }.bind(this))
                    }

                }.bind(this));
            }
            if (billingAddressSelect) {
                billingAddressSelect.observe('change', function () {
                    this._processAddressSelect(this.billingAddress);
                    this.changeAddressFinish();

                }.bind(this))
            }
            //Shipping
            var shippingCountrySelect = this.shippingAddress.countrySelect;
            var shippingRegionSelect = this.shippingAddress.regionSelect;
            var shippingRegionInput = this.shippingAddress.regionInput;
            var shippingTriggerElements = this.shippingAddress.triggerElements;
            var shippingAddressSelect = this.shippingAddress.addressSelect;
            if (shippingCountrySelect) {
                shippingCountrySelect.observe('change', function () {
                    this.countrySelectChanged(this.shippingAddress);
                }.bind(this))
            }
            if (shippingRegionSelect) {
                shippingRegionSelect.observe('change', function () {
                    this.regionSelectChanged(this.shippingAddress);
                }.bind(this))
            }
            if (shippingRegionInput) {
                shippingRegionInput.observe('change', function () {
                    this.regionInputChanged(this.shippingAddress);
                }.bind(this))
            }
            if (shippingTriggerElements) {
                shippingTriggerElements.each(function (id) {
                    var element = $(id);
                    if (element) {
                        element.observe('change', function () {
                            this.triggerElementChanged(this.shippingAddress);
                        }.bind(this))
                    }

                }.bind(this));
            }
            if (shippingAddressSelect) {
                shippingAddressSelect.observe('change', function () {
                    this._processAddressSelect(this.shippingAddress);
                    this.changeAddressFinish();

                }.bind(this))
            }
            //Save Form Data Session
            Form.getElements(this.addressContainer).each(function (el) {
                var elId = el.id;
                if (el === this.billingAddress.useSameAddressCheckbox
                    || this.billingAddress.triggerElements.indexOf(elId) !== -1
                ) {
                    return false;
                }
                else if (this.shippingAddress.triggerElements && this.shippingAddress.triggerElements.indexOf(elId) !== -1) {
                    return false;
                }
                else {
                    el.observe('change', function () {
                        this.saveFormData();
                    }.bind(this));
                }

            }.bind(this))

        },
        saveAddressData: function (el) {
            var elId = el.id.split(':');
            var type = elId[0];
            var fieldId = elId[1];
            if (type == 'shipping') {
                this.shippingData[fieldId] = el.getValue();
            }
            else {
                this.billingData[fieldId] = el.getValue();
            }

        },
        applyBilling: function () {
            if (this.billingAddress.addressSelect && this.billingAddress.addressSelect.getValue()) {

                selectBillingAddress(addressConverter.formAddressDataToQuoteAddress(this.getAddressDataFromQuote()));
            }
            else {
                selectBillingAddress(addressConverter.formAddressDataToQuoteAddress(this.billingData));
            }
        },
        getAddressDataFromQuote: function () {
            var fields = ['firstname', 'lastname', 'middlename', 'street', 'city', 'country_id', 'region', 'region_id', 'postcode', 'telephone', 'company'];
            var addressObj = {};
            var addressData = [];
            window.checkoutConfig.customerData.addresses.each(function (address) {
                if (this.billingAddress.addressSelect.getValue() == address.id) {
                    addressObj = address;
                    return;
                }
            }.bind(this));
            for (var key in addressObj) {
                if (fields.indexOf(key) == -1) {
                    continue;
                }
                if (key == 'region') {
                    if(addressObj[key].region){
                        addressData[key] = addressObj[key].region;
                    }
                }
                else {
                    addressData[key] = addressObj[key];
                }
            }
            return addressData;
        },
        _sameAddressProcess: function (evt) {
            if (typeof(evt) === 'object') {
                var isChecked = false;
                if (evt.target)
                    isChecked = evt.target.checked;
                else isChecked = evt.checked;
                if (isChecked) {
                    this.hideShippingAddress();
                }
                else {
                    this.showShippingAddress();
                }
            }
        },
        _createAccountProcess: function (evt) {
            if (typeof(evt) === 'object') {
                var isChecked = false;
                if (evt.target)
                    isChecked = evt.target.checked;
                else isChecked = evt.checked;
                if (isChecked) {
                    this.showPasswordContainer();
                }
                else {
                    this.hidePasswordContainer();
                }
            }
        },
        showPasswordContainer: function () {
            var passwordContainer = this.billingAddress.passwordContainer;
            passwordContainer.setStyle({
                display: ''
            });
            var newHeight = MageplazaOsc.getHeightFromElement(passwordContainer);
            MageplazaOsc.applyMorphEffect(passwordContainer, newHeight, 0.3, function () {
                passwordContainer.setStyle({'height': ''});
            });
        },
        hidePasswordContainer: function () {
            var passwordContainer = this.billingAddress.passwordContainer;
            MageplazaOsc.applyMorphEffect(passwordContainer, 0, 0.5, function () {
                passwordContainer.setStyle({'display': 'none'});
            });
        },
        changeAddressFinish: function () {
            var params = {};
            var billing = '';
            var shipping = '';
            /*Save quote billing & shipping Address*/
            Form.getElements(this.addressContainer).each(function (el) {
                this.saveAddressData(el);
            }.bind(this));
            this.applyBilling();
            if (!customer.isLoggedIn()) {
                quote.guestEmail = this.billingAddress.emailAddress.getValue();
            }
            if (this.billingAddress.container) {
                billing = Form.serialize(this.billingAddress.container, true);

            }
            if (this.shippingAddress.container) {
                shipping = Form.serialize(this.shippingAddress.container, true);
            }
            if (billing) {
                params = Object.extend(params, billing);
            }
            if (shipping) {
                params = Object.extend(params, shipping);
            }
            var requestOptions = {
                method: 'post',
                parameters: params
            };
            MageplazaOsc.Request(this.saveAddressUrl, requestOptions)

        },
        showShippingAddress: function () {
            var shippingContainer = this.shippingAddress.container;
            shippingContainer.setStyle({
                display: ''
            });
            var newHeight = MageplazaOsc.getHeightFromElement(shippingContainer);
            MageplazaOsc.applyMorphEffect(shippingContainer, newHeight, 0.3, function () {
                shippingContainer.setStyle({
                    height: ''
                })
                MageplazaOsc.updateNumbers();
            });

        },
        hideShippingAddress: function () {
            var shippingContainer = this.shippingAddress.container;
            MageplazaOsc.applyMorphEffect(shippingContainer, 0, 0.5, function () {
                shippingContainer.setStyle({
                    display: 'none'
                })
                MageplazaOsc.updateNumbers();
            });

        },
        countrySelectChanged: function (targetAddress) {
            var regionData = targetAddress.countryRegionData[targetAddress.countrySelect.getValue()];
            if (regionData) {
                if (regionData.type === 'regionSelect') {
                    targetAddress.regionSelect.setValue(regionData.value);
                }
                else {
                    targetAddress.regionInput.setValue(regionData.value);
                }
            }
        },
        regionSelectChanged: function (targetAddress) {
            targetAddress.countryRegionData[targetAddress.countrySelect.getValue()] = {
                'type': 'regionSelect',
                'value': targetAddress.regionSelect.getValue()
            }
        },
        regionInputChanged: function (targetAddress) {
            targetAddress.countryRegionData[targetAddress.countrySelect.getValue()] = {
                'type': 'regionInput',
                'value': targetAddress.regionInput.getValue()
            }
        },
        triggerElementChanged: function (targetAddress) {
            var self = this;
            var flag = targetAddress.triggerElements.all(function (elementId) {
                var element = $(elementId);
                if (element) {
                    return self.validateElement(element);
                }
            }, this);
            if (flag) {
                this.changeAddressFinish();
            }
        },
        validateElement: function (element) {
            var className = $w(element.className);
            var flag = true;
            className.all(function (name) {
                var v = Validation.get(name);
                try {
                    if (Validation.isVisible(element) && !v.test($F(element), element)) {
                        flag = false;
                    } else {
                        flag = true;
                    }
                } catch (e) {
                    flag = true;
                }
            });
            return flag;
        },
        _processAddressSelect: function (targetAddress) {
            if (targetAddress.addressSelect) {
                if (targetAddress.addressSelect.value) {
                    this.hideNewAddressContainer(targetAddress)
                }
                else {
                    this.showNewAddressContainer(targetAddress);
                }
            }
        },
        showNewAddressContainer: function (targetAddress) {
            var newAddressContainer = targetAddress.newAddressContainer;
            newAddressContainer.setStyle({
                display: ''
            });
            var newHeight = MageplazaOsc.getHeightFromElement(newAddressContainer);
            MageplazaOsc.applyMorphEffect(newAddressContainer, newHeight, 0.3, function () {
                targetAddress.container.setStyle({
                    height: ''
                })
            }.bind(this));
        },
        hideNewAddressContainer: function (targetAddress) {
            var newAddressContainer = targetAddress.newAddressContainer;
            MageplazaOsc.applyMorphEffect(newAddressContainer, 0, 0.5, function () {
                newAddressContainer.setStyle({
                    display: 'none'
                })
            }.bind(this));
        },
        saveFormData: function () {
            MageplazaOsc.saveFormData(this.addressContainer);
        }

    };
    return MageplazaOscAddress;
});


