
    define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/url',
        'Magento_Payment/js/view/payment/cc-form',
    ],
    function ($,
        Component,
        placeOrderAction,
        selectPaymentMethodAction,
        customer,
        checkoutData,
        additionalValidators,
        url) {

            'use strict';

            return Component.extend({
                defaults: {
                	  template: 'Transbank_Webpay/payment/webpay'
                },

                placeOrder: function () {

					var self = this,
                    placeOrder;
                    this.isPlaceOrderActionAllowed(false);
                    placeOrder = placeOrderAction(this.getData(), false, this.messageContainer);

					
                    $.when(self).fail(function () {
                        self.isPlaceOrderActionAllowed(true);
                    }).done(this.afterPlaceOrder.bind(this));

                },

                afterPlaceOrder: function(){

					var result = window.checkoutConfig.initTransaction;

                    result = JSON.parse(result);
                    
                    if (typeof result.token_ws !== 'undefined'){

											var form = $('<form action="' + result.url + '" method="post">' +
  										'<input type="text" name="token_ws" value="' + result.token_ws + '" />' +
  										'</form>');
											$('body').append(form);
											form.submit();

                    } else {

                    	window.location.href = result.callUrl+'transbank/Implement/CancelOrder';

                    }

                },

            });
        }
    );
