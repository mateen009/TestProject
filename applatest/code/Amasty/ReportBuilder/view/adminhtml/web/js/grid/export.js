/**
 * Grid export with confirm
 */

define([
    'jquery',
    'underscore',
    'Magento_Ui/js/grid/export',
    'Magento_Ui/js/modal/confirm'
], function ($, _, Component, confirm) {
    'use strict';

    return Component.extend({
        defaults: {
            confirmMessage: 'Action can take a while. Continue?'
        },

        initConfig: function () {
            this._super();

            this.options = _.reject(this.options, { disabled: true });

            return this;
        },

        /**
         * Extracts filtering data from data provider.
         *
         * @returns {Object} Current filters state.
         */
        getFiltering: function () {
            var source = this.source,
                keys = ['filters', 'search', 'namespace'];

            if (!source) {
                return {};
            }

            return _.pick(source.get('params'), keys);
        },

        getParams: function () {
            return this.getFiltering();
        },

        applyOption: function () {
            var parent = this._super.bind(this);

            confirm({
                content: this.confirmMessage,
                actions: {
                    confirm: function () {
                        parent();
                    }
                }
            });
        }
    });
});
