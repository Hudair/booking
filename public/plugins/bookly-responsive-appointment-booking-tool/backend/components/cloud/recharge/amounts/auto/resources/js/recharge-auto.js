jQuery(function($) {
    'use strict';

    let $selector = $('.bookly-js-auto-recharge-selector'),
        $disableAutoRechargeModal = $('#bookly-js-disable-auto-recharge-modal'),
        selector = {
            $items: $('[data-recharge-data]', $selector),
            $dropdown: $('.bookly-js-auto-recharge-dropdown', $selector),
            $amount: $('.bookly-js-auto-amount', $selector),
            $bonus: $('.bookly-js-auto-bonus', $selector).hide(),
            $usersChoice: $('.bookly-js-users-choice-auto', $selector).hide(),
            $bestOffer: $('.bookly-js-best-offer-auto', $selector).hide(),
            $enable: $('.bookly-js-auto-recharge-enable', $selector),
            $enabled: $('.bookly-js-auto-recharge-enabled', $selector),
            $disable: $('.bookly-js-auto-confirm-disable', $selector),
            $container: $('#bookly-recharge-amounts .bookly-js-auto-recharge-container'),
        },
        recharge = {};

    selector
        .$items.on('click', function () {
            recharge = $(this).data('recharge-data');
            selector.$amount.html(recharge.amount);
            if (recharge.bonus) {
                selector.$bonus.show();
                $('span', selector.$bonus).html(recharge.bonus);
            } else {
                selector.$bonus.hide();
            }
            selector.$bestOffer.toggle(recharge.tags.includes('best_offer'));
            selector.$usersChoice.toggle(recharge.tags.includes('users_choice'));
        });
    selector
        .$enable.on('click', function() {
            $(document.body).trigger('bookly.recharge.choice', ['auto', recharge]);
        });
    selector
        .$disable.on('click', function () {
            $disableAutoRechargeModal.booklyModal('show');
        });

    $disableAutoRechargeModal
        .on('show.bs.modal', function () {
            $('.bookly-js-amount', $disableAutoRechargeModal).html(parseFloat(BooklyAutoRechargeL10n.auto_recharge.amount));
            if (BooklyAutoRechargeL10n.auto_recharge.bonus) {
                $('.bookly-js-amount', $disableAutoRechargeModal).append(' + ' + BooklyAutoRechargeL10n.auto_recharge.bonus);
            }
        })
        .on('click', '#bookly-js-auto-recharge-disable', function () {
            let ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                method: 'POST',
                url: ajaxurl,
                data: {
                    action: 'bookly_disable_auto_recharge',
                    csrf_token: BooklyAutoRechargeL10n.csrfToken,
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        booklyAlert({success: [response.data.message]});
                        $disableAutoRechargeModal.booklyModal('hide');
                        autoRechageToggle(false);
                    } else {
                        booklyAlert({error: [response.data.message]});
                    }
                    ladda.stop();
                }
            });
        });

    function autoRechageToggle(enabled) {
        selector.$dropdown.prop('disabled', enabled).toggleClass('disabled', enabled);
        selector.$enable.toggle(!enabled);
        selector.$enabled.toggle(enabled);
        selector.$disable.toggle(enabled);
        if (enabled) {
            selector.$amount.html(BooklyAutoRechargeL10n.auto_recharge.amount);
            if (BooklyAutoRechargeL10n.auto_recharge.bonus) {
                selector.$bonus.show();
                $('span', selector.$bonus).html(BooklyAutoRechargeL10n.auto_recharge.bonus);
            } else {
                selector.$bonus.hide();
            }
            selector.$container.hide();
        } else {
            if ($('.bookly-js-best-offer', selector.$items).length > 0) {
                $('.bookly-js-best-offer', selector.$items).trigger('click');
            } else if ($('.bookly-js-users-choice', selector.$items).length > 0) {
                $('.bookly-js-users-choice', selector.$items).trigger('click')
            } else {
                selector.$items.first().trigger('click');
            }
            selector.$container.show();
        }
    }

    autoRechageToggle(BooklyAutoRechargeL10n.auto_recharge.enabled);

    $(document.body).on('bookly.auto-recharge.toggle', {},
        function (event, enabled) {
            autoRechageToggle(enabled);
        }
    );
});