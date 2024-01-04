define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore',
    'amrepbuilder_helpers',
    'uiRegistry',
    'jquery/ui'
], function ($, Component, ko, _, helpers, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            maxColumnQty: 8,
            columnHeaderTmpl: 'Amasty_ReportBuilder/builder/available_options/columns/header',
            columnFooterTmpl: 'Amasty_ReportBuilder/builder/available_options/columns/footer',
            template: 'Amasty_ReportBuilder/builder/available_options/entities_list/wrapper',
            entityTmpl: 'Amasty_ReportBuilder/builder/available_options/entities_list/entity',
            columnTmpl: 'Amasty_ReportBuilder/builder/available_options/columns/column',
            columnsListTmpl: 'Amasty_ReportBuilder/builder/available_options/columns/columns_list',
            buttonTmpl: 'Amasty_ReportBuilder/components/button',
            mainEntity: {
                entity_index: 0,
                entity_name: null,
                primaryColumn: ko.observable(),
                periodColumn: ko.observable()
            },
            links: {
                isSearched: 'index = component_search:isSearched'
            },
            components: [
                'index = amreportbuilder_report_form',
                'index = chosen_options',
                'index = amasty_report_builder',
                'index = chosen_column_toolbar',
                'index = is_use_period',
                'index = store_ids'
            ]
        },
        selectors: {
            chosenList: '[data-amrepbuilder-js="chosen-list"]'
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    isSearched: false,
                    currentPrimaryColumn: false
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

            self.elems = ko.observableArray([]).extend({deferred: true});

            registry.get(self.components, function () {
                helpers.initComponentsArray(arguments, self);

                if (self.source.data.entities) {
                    self.initEntities(self.source.data.entities);
                }

                self.elems(self.source.data.entities);
            });
        },

        /**
         * Initialize particular entities list
         *
         * @param {Array} items target entities
         */
        initEntities: function (items) {
            var self = this;

            _.each(items, function (item, index) {
                self._initEntity(item, index);
            });
        },


        /**
         * Initialize particular entity
         *
         * @param {object} item target entity
         * @param {number} index target entity index
         */
        _initEntity: function (item, index) {
            item.index = index;
            item.isActive = ko.observable(false);
            item.searchCount = ko.observable(0);
            item.isViewAll = ko.observable(false);
            item.chosenColumnsList = ko.observableArray([]);
            item.columns = ko.observable(item.columns);

            this._initColumns(item);

            if (index === 0) {
                this._initMainEntity(item);
            }
        },

        /**
         * Initialize particular columns list
         *
         * @param {Object} entity target columns
         */
        _initColumns: function (entity) {
            var self = this;

            _.each(Object.keys(entity.columns()), function (key, index) {
                self._initColumn(entity.columns()[key], entity, index);
            });
        },

        /**
         * Initialize particular column
         *
         * @param {object} column target column
         * @param {object} entity column entity
         * @param {number} index column
         */
        _initColumn: function (column, entity, index) {
            column.index = index;
            column.title = ko.observable(column.title);
            column.entity_index = entity.index;
            column.isVisible = ko.observable(true);
            column.isHit = ko.observable(false);

            if (!column.isDisabled) {
                column.isDisabled = ko.observable(false);
            }

            if (column.entity_index === 0 && column.use_for_period) {
                this._initPeriodColumn(column);
            }

            if (column.entity_index === 0 && column.primary) {
                this.mainEntity.primaryColumn(column);

                if (!this.source.data.chosen_data || !this.source.data.chosen_data.length) {
                    this.chosenOptions.initColumn(column);
                    column.sortStatus(2);
                    this.setPrimaryColumn(column);
                }
            }
        },

        /**
         *  Choose and add target column to chosen list via Chosen Component Method
         *
         *  @param {object} prototype target prototype
         */
        chooseColumn: function (prototype) {
            this.chosenOptions.addColumn(prototype);
        },

        /**
         * Setting Primary column§
         *
         * @param {object} column target column
         */
        setPrimaryColumn: function (column) {
            var self = this;

            self.chosenOptions.initColumn(column);

            if (self.currentPrimaryColumn()) {
                self.currentPrimaryColumn().isDisabled(false);
            }

            if (column.position !== undefined) {
                self.chosenOptions.removeColumn(column);
            }

            self.currentPrimaryColumn(column);

            column.isDisabled(true);
        },

        /**
         * Initialize Drag and Drop functionality for target column
         *
         * @param {object} item prototype column element
         * @param {object} nodeElement
         */
        initDnD: function (item, nodeElement) {
            var self = this;

            $(nodeElement).draggable({
                helper: 'clone',
                connectToSortable: self.selectors.chosenList
            }).data({
                item: item
            });
        },

        /**
         *  Remove an entry from the target entity chosen column list by target column index
         *
         *  @param {number} entityIndex target entity index
         *  @param {number} columnIndex
         */
        removeChosenColumn: function (entityIndex, columnIndex) {
            this.elems()[entityIndex].chosenColumnsList.remove(function (item) {
                return item === columnIndex;
            });
        },

        /**
         * Get entity by name
         *
         * @param {string} entityName
         */
        getEntityByName: function (entityName) {
            return ko.utils.arrayFirst(this.elems(), function (entity) {
                return entity.name === entityName;
            });
        },

        /**
         * Get main entity with relations by entity name
         *
         * @param {string} entityName entity name
         * @param {function} callback target function for preparing data
         *
         */
        getEntity: function (entityName, callback) {
            var self = this;

            $.ajax({
                url: self.source.main_entity_url,
                method: 'POST',
                data: {
                    entityName: entityName
                },
                showLoader: true
            }).done(function (data) {
                callback(data);
            });
        },

        /**
         *  Get Column by target index from target entity
         *
         *  @param {number} entityIndex
         *  @param {number} columnIndex
         *  @returns {object} particular column
         */
        getColumnByIndex: function (entityIndex, columnIndex) {
            return this.elems()[entityIndex].columns()[Object.keys(this.elems()[entityIndex].columns())[columnIndex]];
        },

        /**
         *  Clearing all chosen options from target entity
         *
         *  @param {number} entityIndex entity
         */
        clearEntity: function (entityIndex) {
            var self = this,
                column,
                entity = this.elems()[entityIndex],
                entityChosenList = _.clone(entity.chosenColumnsList());

            _.each(entityChosenList, function (columnIndex) {
                column = self.getColumnByIndex(entityIndex, columnIndex);

                if (column) {
                    self.chosenOptions.removeColumn(column);
                }
            });
        },

        /**
         *  Clearing Columns properties
         *
         *  @param {number} entityIndex
         *  @param {string} columnName
         */
        clearColumn: function (entityIndex, columnName) {
            var column = this.elems()[entityIndex].columns()[columnName];

            column.isDisabled(false);

            if (column.position !== undefined) {
                column.isDate(false);
                column.sortStatus(0);
                column.isVisible(true);
                delete column.position;
            }

            if (column.customTitle) {
                column.isEdited(false);
                column.customTitle = null;
            }

            if (column.aggregation) {
                this.toolbar.clearAggregation(column);
            }

            if (column.filtration) {
                this.toolbar.clearFiltration(column);
            }
        },

        /**
         * Main entity initialization
         *
         * @param {object} entity target entity
         */
        _initMainEntity: function (entity) {
            this.storeIds.visible(entity.columns().store_id !== undefined);

            entity.isActive(true);

            this.mainEntity.entity_name = entity.name;
        },

        /**
         * Use period column initialization
         *
         * @param {object} column target column
         */
        _initPeriodColumn: function (column) {
            this.isUsePeriod.visible(true);
            this.mainEntity.periodColumn(column);
        }
    });
});
