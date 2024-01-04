define([
    'jquery'
], function ($) {
    'use strict';

    $.widget('mage.reindexStock', {
        options: {
            url: '',
            failedText: '',
            selectors: {
                label: '.button-label'
            }
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._on({
                'click': $.proxy(this._addCronJob, this)
            });
        },

        /**
         * Method triggers an AJAX request to add cron job to the queue
         * @private
         */
        _addCronJob: function () {
            var self = this,
                result = self.options.failedText;

            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: {
                    form_key: FORM_KEY
                }
            }).done(function (response) {
                if (response['message']) {
                    result = response['message'];
                }
            }).always(function () {
                self.element.find(self.options.selectors.label).text(result);
            });
        }
    });

    return $.mage.reindexStock;
});
