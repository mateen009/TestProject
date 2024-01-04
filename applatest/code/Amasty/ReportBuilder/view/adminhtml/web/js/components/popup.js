/**
 *  Amasty Popup UI Component
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'underscore'
], function ($, ko, Component, _) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Amasty_ReportBuilder/components/popup'
        },
        classes: {
            openPopup: '-popup-opened'
        },

        /**
         * Init observable variables
         *
         * @return {Object}
         */
        initObservable: function () {
            this._super()
                .observe({
                    isActive: false,
                    header: '',
                    description: '',
                    checkbox: false,
                    confirmCallback: false,
                    cancelCallback: false,
                    content: false,
                    buttons: [],
                    type: false
                });

            return this;
        },

        /**
         * Popup Ui Component Init
         */
        initialize: function () {
            var self = this;

            self._super();

            self.type.subscribe(function (value) {
                if (value === 'prompt') {
                    self._initPrompt();
                }

                if (value === 'alert') {
                    self._initAlert();
                }
            });
        },

        /**
         * Popup Wrapper Conteiner Init
         *
         *  @param {object} node
         */
        initWrapper: function (node) {
            var self = this;

            self.wrapper = $(node);
            node.click(function (event) {
                if (node.is(event.target)) {
                    self.close();
                }
            });
        },

        /**
         * Show method
         *
         * @param {object} data popup
         */
        open: function (data) {
            var self = this;

            $('body').addClass(self.classes.openPopup);

            _.each(data, function (value, index) {
                self[index](value);
            });

            self.isActive(true);
        },

        /**
         * Hide method
         */
        close: function () {
            var self = this;

            self.isActive(false);
            self._clear();
            $('body').removeClass(self.classes.openPopup);
        },

        /**
         * Clear method
         */
        _clear: function () {
            var self = this;

            self.header(false);
            self.confirmCallback(false);
            self.confirmCallback(false);
            self.content(false);
            self.description(false);
            self.checkbox(false);
            self.buttons([]);
            self.type(false);
        },

        /**
         * Popup type prompt Init
         */
        _initPrompt: function () {
            var self = this;

            self.buttons([
                {
                    text: 'Cancel',
                    classes: '-clear -link -cancel',
                    callback: function () {
                        if (self.cancelCallback()) {
                            self.cancelCallback()();
                        }

                        self.close();
                    }
                },
                {
                    text: 'Yes',
                    classes: '-primary',
                    callback: function () {
                        if (self.confirmCallback()) {
                            self.confirmCallback()();
                        }

                        self.close();
                    }
                }
            ]);
        },

        /**
         * Popup type alert Init
         */
        _initAlert: function () {
            var self = this;

            self.buttons([
                {
                    text: 'Ok',
                    classes: '-primary',
                    callback: function () {
                        self.close();
                    }
                }
            ]);
        }
    });
});
