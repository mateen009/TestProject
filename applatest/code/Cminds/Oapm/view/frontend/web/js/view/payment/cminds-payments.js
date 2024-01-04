define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'cminds_oapm',
                component: 'Cminds_Oapm/js/view/payment/method-renderer/oapm-method'
            }
        );

        return Component.extend({});
    }
);
