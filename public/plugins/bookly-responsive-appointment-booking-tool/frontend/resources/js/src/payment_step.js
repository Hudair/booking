import $ from 'jquery';
import {opt, laddaStart, scrollTo, booklyAjax} from './shared.js';
import stepCart from './cart_step.js';
import stepTime from './time_step.js';
import stepDetails from './details_step.js';
import stepComplete from './complete_step.js';

/**
 * Payment step.
 */
export default function stepPayment(params) {
    var $container = opt[params.form_id].$container;
    booklyAjax({
        type: 'POST',
        data: {action: 'bookly_render_payment', csrf_token : BooklyL10n.csrf_token, form_id: params.form_id, page_url: document.URL.split('#')[0]},
        success: function (response) {
            if (response.success) {
                // If payment step is disabled.
                if (response.disabled) {
                    save(params.form_id);
                    return;
                }

                $container.html(response.html);
                scrollTo($container, params.form_id);
                if (opt[params.form_id].status.booking == 'cancelled') {
                    opt[params.form_id].status.booking = 'ok';
                }

                const customJS = response.custom_js;

                // Init stripe intents form
                if ($container.find('#bookly-stripe-card-field').length) {
                    if (response.stripe_publishable_key) {
                        var stripe = Stripe(response.stripe_publishable_key, {
                            betas: ['payment_intent_beta_3']
                        });
                        var elements = stripe.elements();
                        var stripe_card = elements.create("card");

                        stripe_card.mount("#bookly-stripe-card-field");
                    } else {
                        $container.find('.bookly-stripe #bookly-stripe-card-field').hide();
                        $container.find('.pay-card .bookly-js-next-step').prop('disabled', true);
                        $container.find('.bookly-stripe .bookly-js-card-error').text('Please call Stripe() with your publishable key. You used an empty string.');
                    }
                }

                var $payments = $('.bookly-payment', $container),
                    $apply_coupon_button = $('.bookly-js-apply-coupon', $container),
                    $coupon_input = $('input.bookly-user-coupon', $container),
                    $coupon_error = $('.bookly-js-coupon-error', $container),
                    $deposit_mode = $('input[type=radio][name=bookly-full-payment]', $container),
                    $coupon_info_text = $('.bookly-info-text-coupon', $container),
                    $buttons = $('.bookly-gateway-buttons,form.bookly-authorize_net,form.bookly-stripe', $container)
                ;
                $payments.on('click', function() {
                    $buttons.hide();
                    $('.bookly-gateway-buttons.pay-' + $(this).val(), $container).show();
                    if ($(this).val() == 'card') {
                        $('form.bookly-' + $(this).data('form'), $container).show();
                    }
                });
                $payments.eq(0).trigger('click');

                $deposit_mode.on('change', function () {
                    var data = {
                        action: 'bookly_deposit_payments_apply_payment_method',
                        csrf_token: BooklyL10n.csrf_token,
                        form_id: params.form_id,
                        deposit_full: $(this).val()
                    };
                    $(this).hide();
                    $(this).prev().css('display', 'inline-block');
                    booklyAjax({
                        type: 'POST',
                        data: data,
                        success: function (response) {
                            if (response.success) {
                                stepPayment({form_id: params.form_id});
                            }
                        }
                    });
                });

                $apply_coupon_button.on('click', function (e) {
                    var ladda = laddaStart(this);
                    $coupon_error.text('');
                    $coupon_input.removeClass('bookly-error');

                    var data = {
                        action: 'bookly_coupons_apply_coupon',
                        csrf_token: BooklyL10n.csrf_token,
                        form_id: params.form_id,
                        coupon_code: $coupon_input.val()
                    };

                    booklyAjax({
                        type: 'POST',
                        data: data,
                        success: function (response) {
                            if (response.success) {
                                stepPayment({form_id: params.form_id});
                            } else {
                                $coupon_error.html(response.error);
                                $coupon_input.addClass('bookly-error');
                                $coupon_info_text.html(response.text);
                                scrollTo($coupon_error, params.form_id);
                                ladda.stop();
                            }
                        },
                        error: function () {
                            ladda.stop();
                        }
                    });
                });

                $('.bookly-js-next-step', $container).on('click', function (e) {
                    var ladda = laddaStart(this),
                        $form
                    ;

                    // Execute custom JavaScript
                    if (customJS) {
                        try {
                            $.globalEval(customJS.next_button);
                        } catch (e) {
                            // Do nothing
                        }
                    }

                    if ($('.bookly-payment[value=local]', $container).is(':checked') || $(this).hasClass('bookly-js-coupon-payment')) {
                        // handle only if was selected local payment !
                        e.preventDefault();
                        save(params.form_id);

                    } else if ($('.bookly-payment[value=card]', $container).is(':checked')) {
                        if ($('.bookly-payment[data-form=stripe]', $container).is(':checked')) {
                            booklyAjax({
                                type: 'POST',
                                data: {
                                    action: 'bookly_stripe_create_intent',
                                    csrf_token: BooklyL10n.csrf_token,
                                    form_id: params.form_id
                                },
                                success: function (response) {
                                    if (response.success) {
                                        stripe.handleCardPayment(
                                            response.intent_secret,
                                            stripe_card
                                        ).then(function (result) {
                                            if (result.error) {
                                                booklyAjax({
                                                    type: 'POST',
                                                    data: {
                                                        action: 'bookly_stripe_failed_payment',
                                                        csrf_token: BooklyL10n.csrf_token,
                                                        form_id: params.form_id,
                                                        intent_id: response.intent_id
                                                    },
                                                    success    : function (response) {
                                                        if (response.success) {
                                                            ladda.stop();
                                                            $container.find('.bookly-stripe .bookly-js-card-error').text(result.error.message);
                                                        }
                                                    }
                                                });
                                            } else {
                                                stepComplete({form_id: params.form_id});
                                            }
                                        });
                                    } else {
                                        if (response.error === 'cart_item_not_available') {
                                            handleErrorCartItemNotAvailable(response, params.form_id);
                                        }
                                        ladda.stop();
                                        $container.find('.bookly-stripe .bookly-js-card-error').text(response.error_message);
                                    }
                                }
                            });
                        } else {
                            var card_action = 'bookly_authorize_net_aim_payment';
                            $form = $container.find('.bookly-authorize_net');
                            e.preventDefault();

                            var data = {
                                action: card_action,
                                csrf_token: BooklyL10n.csrf_token,
                                card: {
                                    number   : $form.find('input[name="card_number"]').val(),
                                    cvc      : $form.find('input[name="card_cvc"]').val(),
                                    exp_month: $form.find('select[name="card_exp_month"]').val(),
                                    exp_year : $form.find('select[name="card_exp_year"]').val()
                                },
                                form_id   : params.form_id
                            };

                            var cardPayment = function (data) {
                                booklyAjax({
                                    type: 'POST',
                                    data: data,
                                    success: function (response) {
                                        if (response.success) {
                                            stepComplete({form_id: params.form_id});
                                        } else if (response.error == 'cart_item_not_available') {
                                            handleErrorCartItemNotAvailable(response, params.form_id);
                                        } else if (response.error == 'payment_error') {
                                            ladda.stop();
                                            $form.find('.bookly-js-card-error').text(response.error_message);
                                        }
                                    }
                                });
                            };
                            cardPayment(data);
                        }
                    } else if (
                           $('.bookly-payment[value=paypal]',     $container).is(':checked')
                        || $('.bookly-payment[value=2checkout]',  $container).is(':checked')
                        || $('.bookly-payment[value=payu_biz]',   $container).is(':checked')
                        || $('.bookly-payment[value=payu_latam]', $container).is(':checked')
                        || $('.bookly-payment[value=payson]',     $container).is(':checked')
                        || $('.bookly-payment[value=mollie]',     $container).is(':checked')
                        || $('.bookly-payment[value=cloud_stripe]', $container).is(':checked')
                    ) {
                        e.preventDefault();
                        $form = $(this).closest('form');
                        if ($form.find('input.bookly-payment-id').length > 0 ) {
                            booklyAjax({
                                type: 'POST',
                                data: {
                                    action:       'bookly_pro_save_pending_appointment',
                                    csrf_token:   BooklyL10n.csrf_token,
                                    form_id:      params.form_id,
                                    payment_type: $form.data('gateway')
                                },
                                success: function (response) {
                                    if (response.success) {
                                        $form.find('input.bookly-payment-id').val(response.payment_id);
                                        $form.submit();
                                    } else if (response.error == 'cart_item_not_available') {
                                        handleErrorCartItemNotAvailable(response,params.form_id);
                                    }
                                }
                            });
                        } else  {
                            booklyAjax({
                                type: 'POST',
                                data: {action: 'bookly_check_cart', csrf_token : BooklyL10n.csrf_token, form_id: params.form_id},
                                success: function (response) {
                                    if (response.success) {
                                        $form.submit();
                                    } else if (response.error == 'cart_item_not_available') {
                                        handleErrorCartItemNotAvailable(response,params.form_id);
                                    }
                                }
                            });
                        }
                    }
                });

                $('.bookly-js-back-step', $container).on('click', function (e) {
                    e.preventDefault();
                    laddaStart(this);
                    stepDetails({form_id: params.form_id});
                });
            }
        }
    });
}

/**
 * Save appointment.
 */
function save(form_id) {
    booklyAjax({
        type: 'POST',
        data: { action : 'bookly_save_appointment', csrf_token : BooklyL10n.csrf_token, form_id : form_id },
        success: function (response) {
            if (response.success) {
                stepComplete({form_id: form_id});
            } else if (response.error == 'cart_item_not_available') {
                handleErrorCartItemNotAvailable(response, form_id);
            }
        }
    });
}

/**
 * Handle error with code 3 which means one of the cart item is not available anymore.
 *
 * @param response
 * @param form_id
 */
function handleErrorCartItemNotAvailable(response, form_id) {
    if (!opt[form_id].skip_steps.cart) {
        stepCart({form_id: form_id}, {
            failed_key: response.failed_cart_key,
            message: opt[form_id].errors[response.error]
        });
    } else {
        stepTime({form_id: form_id}, opt[form_id].errors[response.error]);
    }
}