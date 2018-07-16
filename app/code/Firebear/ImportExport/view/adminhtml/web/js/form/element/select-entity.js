/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
    'jquery',
    'underscore',
    'Magento_Ui/js/form/element/select',
    'Firebear_ImportExport/js/form/element/general',
    'uiRegistry'
    ],
    function ($, _, Acstract, general, reg) {
        'use strict';

        return Acstract.extend(general).extend(
            {
                defaults: {
                    sourceExt: null,
                    sourceOptions: null,
                    imports: {
                        changeSource: '${$.ns}.${$.ns}.settings.entity:value',
                        addDependency: '${$.ns}.${$.ns}.behavior.behavior_field_order:value'
                    }
                },
                initialize: function () {
                    this._super();
                    return this;
                },
                initConfig: function (config) {
                    this._super();
                    this.sourceOptions = $.parseJSON(this.sourceOptions);

                    return this;
                },
                changeSource: function (value) {
                    this.sourceExt = value;
                    if (value in this.sourceOptions) {
                        this.setOptions(this.sourceOptions[value]);
                    }
                },
                addDependency: function (dep) {
                    if (_.size(dep) > 0) {
                        var valueEl = this.value();
                        var select = reg.get(this.parentName + '.source_data_system');
                        if (select != undefined) {
                            var oldValue = select.value();
                        }
                        var options = this.sourceOptions[this.sourceExt];
                        var newOptions = [];
                        _.each(
                            options,
                            function (value, key) {
                                if (value.dep != undefined) {
                                    if (_.indexOf(dep, value.dep) != -1) {
                                        newOptions.push(value);
                                    }
                                }
                            }
                        );
                        this.setOptions(newOptions);
                        this.value(valueEl);
                        if (select != undefined) {
                            select.value(oldValue);
                        }
                    }
                }
            }
        )
    }
);
