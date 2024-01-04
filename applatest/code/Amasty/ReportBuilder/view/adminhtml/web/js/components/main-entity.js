define([
    'jquery',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'amrepbuilder_helpers',
    'uiRegistry'
], function ($, uiRegistry, select, helpers, registry) {
    'use strict';

    return select.extend({
        defaults: {
            components: [
                'index = amreportbuilder_report_form',
                'index = store_ids',
                'index = entities_list',
                'index = chosen_options',
                'index = amasty_report_builder',
                'index = amasty_report_builder_popup',
                'index = display_chart',
                'index = chart_axis_x',
                'index = chart_axis_y',
                'index = is_use_period'
            ]
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    isNotShowNoticeChangeEntity: false,
                    currentValue: false
                });

            return this;
        },

        initialize: function () {
            var self = this;

            self._super();

            registry.get(self.components, function () {
                helpers.initComponentsArray(arguments, self);

                self.currentValue(self.value());

                self._displayChartInit();
                self.displayChart.visible(!!self.value());
                if (!self.value()) {
                    self.storeIds.visible(false);
                }
            });
        },

        /**
         * Entity switcher listener
         *
         * @param {string} value
         */
        onUpdate: function (value) {
            var self = this;

            if (value === self.currentValue()) {
                return false;
            }

            if (self.chosenOptions.elems().length > 1
                && self.entitiesList.elems().length
                && !self.isNotShowNoticeChangeEntity()) {
                self.popup.open({
                    header: 'Are you sure to perform the action?',
                    description: 'The configured data will be lost.',
                    checkbox: {
                        title: 'Do not display this message again.',
                        observer: self.isNotShowNoticeChangeEntity
                    },
                    cancelCallback: function () {
                        self.value(self.currentValue());
                    },
                    confirmCallback: function () {
                        self.changeEntity(value);
                    },
                    type: 'prompt'
                });
            } else {
                self.changeEntity(value);
            }

            return this._super();
        },

        /**
         * Change current entity
         *
         * @param {string} value entity name
         */
        changeEntity: function (value) {
            var self = this;

            self.currentValue(value);
            this.storeIds.value(['0']);

            this.displayChart.visible(!!value);
            this.storeIds.visible(!!value);

            self.chosenOptions.elems.splice(0);
            self.entitiesList.elems([]);

            self.builder.clearAxes();
            self.builder.isUsePeriod.visible(false);
            self.builder.isUsePeriod.checked(false);

            delete self.source.data.chosen_data;

            if (!value) {
                return false;
            }

            self.entitiesList.getEntity(value, function (data) {
                self.entitiesList.source.data.entities = data;
                self.entitiesList.initEntities(data);
                self.entitiesList.elems(data);
            });
        },

        /**
         * Display chart initialization
         */
        _displayChartInit: function () {
            var self = this;

            self.axisXSelect.visible(self.displayChart.value());
            self.axisYSelect.visible(self.displayChart.value());

            self.displayChart.value.subscribe(function (value) {
                self.axisXSelect.visible(value);
                self.axisYSelect.visible(value);
            });
        }
    });
});
