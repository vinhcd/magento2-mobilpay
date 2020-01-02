define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    rendererList.push(
        {
            type: 'mobilpay_cc',
            component: 'Monogo_Mobilpay/js/view/payment/method-renderer/mobilpay-cc-method'
        }
    );

    /** Add view logic here if needed */
    return Component.extend({});
});
