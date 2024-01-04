/* @api */
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'mage/validation'
    ],
    function (
        Component,
        $
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Cminds_Oapm/payment/oapm-form',
                recipientName: '',
                recipientEmail: '',
                recipientNote: ''
            },

            /** @inheritdoc */
            initObservable: function() {
                this._super().observe([
                    'recipientName',
                    'recipientEmail',
                    'recipientNote'
                ]);
                this.hideFieldsFlag = false;
                if (
                    window.checkoutConfig.hasOwnProperty('oapm')
                    && window.checkoutConfig.oapm.hasOwnProperty('hideFields')
                ) {
                    this.hideFieldsFlag = true;
                }
                return this;
            },

            /**
             * @return {Object}
             */
            getData: function() {
                return {
                    method: this.item.method,
                    'additional_data': {
                        'recipient_name': this.recipientName(),
                        'recipient_email': this.recipientEmail(),
                        'recipient_note': this.recipientNote()
                    }
                };
            },

            /**
             * @return {jQuery}
             */
            validate: function() {
                var form = 'form[data-role=oapm-form]';

                return $(form).validation() && $(form).validation('isValid');
            }
        });
    }
);
