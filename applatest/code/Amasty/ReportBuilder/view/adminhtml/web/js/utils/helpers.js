/**
 * Reports Builder helpers
 */

define([
    'jquery',
    'ko',
    'underscore'
], function ($, ko, _) {
    'use strict';

    /**
     * Update ko subscribe method with silent possibilities
     */
    ko.observable.fn.silentUpdate = function (value) {
        this.notifySubscribers = function () {};
        this(value);

        this.notifySubscribers = function () {
            ko.subscribable.fn.notifySubscribers.apply(this, arguments);
        };
    };

    return {

        /**
         * Concat array with all nesting to string
         *
         * @node {object} datepicker container
         * @returns {string} result
         */
        concatArray: function (array) {
            return Array.prototype.concat.apply([], array).join(', ');
        },

        /**
         * Components Array initialization and setting in target component
         *
         * @param {Array} array target uiClasses
         * @param {Object} component current uiClass
         */
        initComponentsArray: function (array, component) {
            _.each(array, function (item) {
                component[item.uniq_name] = item;
            });
        }
    }
});
