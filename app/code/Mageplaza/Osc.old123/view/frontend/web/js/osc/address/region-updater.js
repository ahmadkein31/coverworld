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
    RegionUpdater = Class.create();
    RegionUpdater.prototype = {
        initialize: function (countryEl, regionTextEl, regionSelectEl, regions, disableAction, clearRegionValueOnDisable) {
            this.isRegionRequired = true;
            this.countryEl = $(countryEl);
            this.regionTextEl = $(regionTextEl);
            this.regionSelectEl = $(regionSelectEl);
            this.config = regions['config'];
            delete regions.config;
            this.regions = regions;
            this.disableAction = (typeof disableAction == 'undefined') ? 'hide' : disableAction;
            this.clearRegionValueOnDisable = (typeof clearRegionValueOnDisable == 'undefined') ? false : clearRegionValueOnDisable;

            if (this.regionSelectEl.options.length <= 1) {
                this.update();
            }
            else {
                this.lastCountryId = this.countryEl.value;
            }

            this.countryEl.changeUpdater = this.update.bind(this);

            Event.observe(this.countryEl, 'change', this.update.bind(this));
        },

        _checkRegionRequired: function () {
            if (!this.isRegionRequired) {
                return;
            }

            var label, wildCard;
            var elements = [this.regionTextEl, this.regionSelectEl];
            var that = this;
            if (typeof this.config == 'undefined') {
                return;
            }
            var regionRequired = this.config.regions_required.indexOf(this.countryEl.value) >= 0;

            elements.each(function (currentElement) {
                if (!currentElement) {
                    return;
                }
                var form = currentElement.form,
                    validationInstance = form ? jQuery(form).data('validation') : null,
                    field = currentElement.up('.field') || new Element('div');

                if (validationInstance) {
                    validationInstance.clearError(currentElement);
                }
                label = $$('label[for="' + currentElement.id + '"]')[0];
                if (label) {
                    wildCard = label.down('em') || label.down('span.required');
                    var topElement = label.up('tr') || label.up('li');
                    if (!that.config.show_all_regions && topElement) {
                        if (regionRequired) {
                            topElement.show();
                        } else {
                            topElement.hide();
                        }
                    }
                }

                if (label && wildCard) {
                    if (!regionRequired) {
                        wildCard.hide();
                    } else {
                        wildCard.show();
                    }
                }

                //compute the need for the required fields
                if (!regionRequired || !currentElement.visible()) {
                    if (field.hasClassName('required')) {
                        field.removeClassName('required');
                    }
                    if (currentElement.hasClassName('required-entry')) {
                        currentElement.removeClassName('required-entry');
                    }
                    if ('select' == currentElement.tagName.toLowerCase() &&
                        currentElement.hasClassName('validate-select')
                    ) {
                        currentElement.removeClassName('validate-select');
                    }
                } else {
                    if (!field.hasClassName('required')) {
                        field.addClassName('required');
                    }
                    if (!currentElement.hasClassName('required-entry')) {
                        currentElement.addClassName('required-entry');
                    }
                    if ('select' == currentElement.tagName.toLowerCase() && !currentElement.hasClassName('validate-select')
                    ) {
                        currentElement.addClassName('validate-select');
                    }
                }
            });
        },

        disableRegionValidation: function () {
            this.isRegionRequired = false;
        },

        update: function () {
            if (this.regions[this.countryEl.value]) {

                if (this.lastCountryId != this.countryEl.value) {
                    var i, option, region, def;

                    def = this.regionSelectEl.getAttribute('defaultValue');
                    if (this.regionTextEl) {
                        if (!def) {
                            def = this.regionTextEl.value.toLowerCase();
                        }
                        this.regionTextEl.value = '';
                    }

                    this.regionSelectEl.options.length = 1;
                    for (regionId in this.regions[this.countryEl.value]) {
                        region = this.regions[this.countryEl.value][regionId];

                        option = document.createElement('OPTION');
                        option.value = regionId;
                        option.text = region.name.stripTags();
                        option.title = region.name;

                        if (this.regionSelectEl.options.add) {
                            this.regionSelectEl.options.add(option);
                        } else {
                            this.regionSelectEl.appendChild(option);
                        }

                        if (regionId == def || region.name.toLowerCase() == def || region.code.toLowerCase() == def) {
                            this.regionSelectEl.value = regionId;
                        }
                    }
                }

                if (this.disableAction == 'hide') {
                    if (this.regionTextEl) {
                        this.regionTextEl.style.display = 'none';
                        this.regionTextEl.style.disabled = true;
                    }
                    this.regionSelectEl.style.display = '';
                    this.regionSelectEl.disabled = false;
                } else if (this.disableAction == 'disable') {
                    if (this.regionTextEl) {
                        this.regionTextEl.disabled = true;
                    }
                    this.regionSelectEl.disabled = false;
                }
                this.setMarkDisplay(this.regionTextEl, false);
                this.setMarkDisplay(this.regionSelectEl, true);

                this.lastCountryId = this.countryEl.value;
            } else {
                if (this.disableAction == 'hide') {
                    if (this.regionTextEl) {
                        this.regionTextEl.style.display = '';
                        this.regionTextEl.style.disabled = false;
                    }
                    this.regionSelectEl.style.display = 'none';
                    this.regionSelectEl.disabled = true;
                } else if (this.disableAction == 'disable') {
                    if (this.regionTextEl) {
                        this.regionTextEl.disabled = false;
                    }
                    this.regionSelectEl.disabled = true;
                    if (this.clearRegionValueOnDisable) {
                        this.regionSelectEl.value = '';
                    }
                } else if (this.disableAction == 'nullify') {
                    this.regionSelectEl.options.length = 1;
                    this.regionSelectEl.value = '';
                    this.regionSelectEl.selectedIndex = 0;
                    this.lastCountryId = '';
                }
                this.setMarkDisplay(this.regionSelectEl, false);
                this.setMarkDisplay(this.regionTextEl, true);
            }
            this._checkRegionRequired();
        },

        setMarkDisplay: function (elem, display) {
            var label = $$('label[for="' + elem.id + '"]')[0];
            if (label) {
                display ? label.show() : label.hide();
            }
        }
    };
    return RegionUpdater;
});


