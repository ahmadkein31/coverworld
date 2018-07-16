define([
    "jquery",
    "jquery/ui"
], function (jQuery) {
    "use strict";
    jQuery.widget('magearray.categoryfilter', {
        selects: [],

        _create: function () {
            this.selects = jQuery('#cd-' + this.options.nameInLayout).find('select');
            this.selects.on('change', this, this._onChange);
        },

        _onChange: function (event) {
            var select = this;
            var selectedValue = select.value;

            var redirectUrl = jQuery('option:selected', this).attr('dataUrl');
            var self = event.data;
            var dataId = jQuery(this).attr('dataId');
            var levels = self.options.levels;
            var labelembedded = self.options.labelembedded;
            var nameInLayout = self.options.nameInLayout;
            var nextDropdown = parseInt(dataId) + 1;
            self._clearDropDowns(levels, dataId);
            self._loadNextDropDown(event, selectedValue, labelembedded, nameInLayout, redirectUrl, dataId, levels);
        },

        _loadNextDropDown: function (event, selectedValue, labelembedded, nameInLayout, redirectUrl, dataId, levels) {

            var self = event.data;
            var url = self.options.url;
            var redirectUrl = redirectUrl;
            var category_id = self.options.category_id;
            var nextDropdown = parseInt(dataId) + 1;
            var selectedValue = selectedValue;

            if (dataId == levels) {
                if (selectedValue != "") {
                    window.location.href = redirectUrl;
                }
            } else {
                if (selectedValue != "") {
                    jQuery.ajax({
                        url: url,
                        type: 'get',
                        data: {selectedValue: selectedValue, dataId: dataId, category_id: category_id},
                        dataType: 'json',
                        showLoader: true,
                        success: function (data) {
                            if (data.length) {
                                var optionStr = "";
                                for (var i = 0; i < data.length; i++) {
                                    if (i == 0) {
                                        var labelText = jQuery("#" + nameInLayout + nextDropdown + " option:first").text();
                                        optionStr = "<option value=''>Select " + labelText + "</option>";                 

                                    }
                                    
                                    if (data[i]['id'] == "NA") {
                                        optionStr = optionStr + '<option value="' + data[i]['id'] + '" dataUrl="' + data[i]['url'] + '" selected>' + data[i]['name'] + '</option>';
                                    } else {
                                        optionStr = optionStr + '<option value="' + data[i]['id'] + '" dataUrl="' + data[i]['url'] + '">' + data[i]['name'] + '</option>';
                                    }
                                }
                                jQuery('#' + nameInLayout + nextDropdown).addClass('dropdown-selected');
                                jQuery('#' + nameInLayout + nextDropdown).empty();
                                jQuery('#' + nameInLayout + nextDropdown).append(optionStr);
                                if (dataId != levels) {

                                    var e = document.getElementById(nameInLayout + nextDropdown);
                                    var na_selected_value = e.options[e.selectedIndex].value;

                                    if (na_selected_value == "NA") {

                                        dataId = parseInt(dataId) + 1;

                                        self._loadNextDropDown(event, e.value, finderId, dataId, levels, url);
                                    }
                                }

                            } else {
                                jQuery('#' + nameInLayout + nextDropdown).empty();
                                jQuery('#' + nameInLayout + nextDropdown).append("<option value=''>Please Select</option>");

                            }
                        }
                    });
                }
            }
        },

        _clearDropDowns: function (count, finderId, dataId) {
            for (var j = 1; j < count; j++) {
                if (j >= dataId) {
                    document.getElementById(nameInLayout + (j + 1)).selectedIndex = 0;
                    jQuery('#' + nameInLayout + (j + 1)).empty();
                    jQuery('#' + nameInLayout + (j + 1)).append("<option value=''>Please Select</option>");
                }
            }
        }

    });
    return jQuery.magearray.categoryfilter;
});
