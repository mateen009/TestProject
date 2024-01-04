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
                type: 'usemyterms',
                component: 'Cpm_UseMyTerm/js/view/payment/method-renderer/usemyterms-method'
            }
        );
        return Component.extend({});
    }
);