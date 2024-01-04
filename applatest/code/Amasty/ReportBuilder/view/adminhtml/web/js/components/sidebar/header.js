/**
 * Amasty Sidebar Header component
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
            template: 'Amasty_ReportBuilder/components/sidebar/header',
            templates: {
                button: 'Amasty_ReportBuilder/components/button'
            },
            components: [
                'index = amrepbuilder_sidebar_wrapper'
            ],
            imports: {
                sidebarData: 'amrepbuilder_sidebar_source:sidebarData'
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
        }
    });
});
