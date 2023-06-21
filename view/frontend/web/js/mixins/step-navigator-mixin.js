define([
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function (quote, customerData) {
    return function (stepNavigator) {
        stepNavigator.steps.subscribe(function (steps) {
            if (steps[0].isVisible()) {
                customerData.reload(['cart'], true);
            }
        });
        return stepNavigator;
    }
});
