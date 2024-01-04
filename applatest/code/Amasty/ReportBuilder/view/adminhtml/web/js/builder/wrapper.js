define([
    'jquery',
    'uiComponent',
    'ko',
    'amrepbuilder_helpers',
    'uiRegistry',
    'mage/translate'
], function ($, Component, ko, helpers, registry, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/builder/wrapper',
            columnHeaderTmpl: 'Amasty_ReportBuilder/builder/header',
            columnTmpl: 'Amasty_ReportBuilder/builder/available_options/columns/column',
            messages: {
                sameAxes: $t('Axis values cannot be the same')
            },
            components: [
                'index = chosen_options',
                'index = entities_list',
                'index = chart_axis_x',
                'index = chart_axis_y',
                'index = is_use_period',
                'index = amasty_report_builder_popup'
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
                    isVisible: false
                });

            return this;
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

                self._usePeriodInit();
                self._axisSelectsInit();

                self.entitiesList.elems.subscribe(function (value) {
                    if (value) {
                        self.isVisible(!!value.length);
                    }
                });
            });
        },

        /**
         * Adding target select option for axes
         *
         * @param {object} item
         */
        addAxis: function (item) {
            var self = this,
                valueX = self.axisXSelect.value(),
                valueY = self.axisYSelect.value();

            self.axisXSelect.options.push(self._createOption(item));
            self.axisYSelect.options.push(self._createOption(item));

            self.axisXSelect.value(valueX);
            self.axisYSelect.value(valueY);
        },

        /**
         * Removing target select option for axes by option id
         *
         * @param {string} id
         */
        removeAxis: function (id) {
            var self = this,
                valueX = self.axisXSelect.value(),
                valueY = self.axisYSelect.value();

            self.axisXSelect.options.remove(function (item) {
                return item.value === id;
            });

            self.axisYSelect.options.remove(function (item) {
                return item.value === id;
            });

            self.axisXSelect.value(valueX);
            self.axisYSelect.value(valueY);
        },

        /**
         * Clearing axes data
         *
         * @param {number} index
         */
        clearAxes: function (index) {
            var self = this;

            index = index ? index : 0;

            self.axisXSelect.options.splice(index);
            self.axisYSelect.options.splice(index);
        },

        /**
         * Axis select initialization
         */
        _axisSelectsInit: function () {
            var self = this;

            self.entitiesList.currentPrimaryColumn.subscribe(function (item) {
                self.removeAxis(item.id);
                self.addAxis(item);
            });

            self.axisXSelect.value.subscribe(function (value) {
                if (value) {
                    self._validateAxes();
                }
            });

            self.axisYSelect.value.subscribe(function (value) {
                if (value) {
                    self._validateAxes();
                }
            });
        },

        /**
         * Axes validation
         */
        _validateAxes: function () {
            if (this.axisYSelect.value() === this.axisXSelect.value()) {
                this.axisXSelect.error(this.messages.sameAxes);
                this.axisYSelect.error(this.messages.sameAxes);
            } else {
                this.axisXSelect.error(false);
                this.axisYSelect.error(false);
            }
        },

        /**
         * Use Period Select initialization
         */
        _usePeriodInit: function () {
            var self = this,
                nextColumn;

            self.isUsePeriod.visible(false);
            self.isUsePeriod.value(self.source.data.is_use_period);

            self.isUsePeriod.value.subscribe(function (value) {
                nextColumn = value ? self.entitiesList.mainEntity.periodColumn() : self.entitiesList.mainEntity.primaryColumn();

                if (!self.chosenOptions._checkColumnFiltrationStatus(self.entitiesList.currentPrimaryColumn())) {
                    self.entitiesList.setPrimaryColumn(nextColumn);

                    return false;
                }

                self.popup.open({
                    header: 'Are you sure?',
                    description: 'Current sorting and filters configuration of the main column will be lost.',
                    confirmCallback: function () {
                        self.entitiesList.setPrimaryColumn(nextColumn);
                        self.source.data.is_use_period = value;
                    },
                    cancelCallback: function () {
                        self.isUsePeriod.value.silentUpdate(!self.isUsePeriod.value());
                        self.isUsePeriod.checked(self.isUsePeriod.value());
                    },
                    type: 'prompt'
                });
            });
        },

        /**
         * Creating axis select option from item prototype
         *
         * @param {object} item
         * @returns {object} created option
         */
        _createOption: function (item) {
            return {
                label: item.title(),
                labeltitle: item.title(),
                value: item.id
            };
        }
    });
});
