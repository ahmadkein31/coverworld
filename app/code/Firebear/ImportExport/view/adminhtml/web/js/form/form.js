/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
        'jquery',
        'Magento_Ui/js/form/form',
        './adapter',
        'uiRegistry',
        'Magento_Ui/js/lib/spinner',
        'rjsResolver'
    ],
    function ($, Form, adapter, reg, loader, resolver) {
        'use strict';

        /**
         * Collect form data.
         *
         * @param {Array} items
         * @returns {Object}
         */
        function collectData(items) {
            var result = {};

            items = Array.prototype.slice.call(items);

            items.forEach(function (item) {
                switch (item.type) {
                    case 'checkbox':
                        result[item.name] = +!!item.checked;
                        break;

                    case 'radio':
                        if (item.checked) {
                            result[item.name] = item.value;
                        }
                        break;

                    default:
                        result[item.name] = item.value;
                }
            });

            return result;
        }

        return Form.extend(
            {
                defaults: {
                  nameModal:'',
                },
                initialize: function () {
                    this._super();
                    resolver(this.checkButton, this);

                    return this;
                },
                initAdapter: function () {
                    adapter.on({
                        'reset': this.reset.bind(this),
                        'save': this.save.bind(this, true, {}),
                        'saveAndContinue': this.save.bind(this, false, {}),
                        'saveAndRun': this.saveAndRun.bind(this, {})
                    }, this.selectorPrefix, this.eventPrefix);

                    return this;
                },

                destroyAdapter: function () {
                    adapter.off([
                        'reset',
                        'save',
                        'saveAndContinue',
                        'saveAndRun'
                    ], this.eventPrefix);

                    return this;
                },
                saveAndRun: function (data) {
                    this.validate();

                    if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                        this.setAdditionalData(data)
                            .extSubmit(true);
                    }
                },
                extSubmit: function (redirect) {
                    localStorage.removeItem('jobId');
                    loader.get(this.name).show();
                    var self = this;
                    var additional = collectData(this.additionalFields),
                        source = this.source;
                    _.each(additional, function (value, name) {
                        source.set('data.' + name, value);
                    });
                    $.ajax({
                        type: "POST",
                        url: this.source.submit_url,
                        data: this.source.get('data'),
                        success: function (msg) {
                            loader.get(self.name).hide();
                            if (msg != false) {
                                localStorage.setItem('jobId', msg);
                                reg.set(self.namespace + "." + self.namespace + ".general.entity:value", msg);
                                reg.get(self.namespace + "." + self.namespace + "." + self.nameModal).toggleModal();
                            }
                        }
                    });
                },
                checkButton: function() {
                    reg.get('import_job_form.import_job_form.general.title', function (name) {
                        if (name.value()) {
                            reg.get('import_job_form.import_job_form.source.check_button', function (object) {
                                object.validateGeneral();
                            });
                        }
                    });
                }
            }
        );
    }
);