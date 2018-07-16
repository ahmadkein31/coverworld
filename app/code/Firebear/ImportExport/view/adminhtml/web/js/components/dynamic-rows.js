/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

define(
    [
        'Magento_Ui/js/dynamic-rows/dynamic-rows',
        'Magento_Ui/js/lib/spinner',
        'uiRegistry',
        'jquery',
        'underscore'
    ],
    function (Element, loader, reg, $, _) {
        'use strict';

        return Element.extend(
            {
                deleteRecords: function () {
                    this.destroyChildren();
                    this.recordData([]);
                    this.reload();
                },
            }
        );
    }
);