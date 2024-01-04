/**
 * Column Header Component
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'amrepbuilder_helpers',
    'uiRegistry'
], function ($, Component, ko, helpers, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/builder/chosen_options/columns/header',
            templates: {
                button: 'Amasty_ReportBuilder/components/button',
                checkIcon: 'Amasty_ReportBuilder/components/icons/check_mark',
                editIcon: 'Amasty_ReportBuilder/components/icons/edit',
                closeIcon: 'Amasty_ReportBuilder/components/icons/close'
            },
            components: [
                'index = chosen_options'
            ]
        },
        classes: {
            edit: '-edit'
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    editableItem: null,
                    currentValue: null
                });

            return this;
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            var self = this;

            self._super();

            registry.get(self.components, function () {
                helpers.initComponentsArray(arguments, self);
            });
        },

        /**
         * Item states initialization
         *
         * @params {Object} item
         */
        initItem: function (item) {
            if (ko.isObservable(item.customTitle)) {
                return false;
            }

            item.customTitle = ko.observable(item.customTitle || false);
            item.isEdited = ko.observable(false);
        },

        /**
         * On focus item title event Method
         *
         *  @params {Object} item
         */
        onFocus: function (item) {
            item.isEdited(true);
            this.chosenOptions.isEdited(true);
            this.editableItem(item);
            this.currentValue(item.customTitle());

            if (!item.customTitle()) {
                item.customTitle(item.title);
            }
        },

        /**
         * On Blur event Component Method
         */
        onBlur: function () {
            if (this.editableItem()) {
                this.editableItem().isEdited(false);
                this.currentValue(null);
            }

            this.chosenOptions.isEdited(false);
            this.editableItem(null);
        },

        /**
         * Save item title Method
         */
        save: function () {
            if (this.editableItem().title === this.editableItem().customTitle() || !this.editableItem().customTitle()) {
                this.reset();
            }

            this.onBlur();
        },

        /**
         * Reset item title to default Method
         */
        reset: function () {
            this.editableItem().customTitle('');
            this.onBlur();
        },

        /**
         * Cancelling editing Method
         */
        cancel: function () {
            this.editableItem().customTitle(this.currentValue());
            this.onBlur();
        }
    });
});
