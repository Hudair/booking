jQuery(function ($) {
    $(document.body).on('service.initForm', {},
        // Bind an event handler to the components for service panel.
        function (event, $panel, service_id) {
            let containers = {
                    advanced: $('#bookly-services-advanced-container', $panel),
                    wc: $('#bookly-services-wc-container', $panel),
                },
                $wcSettings = $('#bookly-js-wc-settings', containers.wc),
                $wcInfo = $('[name="wc_cart_info"]', $panel),
                $wcProduct = $('#bookly_wc_product', $panel),
                wcEditor = $('#bookly_wc_cart_info').booklyAceEditor(),
                $modalContent = $panel.closest('.modal-content'),
                $serviceError = $('.bookly-js-service-error', $modalContent),
                $saveButton = $('#bookly-save', $modalContent)
            ;

            // Advanced tab
            $('.bookly-js-frequencies', containers.advanced).booklyDropdown();

            containers.advanced.off()
                .on('change', '[name="recurrence_enabled"]', function () {
                    $('.bookly-js-frequencies', containers.advanced).closest('.form-group').toggle(this.value != '0');
                    checkRepeatError(containers.advanced);
                })
                .on('change', '.bookly-js-frequencies input[type="checkbox"]', function () {
                    checkRepeatError(containers.advanced);
                })
                .on('change', '[name=limit_period]', function () {
                    $('[name=appointments_limit]', containers.advanced).closest('.form-group').toggle(this.value !== 'off');
                })
                .on('keyup change', '.bookly-js-capacity', function () {
                    checkCapacityError(containers.advanced);
                })
                .on('change', '[name="bookly_services_final_step_url_mode"]', function () {
                    let $finalStepUrl = $('.bookly-js-final-step-url', containers.advanced);
                    if (this.value == 0) {
                        $finalStepUrl.hide().find('input').val('');
                    } else {
                        $finalStepUrl.show();
                    }
                });

            function checkRepeatError($panel) {
                if ($('[name="recurrence_enabled"]:checked', containers.advanced).val() == 1 && $('[name="recurrence_frequencies[]"]:checked', containers.advanced).length == 0) {
                    $('[name="recurrence_enabled"]', containers.advanced).addClass('is-invalid');
                    $('.bookly-js-frequencies', containers.advanced).closest('.form-group').find('button.dropdown-toggle').addClass('btn-danger').removeClass('btn-default');
                    $('.bookly-js-recurrence-error', $serviceError).remove();
                    $serviceError.append('<div class="bookly-js-recurrence-error bookly-js-error">' + BooklyProL10nServiceEditDialog.recurrence_error + '</div>');
                } else {
                    $('[name="recurrence_enabled"]', containers.advanced).removeClass('is-invalid');
                    $('.bookly-js-frequencies', containers.advanced).closest('.form-group').find('button.dropdown-toggle').removeClass('btn-danger').addClass('btn-default');
                    $('.bookly-js-recurrence-error', $serviceError).remove();
                }
                $saveButton.prop('disabled', $('.bookly-js-error', $serviceError).length > 0);
            }

            function checkCapacityError($panel) {
                if (parseInt($('[name="capacity_min"]', containers.advanced).val()) > parseInt($('[name="capacity_max"]', containers.advanced).val())) {
                    $('.bookly-js-capacity-error', $serviceError).remove();
                    $serviceError.append('<div class="bookly-js-capacity-error bookly-js-error">' + BooklyProL10nServiceEditDialog.capacity_error + '</div>');
                    $('.bookly-js-capacity', containers.advanced).addClass('is-invalid');
                } else if (!(parseInt($('[name="capacity_min"]', containers.advanced).val()) > 0)) {
                    $('.bookly-js-capacity-error', $serviceError).remove();
                    $serviceError.append('<div class="bookly-js-capacity-error bookly-js-error"></div>');
                    $('.bookly-js-capacity', containers.advanced).addClass('is-invalid');
                } else {
                    $('.bookly-js-capacity-error', $serviceError).remove();
                    $('.bookly-js-capacity', containers.advanced).removeClass('is-invalid');
                }
                $saveButton.prop('disabled', $serviceError.find('.bookly-js-error').length > 0);
            }

            // Woocommerce tab
            $wcInfo.data('default', $wcInfo.val());
            wcEditor.booklyAceEditor('onChange', function () {
                $wcInfo.val(wcEditor.booklyAceEditor('getValue'));
            });

            $('#bookly_settings_woo_commerce button[type="reset"]')
                .on('click', function () {
                    wcEditor.booklyAceEditor('setValue', $wcInfo.data('default'));
                });

            $wcProduct
                .on('change', function () {
                    $wcSettings.toggle(this.value != 0);
                }).trigger('change');
        }
    )
});