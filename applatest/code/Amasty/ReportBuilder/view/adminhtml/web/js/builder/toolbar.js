define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/translate',
    'amrepbuilder_helpers',
    'underscore',
    'uiRegistry',
    'mage/calendar',
    'mage/dropdown'
], function ($, Component, ko, $t, helpers, _, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/builder/column/toolbar/wrapper',
            templates: {
                button: 'Amasty_ReportBuilder/components/button',
                aggregationIcon: 'Amasty_ReportBuilder/components/icons/aggregation',
                date: 'Amasty_ReportBuilder/builder/column/filters/date_range',
                filterToolbar: 'Amasty_ReportBuilder/builder/column/filters/toolbar',
                text: 'Amasty_ReportBuilder/builder/column/filters/text',
                textRange: 'Amasty_ReportBuilder/builder/column/filters/text_range',
                select: 'Amasty_ReportBuilder/builder/column/filters/select',
                aggregation: 'Amasty_ReportBuilder/builder/column/filters/aggregation',
                multiselect: 'Amasty_ReportBuilder/builder/column/filters/multiselect',
                calendarIcon: 'Amasty_ReportBuilder/components/icons/calendar',
                visibilityIcon: 'Amasty_ReportBuilder/components/icons/visibility',
                filterIcon: 'Amasty_ReportBuilder/components/icons/filter',
                sortIcon: 'Amasty_ReportBuilder/components/icons/sort',
                removeIcon: 'Amasty_ReportBuilder/components/icons/remove'
            },
            components: [
                'index = chosen_options',
                'index = entities_list',
                'index = amreportbuilder_report_form',
                'index = amasty_report_builder_select',
                'index = amasty_report_builder_popup'
            ],
            imports: {
                isEdited: 'index = chosen_options:isEdited',
                isUsePeriod: 'index = is_use_period:value'
            },
            selectors: {
                datepickerFrom: '[data-amrepbuilder-js="datepicker-from"]',
                datepickerTo: '[data-amrepbuilder-js="datepicker-to"]'
            },
            toolbarButtonPath: 'Amasty_ReportBuilder/builder/column/toolbar/buttons/'
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

                self.elems([
                    self.toolbarButtonPath + 'aggregation',
                    self.toolbarButtonPath + 'date',
                    self.toolbarButtonPath + 'sort',
                    self.toolbarButtonPath + 'filter',
                    self.toolbarButtonPath + 'visibility',
                    self.toolbarButtonPath + 'remove'
                ]);
            });
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    isEdited: false,
                    isUsePeriod: false
                });

            return this;
        },

        /**
         * Column filtration object initialization
         *
         * @param {Object} column
         */
        initFiltration: function (column) {
            this._initFiltrationDeps(column, 'filtration', 'isFilter');

            switch (column.frontend_model) {
                case 'multiselect':
                    this._initMultiselectFilter(column);
                    break;
                case 'text':
                    this._initTextFilter(column);
                    break;
                case 'select':
                    this._initSelect(column, column.options,  'filtration');
                    break;
                default:
                    this._initRange(column);
            }
        },

        /**
         * Aggregation filter initialization
         *
         * @param {Object} column
         */
        initAggregation: function (column) {
            var entity = this.entitiesList.elems()[column.entity_index];

            this._initFiltrationDeps(column, 'aggregation', 'isAggregation');
            this._initSelect(column, column.aggregationOptions, 'aggregation');

            column.isAggregationEnabled = ko.observable((entity ? entity.use_aggregation : false)
                || column.eav_attribute);
        },

        /**
         * DateRange picker initialization
         *
         * @param {Object} node
         * @param {Object} column
         */
        initDatePicker: _.debounce(function (node, column) {
            $(node).dateRange({
                onClose: function (value, event) {
                    if ($(event.input).attr('name') === 'from') {
                        column.filtration.value.from(value);
                    } else {
                        column.filtration.value.to(value);
                    }
                },
                dateFormat: 'mm/dd/yy',
                from: {
                    id: 'datepicker-from-' + column.name
                },
                to: {
                    id: 'datepicker-to-' + column.name
                }
            });
        }, 1000),

        /**
         * Toggling sort status
         *
         * @param {Object} item
         */
        toggleSort: function (item) {
            var nextSortStatus = item.sortStatus() + 1 > 2 ? 0 : item.sortStatus() + 1;

            if (!item.sortStatus()) {
                this.chosenOptions.resetSorting();
            }

            if (!nextSortStatus) {
                this.entitiesList.currentPrimaryColumn().sortStatus(2);
            }

            item.sortStatus(nextSortStatus);
        },

        /**
         * Toggling sort status
         *
         * @param {Object} item
         */
        toggleDate: function (item) {
            var self = this,
                nextValue = !item.isDate();

            if (nextValue) {
                self.chosenOptions.elems.each(function (column) {
                    column.isDate(false);
                });

                item.sortStatus(false);
                item.filtration.isActive(false);
            }

            item.isDate(nextValue);
        },

        /**
         * Clearing Multiselect values in target item
         *
         * @param {Object} item
         */
        clearMultiselect: function (item) {
            this.clearFiltration(item);

            _.each(item.filtration.value(), function (filter) {
                filter.isChecked(false);
            });
        },

        /**
         * Clearing filtration values in target item
         *
         * @param {Object} item
         */
        clearRange: function (item) {
            this.clearFiltration(item);

            _.each(item.filtration.value, function (filter) {
                filter('');
            });
        },

        /**
         * Clearing text filtration values in target item
         *
         * @param {Object} item
         */
        clearText: function (item) {
            this.clearFiltration(item);
            this._initTextFilter(item);
        },

        /**
         * Clearing filtration values in target item
         *
         * @param {Object} item
         */
        clearFiltration: function (item) {
            item.filtration.isActive(false);
            item.isFilter(false);
            item.isFooterActive(false);

            delete item.filtration.value;
        },

        /**
         * Clearing aggregation values in target item
         *
         * @param {Object} item
         */
        clearAggregation: function (item) {
            item.aggregation.isActive(false);
            item.isAggregation(false);
            item.isFooterActive(false);

            delete item.aggregation.value;
        },

        /**
         * Applying filtration values in target item
         *
         * @param {Object} item
         */
        applyFiltration: function (item) {
            item.isFilter(false);
            item.isFooterActive(false);
            item.filtration.isActive(true);
        },

        /**
         * Range picker observers initialization
         *
         * @param {Object} column
         */
        _initRange: function (column) {
            if (!column.filtration.value) {
                column.filtration.value = {
                    from: ko.observable(''),
                    to: ko.observable('')
                }
            } else {
                column.filtration.value.from = ko.observable(column.filtration.value.from ? column.filtration.value.from : false);
                column.filtration.value.to = ko.observable(column.filtration.value.to ? column.filtration.value.to : false);
            }

            column.filtration.isEmpty = ko.computed(function () {
                return !(column.filtration.value.from() || column.filtration.value.to());
            }, column);
        },

        /**
         * Select initialization
         *
         * @param {Object} column
         * @param {Object} options
         * @param {String} filtrationType dependency filtration name
         */
        _initSelect: function (column, options, filtrationType) {
            column[filtrationType].value = ko.observable(column[filtrationType].value || false);

            this.select.init(column, options, filtrationType);
        },

        /**
         * Text Filter Initialization
         *
         * @param {Object} column
         */
        _initTextFilter: function (column) {
            column.filtration.value = ko.observable(column.filtration.value || '');
        },

        /**
         * Multiselect initialization
         *
         * @param {Object} column
         */
        _initMultiselectFilter: function (column) {
            // TODO: need to check multiselect
            column.filtration.value = ko.observableArray(column.filtration.value || []);

            column.filtration.isEmpty = ko.computed(function () {
                var result = true;

                column.filtration.value.each(function (element) {
                    if (element.isChecked()) {
                        result = false;
                    }
                })

                return result;
            }, column);
        },

        /**
         * Column filtration dependencies initialization
         *
         * @param {Object} column
         * @param {String} filtrationType type of filter
         * @param {String} flag type of dependency flag
         */
        _initFiltrationDeps: function (column, filtrationType, flag) {
            if (!column[filtrationType]) {
                column[filtrationType] = {};
            }

            column[filtrationType].isActive = ko.observable(column[filtrationType].isActive || false);
            column[flag] = ko.observable(column[flag] ? column[flag]() : false);
        }
    });
});
