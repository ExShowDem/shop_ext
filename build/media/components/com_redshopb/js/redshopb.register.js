/**
 * @copyright  Copyright (C) 2012 - 2018 Aesir. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/** global: Joomla */

// Only define the redSHOPB namespace if not defined.
redSHOPB = window.redSHOPB || {};

(function ($) {
    redSHOPB.register = {
        isAllowCompanyRegister: false,
        formId: 'registerForm',

        init: function () {
            redSHOPB.register.checkBillingCountry();
            redSHOPB.register.checkShippingCountry();

            $(document).on('change', '#jform_billing_country_id', function () {
                redSHOPB.register.checkBillingCountry();
            }).on('change', '#jform_shipping_country_id', function () {
                redSHOPB.register.checkShippingCountry()
            });

            redSHOPB.register.checkUseBilling();

            $('#jform_usebilling').click(function (event) {
                $('.js-registration-shipping-form-wrapper').toggle();
                redSHOPB.register.checkUseBilling();
            });

            $('select').chosen();

            if (redSHOPB.register.isAllowCompanyRegister === true) {
                var $formRegisterType = $('#jform_register_type');
                var $businessCompanyNameWrapper = $('.bussiness-company-name-wrapper');

                $businessCompanyNameWrapper.find('#jform_business_company_name-lbl').text($businessCompanyNameWrapper.find('#jform_business_company_name-lbl').text() + ' *');

                if ($formRegisterType.find('input[name="jform[register_type]"]:checked').val() === 'business') {
                    // Add required field for company name field
                    $businessCompanyNameWrapper.find('#jform_business_company_name').attr('required', 'required').addClass('required').attr('aria-required', 'required');
                    $('.bussinessDiv').removeClass('hidden');
                }

                var registerType = $formRegisterType.find('input[name="jform[register_type]"]:checked').val();
                if (registerType === 'personal') {
                    var hiddenRequired = $('.bussinessDiv > .controls > input.required:text');
                    toggleRequire(hiddenRequired, false);
                }

                $formRegisterType.find('input[name="jform[register_type]"]').click(function (event) {
                    var registerType = $formRegisterType.find('input[name="jform[register_type]"]:checked').val();

                    if (registerType === 'personal') {
                        // Register as "Personal"
                        $('.bussinessDiv').addClass('hidden');
                        toggleRequire(hiddenRequired, false);
                        $('.personalDiv').removeClass('hidden');
                        $('.bussiness-company-url-wrapper').addClass('hidden');
                        $businessCompanyNameWrapper.addClass('hidden');
                        $businessCompanyNameWrapper.find('#jform_business_company_name').removeAttr('required').removeClass('required').removeAttr('aria-required');
                        $('.b2c-company-infor').removeClass('hidden');
                    } else {
                        // Register as "Business"
                        $('.bussinessDiv').removeClass('hidden');
                        toggleRequire(hiddenRequired, true);
                        $('.bussiness-company-url-wrapper').removeClass('hidden');
                        $businessCompanyNameWrapper.removeClass('hidden');
                        $businessCompanyNameWrapper.find('#jform_business_company_name').attr('required', 'required').addClass('required').attr('aria-required', 'required');
                        $('.b2c-company-infor').addClass('hidden');
                    }
                })
            }
        },

        checkBillingCountry: function () {
            var $billingCountry = $('#jform_billing_country_id');
            var selectedCountryId = $billingCountry.find('option:selected');

            if (selectedCountryId.length) {
                var hasState = selectedCountryId.data('has_state');
                var eu = selectedCountryId.data('eu');
                if (hasState == 1) {
                    $('.billingStateGroup').removeClass('hide');
                } else {
                    $('.billingStateGroup').addClass('hide');
                }

                var jformVatNumber = $('#jform_vat_number');

                if (eu === 1) {
                    jformVatNumber.data('rule-vies_jform_vat_number', true);
                    $billingCountry.rules('remove', 'country_jform_billing_country_id');
                    if (jformVatNumber.val()) {
                        jformVatNumber.trigger('blur');
                    }
                } else {
                    jformVatNumber.data('rule-vies_jform_vat_number', false);
                    $billingCountry.rules('add', 'country_jform_billing_country_id');
                    $('.waitVies').remove();
                }
            }
        },

        checkShippingCountry: function () {
            var selectedCountryId = $('#jform_shipping_country_id').find('option:selected');
            if (selectedCountryId.length) {
                var hasState = selectedCountryId.data('has_state');
                if (hasState === 1) {
                    $('.shippingStateGroup').removeClass('hide');
                } else {
                    $('.shippingStateGroup').addClass('hide');
                }
            }
        },

        checkUseBilling: function () {
            if ($('#jform_usebilling').is(':checked')) {
                $('#shippingaddress :input').attr('disabled', true);
                $('#jform_shipping_country_id, #jform_shipping_state_id').prop('disabled', true).trigger('liszt:updated');
            } else {
                $('#shippingaddress :input').attr('disabled', false);
                $('#jform_shipping_country_id, #jform_shipping_state_id').prop('disabled', false).trigger('liszt:updated');
            }
        },

        submitForm: function (task) {
            var registerForm = document.getElementById(redSHOPB.register.formId);

            if (document.formvalidator.isValid(registerForm)) {
                Joomla.submitform(task, registerForm);
            }
        }
    };
})(jQuery);

function toggleRequire (elem, isRequired) {
    if (isRequired) {
        elem.attr('required', '');
        elem.attr('aria-required', 'true');
        elem.addClass('required');
    } else {
        elem.removeAttr('required');
        elem.removeAttr('aria-required');
        elem.removeClass('required');
    }
}
