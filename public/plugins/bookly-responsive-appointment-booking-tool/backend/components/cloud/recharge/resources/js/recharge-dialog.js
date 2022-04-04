jQuery(function($) {
    'use strict';

    let $modal = $('#bookly-js-recharge-modal'),
        $back  = $('.bookly-js-back', $modal),
        slides = {
            amounts: $('#bookly-recharge-amounts', $modal),
            payment: $('#bookly-recharge-payment', $modal).hide(),
            accepted: $('#bookly-recharge-accepted', $modal).hide(),
            cancelled: $('#bookly-recharge-cancelled', $modal).hide(),
        },
        $rechargeModalActivator = $('.bookly-js-recharge-dialog-activator'),
        payment = {type: '', data: {}},
        $recharge = $('[data-recharge-manual]'),
        hash = window.location.href.split('#')
    ;

    let $amounts = $('.bookly-js-amount', slides.payment),
        $pay = $('.bookly-js-pay', slides.payment);

    $(document.body).on('bookly.recharge.choice', {},
        function (event, type, recharge) {
            $modal.booklyModal('show');
            payment.type = type;
            payment.recharge = recharge;
            $amounts.html(recharge.amount);
            showSlide('payment');
        }
    );

    $rechargeModalActivator.on('click', function () {
        $modal.booklyModal();
        $back.trigger('click');
    });

    if (hash.length > 1) {
        let hashObj = {};
        hash[1].split('&').forEach(function (part) {
            var params = part.split('=');
            hashObj[params[0]] = params[1];
        });

        let showModal = false;
        if (hashObj.hasOwnProperty('payment')) {
            if (hashObj.payment === 'cancelled') {
                $('.bookly-js-message', showSlide('cancelled')).html(BooklyRechargeDialogL10n.payment.manual.cancelled);
                showModal = true;
            } else if (hashObj.payment === 'accepted') {
                $('.bookly-js-message', showSlide('accepted')).html(BooklyRechargeDialogL10n.payment.manual.accepted);
                showModal = true;
            }
        } else if (hashObj.hasOwnProperty('auto-recharge')) {
            if (hashObj['auto-recharge'] === 'cancelled') {
                $('.bookly-js-message', showSlide('cancelled')).html(BooklyRechargeDialogL10n.payment.auto.cancelled);
                showModal = true;
            } else if (hashObj['auto-recharge'] === 'enabled') {
                $('.bookly-js-message', showSlide('accepted')).html(BooklyRechargeDialogL10n.payment.auto.enabled);
                showModal = true;
            }
        } else if (hashObj.hasOwnProperty('recharge')) {
            window.location.href = '#';
            $rechargeModalActivator.trigger('click');
        } else if (hashObj.hasOwnProperty('notifications-settings')) {
            window.location.href = '#';
            $('#bookly-open-account-settings').trigger('click');
            $('[href="#bookly-account-notifications-tab"]').click();
        }
        if (showModal) {
            window.location.href = '#';
            $modal.booklyModal('show');
            setTimeout(function () {
                if (slides.accepted.css('display') === 'block' || slides.cancelled.css('display') === 'block') {
                    $modal.booklyModal('hide');
                }
            }, 5000);
        }

    }

    $recharge.on('click', function () {
        $(document.body).trigger('bookly.recharge.choice', ['manual', $(this).data('recharge-manual')]);
    });

    $back.on('click', function () {
        showSlide('amounts');
    });

    $pay.on('click', function () {
        switch ($(this).data('gateway')) {
            case 'paypal':
                if (payment.type === 'manual') {
                    payManualPayPal(this);
                } else if (payment.type === 'auto') {
                    payAutoPayPal(this);
                }
                break;
            case 'card':
                stripe(this);
                break;
        }
    });

    function showSlide(slide) {
        $.each(slides, function () {
            this.hide();
        });

        if (slide === 'payment') {
            // Hide card payment for disabled countries
            slides.payment.find('#bookly-pay-card').toggle(
                !!BooklyRechargeDialogL10n.country &&
                !BooklyRechargeDialogL10n.no_card.includes(BooklyRechargeDialogL10n.country)
            );
            slides.payment.find('.bookly-js-action').text(BooklyRechargeDialogL10n.payment[payment.type].action);
        }

        return slides[slide].show();
    }

    function payManualPayPal(btn) {
        const ladda = Ladda.create(btn);
        ladda.start();

        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'bookly_create_paypal_order',
                url: document.URL.split('#')[0],
                csrf_token: BooklyRechargeDialogL10n.csrfToken,
                recharge: payment.recharge.id,
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.replace(response.data.order_url);
                } else {
                    $modal.booklyModal('hide');
                    ladda.stop();
                    booklyAlert({error: [response.data.message]});
                }
            }
        });
    }

    function payAutoPayPal(btn) {
        const ladda = Ladda.create(btn);
        ladda.start();

        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'bookly_init_auto_recharge_paypal',
                url: document.URL.split('#')[0],
                csrf_token: BooklyRechargeDialogL10n.csrfToken,
                recharge: payment.recharge.id
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.replace(response.data.paypal_preapproval);
                } else {
                    $modal.booklyModal('hide');
                    ladda.stop();
                    booklyAlert({error: [response.data.message]});
                }
            }
        });
    }

    /**
     * Pay with card via Stripe
     *
     * @param btn
     */
    function stripe(btn) {
        const ladda = Ladda.create(btn);
        ladda.start();

        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'bookly_create_stripe_checkout_session',
                csrf_token: BooklyRechargeDialogL10n.csrfToken,
                recharge: payment.recharge.id,
                mode: payment.type === 'manual' ? 'payment' : 'setup',
                url: document.URL.split('#')[0],
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    window.location.href = response.url;
                } else {
                    ladda.stop();
                    booklyAlert({error: [response.data.message]});
                }
            }
        });
    }
});