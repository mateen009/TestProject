define([
    'jquery',
    'uiComponent',
    'ko',
    'amrepbuilder_helpers',
    'uiRegistry',
    'underscore'
], function ($, Component, ko, helpers, registry, _) {
    'use strict';

    return Component.extend({
        defaults: {
            components: [
                'index = bookmarks',
                'index = amrepbuilder_loader',
                'index = amreportbuilder_view_listing_data_source',
                'index = amrepbuilder_chart'
            ],
            imports: {
                report_id: 'index = amreportbuilder_view_listing_data_source:report_id',
                chart_update_url: 'index = amreportbuilder_view_listing_data_source:chart_update_url'
            }
        },
        form: null,

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            var self = this;

            self._super();

            registry.get(self.components, function () {
                helpers.initComponentsArray(arguments, self);

                self.loader.isLoad(true);

                self._initToolbar();
                self._initChosenData();
            });
        },

        /**
         * Update Bookmarks data with new data from toolbar
         */
        _updateBookmarks: _.debounce(function () {
            var self = this,
                formData = this.toolbar.serializeArray();

            for (var i = 0; i < formData.length; i++) {
                var input = formData[i];

                self.bookmarks.current.filters[input.name] = input.value;
            }

            self.bookmarks.saveState();
        }, 1000),

        /**
         *  Toolbar initialization
         */
        _initToolbar: function () {
            var self = this;

            self.toolbar = $('#amrepbuilder_charts_toolbar');

            self.toolbar.change(function () {
                self.loader.isLoad(true);
                self._updateBookmarks();
            });

            self.bookmarks.on('saveState', function () {
                self.loader.isLoad(true);
                self.source.reload();
            });

            self.source.on('reloaded', function () {
                self.loader.isLoad(false);
            });
        },

        /**
         *  Update toolbar data via data from bookmarks
         */
        _initChosenData: function () {
            var self = this,
                data = self.bookmarks.current.filters,
                inputsInterval = $(self.toolbar).find('[name=interval]'),
                inputStore = $(self.toolbar).find('[name=store]'),
                storeOptions = $(inputStore).find('option');

            $(inputsInterval).filter(function () {
                return this.value === data.interval
            }).click();

            $(storeOptions).filter(function () {
                return this.value === data.store
            }).attr('selected', 'true');

            self.toolbar.change();
        }
    });
});
