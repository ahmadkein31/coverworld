define([
    'jquery',
    'uiRegistry'
], function ($, registry) {
    var ampromoForm = {
        update: function (type) {
            var action = '';
            this.resetFields(type);
            var actionFieldSet = $('#' + type +'rule_actions_fieldset_').parent();
            window.amPromoHide = 0;

            actionFieldSet.show();
            if (typeof window.amRulesHide !="undefined" && window.amRulesHide == 1) {
                actionFieldSet.hide();
            }

            var selector = $('[data-index="simple_action"] select');
            if (type !== 'sales_rule_form') {
                action = selector[1] ? selector[1].value : selector[0].value;
            } else {
                action = selector.val();
            }

            if (action.match(/^ampromo/)) {
                this.hideFields(['simple_free_shipping', 'apply_to_shipping'], type);
            }

            this.hideBannersTab();
            switch (action) {
                case 'ampromo_cart':
                    actionFieldSet.hide();
                    window.amPromoHide = 1;

                    this.hideFields(['discount_qty', 'discount_step'], type);
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]'], type);
                    break;
                case 'ampromo_items':
                    this.showFields(['ampromorule[sku]', 'ampromorule[type]'], type);
                    this.showBannersTab();
                    break;
                case 'ampromo_product':
                    this.showBannersTab();
                    break;
                case 'ampromo_spent':
                    actionFieldSet.hide();
                    window.amPromoHide = 1;

                    this.showFields(['ampromorule[sku]', 'ampromorule[type]'], type);
                    break;
            }
        },
        showBannersTab: function(){
            jQuery('[data-index=ampromorule_top_banner]').show();
            jQuery('[data-index=ampromorule_after_product_banner]').show();
        },
        hideBannersTab: function(){
            jQuery('[data-index=ampromorule_top_banner]').hide();
            jQuery('[data-index=ampromorule_after_product_banner]').hide();
        },
        resetFields: function (type) {
            this.showFields([
                'discount_qty', 'discount_step', 'apply_to_shipping', 'simple_free_shipping'
            ], type);
            this.hideFields(['ampromorule[sku]', 'ampromorule[type]'], type);
        },

        hideFields: function (names, type) {
            return this.toggleFields('hide', names, type);
        },

        showFields: function (names, type) {
            return this.toggleFields('show', names, type);
        },

        addPrefix: function (names, type) {
            for (var i = 0; i < names.length; i++) {
                names[i] = type + '.' + type + '.' + 'actions.' + names[i];
            }

            return names;
        },

        toggleFields: function (method, names, type) {
            registry.get(this.addPrefix(names, type), function () {
                for (var i = 0; i < arguments.length; i++) {
                    arguments[i][method]();
                }
            });
        }
    };

    return ampromoForm;
});
