/**
 * Copyright © 2016 ShopGo. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        '../model/shipping-rates-validator',
        '../model/shipping-rates-validation-rules'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        skynetShippingRatesValidator,
        skynetShippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('skynet', skynetShippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('skynet', skynetShippingRatesValidationRules);
        return Component;
    }
);
