var config = {
    config: {
        mixins: {
            /* force billing address revision */
            'Magento_Checkout/js/action/select-shipping-address': {
                'Cminds_Oapm/js/action/select-shipping-address-mixin': true
            }
        }
    },
    /* @toDo: revise in order to use a mixin */
    map: {
        '*': {
            'Magento_Checkout/js/model/shipping-save-processor/default' :
                'Cminds_Oapm/js/model/shipping-save-processor/default'
        }
    }
};