/**
 * Amasty Sidebar Content component
 */

define([
    'jquery',
    'uiComponent'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/components/sidebar/content',
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
        }
    });
});
