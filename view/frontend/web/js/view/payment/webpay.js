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
                type: 'webpay',
                component: 'Transbank_Webpay/js/view/payment/method-renderer/webpay-method'
            }
        );
        return Component.extend({});
    }
);
