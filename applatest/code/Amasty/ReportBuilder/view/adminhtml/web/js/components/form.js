/**
 * Magento form component extend for Report builder
 */
define([
    'Magento_Ui/js/form/form',
    'ko',
    'underscore',
    'amrepbuilder_helpers',
    'uiRegistry'
], function (uiForm, ko, _, helpers, registry) {
    'use strict';

    return uiForm.extend({
        defaults: {
            components: [
                'index = chosen_options',
                'index = entities_list',
                'index = amasty_report_builder_popup'
            ]
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            var self = this;

            this._super();

            registry.get(self.components, function () {
                helpers.initComponentsArray(arguments, self);
            });
        },

        /**
         * @inheritdocs
         */
        save: function (redirect, data) {
            if (this.validateData()) {
                this.validate();

                if (!this.additionalInvalid && !this.source.get('params.invalid')) {
                    this.updateData();
                    this.clearFormData();
                    this.setAdditionalData(data).submit(redirect);
                } else {
                    this.focusInvalid();
                }
            }
        },

        /**
         *  Validate form data
         *
         *  @returns {boolean}
         */
        validateData: function () {
            var hasVisible = false;

            this.validate();

            _.each(this.chosenOptions.elems(), function (column, index) {
                if (index === 0) {
                    return false;
                }

                if (column.isVisible()) {
                    hasVisible = true;
                }
            });

            if (!this.chosenOptions.elems().length) {
                return false;
            }

            if (this.chosenOptions.elems().length < 2 || !hasVisible) {
                this.popup.open({
                    header: 'Oops!',
                    description: 'You can\'t save and display a report with only default column. Please choose and' +
                        ' add at least one additional visible column to the Chosen options block.',
                    type: 'alert'
                });

                return false;
            }

            return true;
        },

        /**
         *  Update current data json value from chosen options list elems
         */
        updateData: function () {
            var data = this.source.data;

            data.chosen_data = ko.toJSON(this.chosenOptions.elems());
        },

        /**
         *  Preparing form data before sending to server
         */
        clearFormData: function () {
            delete this.source.data.entities;
        },

        /**
         *  Reset Form Data
         */
        reset: function () {
            window.location.reload();
        }
    });
});
