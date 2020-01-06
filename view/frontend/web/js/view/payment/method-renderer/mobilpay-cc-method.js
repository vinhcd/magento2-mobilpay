define([
    'underscore',
    'jquery',
    'mage/storage',
    'mage/url',
    'mage/template',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Checkout/js/model/payment/additional-validators'
], function (
    _,
    $,
    storage,
    urlBuilder,
    mageTemplate,
    errorProcessor,
    Component,
    additionalValidators,
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Monogo_Mobilpay/payment/mobilpay-cc'
        },

        options: {
            hiddenFormTmpl: mageTemplate(
                '<% _.each(data.inputs, function(val, key){ %>' +
                '<input value="<%= val %>" name="<%= key %>" type="hidden" />' +
                '<% }); %>'
            )
        },

        getCode: function() {
            return 'mobilpay_cc';
        },

        placeOrder: function (data, event) {
            return this.continueToMobilpay(data, event);
        },

        continueToMobilpay: function (data, event) {
            var self = this;

            if (event) {
                event.preventDefault();
            }
            if (this.validate() && additionalValidators.validate()) {
                this.isPlaceOrderActionAllowed(false);

                this.getPlaceOrderDeferredObject()
                    .fail(
                        function () {
                            self.isPlaceOrderActionAllowed(true);
                        }
                    ).done(
                    function () {
                        // customerData.invalidate(['cart']);
                        // self.afterPlaceOrder();
                        storage.get(
                            urlBuilder.build('mobilpay/payment/prepareCredit', {})
                        ).fail(
                            function (response) {
                                errorProcessor.process(response, self.messageContainer);
                            }
                        ).success(
                            function (response) {
                                var $form = $('#' + self.getCode() + '-form');
                                $form.attr('action', window.checkoutConfig.payment.mobilpay_cc.apiUrl);
                                $form.attr('method', 'post');
                                var tmpl = self.options.hiddenFormTmpl({
                                    data: {inputs: response}
                                });
                                $form.append(tmpl);
                                $form.submit();
                            }
                        );
                    }
                );
                return true;
            }
            return false;
        },
    });
});
