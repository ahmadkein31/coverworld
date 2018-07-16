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
define(["mageplaza/osc/popup", "prototype"], function (MageplazaOscPopup) {
    /* Term and Condition Class*/
    MageplazaOscReviewTerms = Class.create();
    MageplazaOscReviewTerms.prototype = {
        initialize: function (config) {
            this.termContainer = $$(config.termContainer).first();
            this.termItemElements = $$(config.termItemElements);
            this.linkFromItem = config.linkFromItem;
            this.checkboxFromItem = config.checkboxFromItem;
            this.checkedFromItem = config.checkedFromItem;
            this.acceptTermItem = config.acceptTermItem;
            this.descriptionContainerFromItem = config.descriptionContainerFromItem;
            this.isRequiredReadTerm = config.isRequiredReadTerm;
            this.readTermMessage = config.readTermMessage;
            this.errorClass = config.errorClass;
            this.popup = {};
            this.initObserver();
        },
        initObserver: function () {
            this.termItemElements.each(function (item) {
                var link = item.select(this.linkFromItem).first();
                var checkbox = item.select(this.checkboxFromItem).first();
                var isChecked = item.select(this.checkedFromItem).first();
                var accept = item.select(this.acceptTermItem).first();
                var description = item.select(this.descriptionContainerFromItem).first();
                var notExistTerm = !link || !description;
                if (notExistTerm) {
                    return;
                }
                var linkId = link.id;
                if (checkbox) {
                    if (isChecked.value == 1) {
                        checkbox.checked = true;
                    }
                    else {
                        checkbox.checked = false;
                    }
                    checkbox.observe('click', function (evt) {
                        this._removeAllMessage();
                        if (this.isRequiredReadTerm && isChecked.value == 0) {
                            evt.target.checked = false;
                            MageplazaOsc.showMessage(this.termContainer, this.readTermMessage, this.errorClass);
                        }
                        if (evt.target.checked) {
                            isChecked.value = 1;
                        }
                        else {
                            isChecked.value = 0;
                        }
                        this.saveFormData();
                    }.bind(this));
                }
                this.popup[linkId] = new MageplazaOscPopup({
                    selector: item
                });
                if (accept) {
                    accept.observe('click', function () {
                        checkbox.checked = true;
                        isChecked.value = 1;
                        this._removeAllMessage();
                        this.popup[linkId].hidePopup();
                        this.saveFormData();
                    }.bind(this));
                }
            }.bind(this));
        },
        _removeAllMessage: function () {
            this.termContainer.select('.validation-advice').each(function (adviceEl) {
                adviceEl.remove();
            });
            MageplazaOsc.removeMessage(this.termContainer, this.errorClass);
        },
        saveFormData: function () {
            MageplazaOsc.saveFormData(this.termContainer);
        }
    };
    return MageplazaOscReviewTerms;
});


