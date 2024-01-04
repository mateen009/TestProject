/**
 * Amasty Sidebar Wrapper component
 */

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
            template: 'Amasty_ReportBuilder/components/sidebar/wrapper',
            templates: {
                button: 'Amasty_ReportBuilder/components/button',
                toggleIcon: 'Amasty_ReportBuilder/components/icons/toggle'
            },
            components: [
                'index = amrepbuilder_sidebar_loader'
            ]
        },
        nodes: {
            body: $('body')
        },
        classes: {
            opened: '-amrepbuilder-opened'
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

            resolver(function () {
                if (typeof self.loader !== 'undefined') {
                    self.loader.isLoad(false);
                }
            });
        },

        /**
         * Toggle sidebar block
         */
        toggleSidebar: function () {
            var self = this;

            self.nodes.body.toggleClass(self.classes.opened);
        },

        /**
         * Set url string to window location href
         *
         * @param {string} url
         */
        setHref: function (url) {
            window.location.href = url;
        }
    });
});
