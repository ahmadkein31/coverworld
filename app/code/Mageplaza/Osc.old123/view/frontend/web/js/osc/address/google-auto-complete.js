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
    //*Auto detect address by stress Google Map Api*/
    var MageplazaOscAddressDetect = Class.create();
    MageplazaOscAddressDetect.prototype = {
        initialize: function (config) {
            // init dom elements
            this.inputSelector = $(config.inputSelector);
            this.componentForm = config.componentForm;
            this.addressType = config.addressType;
            this.addressElementsIds = config.addressElementsIds;
            this.regionUpdater = config.regionUpdater;
            this.secureCheckoutAddress = config.secureCheckoutAddress;
            this.geolocation = $(config.geolocation);
            this.specificCountry = config.specificCountry;
            // init observer
            this.initGoogleAutoComplete();
        },
        initGoogleAutoComplete: function () {
            var me = this;
            if (this.inputSelector) {
                var options = {
                    types: ['geocode']
                };
                if (this.specificCountry) {
                    var restrictions = {
                        componentRestrictions: {country: this.specificCountry}
                    }
                    options = Object.extend(options, restrictions);
                }
                //Google Auto Complete
                this.googleAutoComplete = new google.maps.places.Autocomplete(this.inputSelector, options);
                google.maps.event.addListener(this.googleAutoComplete, 'place_changed', function () {
                    me.googleResponse(me.googleAutoComplete.getPlace());
                });
            }
            // Geolocation
            if (this.geolocation) {
                this.geolocation.observe('click', function () {
                    this.geoLocate();
                }.bind(this));
            }
        },
        googleResponse: function (place) {
            var street, city, region_id, region, country, postal_code;

            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (this.componentForm[addressType]) {
                    if (addressType == 'street_number') {
                        if (street && place.address_components[i][this.componentForm['street_number']])
                            street += ', ' + place.address_components[i][this.componentForm['street_number']];
                        else
                            street = place.address_components[i][this.componentForm['street_number']];
                    }
                    if (addressType == 'route') {
                        if (street && place.address_components[i][this.componentForm['route']])
                            street += ' ' + place.address_components[i][this.componentForm['route']];
                        else
                            street = place.address_components[i][this.componentForm['route']];
                    }
                    if (addressType == 'neighborhood') {
                        if (street && place.address_components[i][this.componentForm['neighborhood']])
                            street += ', ' + place.address_components[i][this.componentForm['neighborhood']];
                        else
                            street = place.address_components[i][this.componentForm['neighborhood']];
                    }
                    if (addressType == 'administrative_area_level_2') {
                        if (street && place.address_components[i][this.componentForm['administrative_area_level_2']])
                            street += ', ' + place.address_components[i][this.componentForm['administrative_area_level_2']];
                        else
                            street = place.address_components[i][this.componentForm['administrative_area_level_2']];
                    }
                    if (addressType == 'locality')
                        city = place.address_components[i][this.componentForm['locality']];
                    if (addressType == 'administrative_area_level_1') {
                        region_id = place.address_components[i]['short_name'];
                        region = place.address_components[i]['long_name'];
                    }
                    if (addressType == 'country')
                        country = place.address_components[i][this.componentForm['country']];
                    if (addressType == 'postal_code')
                        postal_code = place.address_components[i][this.componentForm['postal_code']];
                }
            }
            this.responseComponents = {
                street1: street,
                city: city,
                region_id: region_id,
                region: region,
                country_id: country,
                postcode: postal_code,
            };
            this.fillAddressById();
        },
        fillAddressById: function () {
            var me = this;
            var billingAddress = this.secureCheckoutAddress.billingAddress;
            var shippingAddress = this.secureCheckoutAddress.shippingAddress;
            this.addressElementsIds.each(function (elementId) {
                var element = $(me.addressType + ":" + elementId);
                if (element && me.responseComponents[elementId]) {
                    element.value = me.responseComponents[elementId];
                }
            });
            if (this.regionUpdater)
                this.regionUpdater.update();
            if (this.addressType == 'billing') {
                this.secureCheckoutAddress.triggerElementChanged(billingAddress);
            }
            else {
                this.secureCheckoutAddress.triggerElementChanged(shippingAddress);
            }
        },
        geoLocate: function () {
            if (navigator.geolocation) {
                var me = this;
                navigator.geolocation.getCurrentPosition(function (position) {
                    me.getAddressBytLatitude(position.coords.latitude, position.coords.longitude);
                });
            }
        },
        getAddressBytLatitude: function (myLatitude, myLongitude) {
            var me = this;
            var geocoder = new google.maps.Geocoder();
            var location = new google.maps.LatLng(myLatitude, myLongitude);
            geocoder.geocode({'latLng': location}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    me.googleResponse(results[0]);
                } else {
                    return false;
                }
            });
        }
    };
    return MageplazaOscAddressDetect;
});


