define(
    ['mage/utils/wrapper'],
    function (wrapper) {
        'use strict';
        return function (selectShippingAddressAction) {
            return wrapper.wrap(selectShippingAddressAction, function (originalAction, shippingAddress ) {

                var originalFunction = shippingAddress.canUseForBilling;
                shippingAddress.canUseForBilling = function(){
                    if( window.checkoutConfig.hasOwnProperty('oapm') ){
                        var email = null;
                        if (window.checkoutConfig.oapm.hasOwnProperty('customerEmail') )
                            email = window.checkoutConfig.oapm.customerEmail;

                        if (email)
                            return false;
                        else
                            return originalFunction();
                    }
                }

                return originalAction(shippingAddress);
            });
        };
    }
);