/**
 *  Amasty Select UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/components/select'
        },

        /**
         * Select Picker Component initialization
         *
         * @param {Object} item
         * @param {Object} options - select options
         * @param {String} depsName - dependency type name
         * @param {String} label - default select label
         */
        init: function (item, options, depsName, label) {
            var selectedOption,
                value = item[depsName].value,
                label = label || 'Select Option';

            if (!options.length) {
                return false;
            }

            item['select_' + depsName] = {
                value: ko.observable(value() || ''),
                label: ko.observable(label),
                options: options
            };

            item['select_' + depsName].value.subscribe(function (currentValue) {
                value(currentValue);
            });

            selectedOption = ko.utils.arrayFirst(item['select_' + depsName].options, function (option) {
                return option.value === item['select_' + depsName].value(); // Magento's attr types can be diff
            });

            if (selectedOption) {
                item['select_' + depsName].label(selectedOption.label);
            }
        },

        /**
         * Select Dropdown initialization
         *
         * @param {Object} node datepicker container
         */
        initDropdown: function (node) {
            $(node).dropdown();
        },

        /**
         * Clearing Select values in target item
         *
         * @param {Object} item
         * @param {String} depsName dependency type name
         */
        clear: function (item, depsName) {
            item['select_' + depsName].value(false);
            item['select_' + depsName].label('Select Option');
        },
    });
});
