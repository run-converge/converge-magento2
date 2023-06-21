var config = {
    map: {
        '*': {
            'converge': 'Converge_Converge/js/generic',
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/step-navigator': {
                'Converge_Converge/js/mixins/step-navigator-mixin': true
            },
        }
    }
};
