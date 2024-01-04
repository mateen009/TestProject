define([
    'jquery',
    'uiComponent',
    'ko',
    'amrepbuilder_helpers',
    'uiRegistry',
    'rjsResolver'
], function ($, Component, ko, helpers, registry, resolver) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/builder/chosen_options/wrapper',
            templates: {
                column: 'Amasty_ReportBuilder/builder/chosen_options/columns/column',
                columnsList: 'Amasty_ReportBuilder/builder/chosen_options/columns/columns_list',
                button: 'Amasty_ReportBuilder/components/button',
                header: 'Amasty_ReportBuilder/builder/header',
                columnHeader: 'Amasty_ReportBuilder/builder/chosen_options/columns/header',
                columnFooter: 'Amasty_ReportBuilder/builder/chosen_options/columns/footer',
                overlay: 'Amasty_ReportBuilder/builder/chosen_options/overlay',
                howToLink: 'Amasty_ReportBuilder/builder/howto_link'
            },
            title: 'Chosen options',
            descr: 'Add the columns here that you would like to configure to be displayed on the report page',
            elems: ko.observableArray([]).extend({deferred: true}),
            entitiesList: {},
            components: [
                'index = entities_list',
                'index = amreportbuilder_report_form',
                'index = chosen_column_header',
                'index = component_search',
                'index = chosen_column_toolbar',
                'index = amasty_report_builder',
                'index = amasty_report_builder_popup'
            ],
            imports: {
                howto_link: '${ $.provider }:howto_link'
            }
        },
        selectors: {
            column: '[data-amrepbuilder-js="column"]:not(.-disabled)'
        },
        classes: {
            hover: '-hovered',
            disabled: '-disabled'
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    isEdited: false
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

                self._initPrimaryColumn();
            });

            resolver(function () {
                if (self.source.data && self.source.data.chosen_data) {
                    self._initChosenData();
                }
            });
        },

        /**
         * Initialize DropZone
         *
         * @param {object} element
         */
        initDropZone: function (element) {
            var self = this;

            self.dropZone = $(element);
            self.dropZone.droppable({
                over: function () {
                    self.dropZone.addClass(self.classes.hover);
                },
                out: function () {
                    self.dropZone.removeClass(self.classes.hover);
                },
                drop: function (event, ui) {
                    self.currentDropItem = ui.draggable;
                    self.dropZone.removeClass(self.classes.hover);
                }
            });
        },

        /**
         * Initialize Drag and Drop functionality for target column
         *
         * @param {object} item prototype column node element
         */
        initDnD: function (item) {
            var self = this;

            $(item).sortable({
                items: self.selectors.column,
                receive: function (event, ui) {
                    var prototype = $(ui.item).data('item');

                    self.addColumn(prototype, self.currentDropItem.index());
                    self.currentDropItem.remove();
                },
                start: function (event, ui) {
                    self.isSorting = true;
                    self.currentSortingItemIndex = ui.item.index();
                },
                stop: function (event, ui) {
                    if (self.isSorting) {
                        self._sortColumn(ui);
                    }
                }
            });
        },

        /**
         * Initialize particular column
         *
         * @param {object} column target column
         */
        initColumn: function (column) {
            this.columnHeader.initItem(column);
            column.isFooterActive = ko.observable(false);
            this.toolbar.initAggregation(column);
            this.toolbar.initFiltration(column);
            column.isDate = ko.observable(column.isDate || false);
            column.isVisible = ko.observable(
                typeof column.isVisible !== 'undefined' ? column.isVisible : true
            );
            if (!ko.isObservable(column.sortStatus)) {
                column.sortStatus = ko.observable(column.sortStatus || 0);
            }

        },

        /**
         * Reset sorting in all chosen columns
         */
        resetSorting: function () {
            this.elems.each(function (column) {
                column.sortStatus(0);
            });
        },

        /**
         *  Add target column from available list to chosen list
         *
         *  @param {object} prototype target prototype
         *  @param {number} index target position
         */
        addColumn: function (prototype, index) {
            var self = this,
                prototypeEntity = self.entitiesList.elems()[prototype.entity_index];

            index = index || self.elems().length;

            prototypeEntity.searchCount(prototypeEntity.searchCount() - 1);
            self.search.clearColumnTitle(prototype);
            self.initColumn(prototype);
            prototype.isDisabled(true);
            prototypeEntity.chosenColumnsList.push(prototype.index);

            if (self.elems().length === index) {
                self.elems.push(prototype);
            } else {
                self.elems.splice(index, 0, prototype);
            }

            self.builder.addAxis(prototype);
            self.isSorting = false;
        },

        /**
         * Remove columns list from chosen index
         *
         * @param {number} targetIndex
         */
        removeColumns: function (targetIndex) {
            var self = this,
                index = 0;

            self.elems.each(function () {
                if (index > targetIndex) {
                    self.removeColumn(self.elems()[index--]);
                }

                index++;
            });
        },

        /**
         * Remove target column from chosen list
         *
         * @param {object} item target item
         */
        removeColumn: function (item) {
            if (item.sortStatus()) {
                this.entitiesList.currentPrimaryColumn().sortStatus(2);
            }

            this.builder.removeAxis(item.id);
            this.entitiesList.removeChosenColumn(item.entity_index, item.index);
            this.elems.remove(function (column) {
                return column.position === item.position;
            });
            this.entitiesList.clearColumn(item.entity_index, item.name);
        },

        /**
         * Clearing all elems from chosen list and enabling prototypes elements
         */
        clearAll: function () {
            var self = this;

            self.popup.open({
                header: 'Are you sure?',
                description: 'Do you really want to delete all columns?',
                confirmCallback: function () {
                    self.removeColumns(0);
                },
                type: 'prompt'
            });
        },

        /**
         *  Sorting columns Method
         *
         *  @param {object} ui DnD helper class
         */
        _sortColumn: function (ui) {
            var self = this,
                targetIndex = ui.item.index(),
                prototype;

            if (targetIndex !== this.currentSortingItemIndex) {
                prototype = self.elems.splice(self.currentSortingItemIndex, 1)[0];

                self.elems.splice(targetIndex, 0, prototype);
            }
        },

        /**
         * Chosen Data initialization
         */
        _initChosenData: function () {
            var self = this,
                entity,
                prototype;

            _.each(self.source.data.chosen_data, function (column) {
                entity = self.entitiesList.getEntityByName(column.entity_name);
                prototype = entity.columns()[column.name];

                column.entity_index = entity.index;
                self.initColumn(column);

                if (prototype) {
                    entity.chosenColumnsList.push(prototype.index);

                    prototype.position = column.position;
                    prototype.isDate = ko.observable(column.isDate());
                    prototype.isFilter = ko.observable(column.isFilter());
                    prototype.isVisible = ko.observable(column.isVisible());
                    prototype.sortStatus = ko.observable(column.sortStatus());

                    column.isDisabled = prototype.isDisabled;
                    column.isDisabled(true);
                }
            });

            self.entitiesList.currentPrimaryColumn.silentUpdate(self.source.data.chosen_data[0]);
            self.elems(self.source.data.chosen_data);
        },

        /**
         * Initialize primary column
         */
        _initPrimaryColumn: function () {
            var self = this;

            self.entitiesList.currentPrimaryColumn.subscribe(function (column) {
                if (self.elems()[0]) {
                    self.removeColumn(self.elems()[0]);
                }

                self.elems.unshift(column);
            });
        },

        /**
         * Checking column filtration status
         *
         * @returns {boolean}
         */
        _checkColumnFiltrationStatus: function (column) {
            if (column.position === undefined) {
                return false
            }

            return column.isDate() || !column.isVisible() || column.isFilter() || column.sortStatus();
        }
    });
});
