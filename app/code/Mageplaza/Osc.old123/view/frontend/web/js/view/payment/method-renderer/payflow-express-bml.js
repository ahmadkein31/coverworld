/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Mageplaza_Osc/js/view/payment/method-renderer/paypal-express-abstract'
    ],
    function (Component) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Mageplaza_Osc/payment/payflow-express-bml'
            }
        });
    }
);
