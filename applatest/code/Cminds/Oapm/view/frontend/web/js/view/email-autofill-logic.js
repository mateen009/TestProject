define(
    [
        'ko',
        'uiComponent',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data'
    ],
    function (ko, Component, $, quote, checkoutData) {
        "use strict";
        if (
            window.checkoutConfig.hasOwnProperty('oapm')
            && window.checkoutConfig.oapm.hasOwnProperty('customerEmail')
        ) {
            var email = window.checkoutConfig.oapm.customerEmail;
            quote.guestEmail = email;
            // @toDo may not be needed, check
            checkoutData.setValidatedEmailValue(email);
            checkoutData.setCheckedEmailValue(email);
            var $form = $("<form/>");
            $form.attr('data-role', 'email-with-possible-login')
                .append('<input type="hidden" name="username" value="' + email + '" />');
            $('#checkout').append($form);
        }

        return Component.extend({});
    }
);
