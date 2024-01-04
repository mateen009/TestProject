define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore',
    'amrepbuilder_helpers',
    'uiRegistry'
], function ($, Component, ko, _, helpers, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/builder/search/wrapper',
            templates: {
                button: 'Amasty_ReportBuilder/components/button',
                message: 'Amasty_ReportBuilder/builder/search/message',
                searchIcon: 'Amasty_ReportBuilder/components/icons/search',
                alertIcon: 'Amasty_ReportBuilder/components/icons/alert',
                closeIcon: 'Amasty_ReportBuilder/components/icons/close'
            },
            placeholder: 'Start typing to find the column…',
            message: 'No result. Try changing your search query.',
            components: [
                'index = entities_list'
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
                    isSearched: false,
                    isHasResults: false,
                    isSearching: false,
                    results: [],
                    value: ''
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

            self.value.subscribe(function (value) {
                self.search(value);
            })
        },

        /**
         * Search process
         *
         * Searching target columns via value in entity list
         *
         * @params {String} value
         */
        search: _.debounce(function (value) {
            var self = this,
                searchRegex,
                isHit;

            if (!value.length) {
                self.isSearched(false);
                self.clear();

                return false;
            }

            self.isSearching(true);
            self.isHasResults(false);
            self.clear();

            searchRegex = self._preparingSearchRegex(value);

            self.entitiesList.elems.each(function (entity) {
                isHit = false;
                entity.searchCount(0);
                entity.isViewAll(false);

                _.each(entity.columns(), function (column) {
                    var columnTitle = column.title(),
                        matchResult = columnTitle.match(searchRegex);

                    isHit = (!column.isDisabled() && matchResult);

                    if (isHit) {
                        entity.searchCount(entity.searchCount() + 1);
                        entity.isViewAll(true);
                        self.results.push(column);
                        self._highlightColumnTitle(column, matchResult);
                    }

                    if (isHit && !self.isHasResults()) {
                        self.isHasResults(true);
                    }

                    column.isHit(!_.isEmpty(isHit));
                });
            });

            self.isSearched(true);
            self.isSearching(false);
        }, 1200),

        /**
         * Clearing all searched results
         */
        clear: function () {
            var self = this;

            self.results.each(function (column) {
                self.clearColumnTitle(column);
            });

            self.entitiesList.elems.each(function (entity) {
                entity.isViewAll(false);
            });

            self.results.removeAll();
        },

        /**
         * Clearing target column title
         *
         * @params {Object} column
         */
        clearColumnTitle: function (column) {
            column.title(column.title().replace(/<\/?[^>]+(>|$)/g, ""));
        },

        /**
         * Preparing target search string & clearing special characters
         *
         * @params {String} string - request string
         * @returns {Object} regex - cleared regex search
         */
        _preparingSearchRegex: function (string) {
            var string = string.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

            return new RegExp(string, 'i');
        },

        /**
         * Highlight target column title
         *
         * @params {Object} column
         * @params {Object} matchResult of the string
         */
        _highlightColumnTitle: function (column, matchResult) {
            var oldTitle = column.title(),
                newTitle = oldTitle.substring(0, matchResult.index) + "<mark>" + matchResult[0] + "</mark>" + oldTitle.substring(matchResult.index + matchResult[0].length);

            column.title(newTitle);
        },
    });
});
