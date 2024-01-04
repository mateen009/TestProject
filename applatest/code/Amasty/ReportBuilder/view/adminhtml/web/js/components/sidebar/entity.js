/**
 * Amasty Sidebar Entity component
 */

define([
    'jquery',
    'uiComponent',
    'amrepbuilder_helpers',
    'uiRegistry'
], function ($, Component, helpers, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/components/sidebar/entity',
            templates: {
                entity: 'Amasty_ReportBuilder/components/sidebar/entity',
                button: 'Amasty_ReportBuilder/components/button',
                removeIcon: 'Amasty_ReportBuilder/components/icons/remove',
                editIcon: 'Amasty_ReportBuilder/components/icons/edit',
                copyIcon: 'Amasty_ReportBuilder/components/icons/copy'
            },
            components: [
               'index = amrepbuilder_sidebar_popup',
               'index = amrepbuilder_sidebar_wrapper'
            ],
            imports: {
                sidebarData: 'amrepbuilder_sidebar_source:sidebarData',
                currentReportId: 'amrepbuilder_sidebar_source:currentReportId'
            }
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
         * Event to delete report record
         *
         * @param {string} url
         */
        deleteEntity: function (url) {
            var self = this;

            self.popup.open({
                description: 'Are you sure you want to delete this report?',
                confirmCallback: function () {
                    self.sidebarWrapper.setHref(url);
                },
                type: 'prompt'
            });
        },
    });
});
