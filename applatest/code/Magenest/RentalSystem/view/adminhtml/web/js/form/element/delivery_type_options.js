/**
 * Copyright © 2019 Magenest. All rights reserved.
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        initialize: function () {
            this._super();
            var selectedType = this._super().initialValue;
            this.resetVisibility(selectedType);
            return this;
        },

        onUpdate: function (value) {
            var leadTimeField = uiRegistry.get('index = lead_time');
            if (leadTimeField.visibleValue === value) {
                leadTimeField.show();
            } else {
                leadTimeField.hide();
            }

            var addressField = uiRegistry.get('index = pickup_address');
            if (addressField.visibleValue === value) {
                addressField.show();
            } else {
                addressField.hide();
            }

            if (addressField.visibleValue !== value && leadTimeField.visibleValue !== value) {
                leadTimeField.show();
                addressField.show();
            }

            return this._super();
        },

        resetVisibility: function (selectedType) {
            if (uiRegistry.has('index = lead_time') && uiRegistry.has('index = pickup_address')) {
                var leadTimeField = uiRegistry.get('index = lead_time');
                if (leadTimeField.visibleValue === selectedType) {
                    leadTimeField.show();

                } else {
                    leadTimeField.hide();
                }

                var addressField = uiRegistry.get('index = pickup_address');
                if (addressField.visibleValue === selectedType) {
                    addressField.show();
                } else {
                    addressField.hide();
                }

                if (addressField.visibleValue !== selectedType && leadTimeField.visibleValue !== selectedType) {
                    addressField.show();
                    leadTimeField.show();
                }
            }
        },
    });
});