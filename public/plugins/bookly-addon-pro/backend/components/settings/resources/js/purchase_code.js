jQuery(function ($) {
    $('.bookly-js-detach-pc').on('click', function (e) {
        e.preventDefault();
        if (confirm(PurchaseCodeL10n.confirmDetach)) {
            let $this = $(this),
                $input = $this.closest('.form-group').find('input'),
                name = $input.prop('id')
            ;
            $input.prop('disabled', true);
            $.post(ajaxurl, {
                action: 'bookly_pro_detach_purchase_code',
                csrf_token: BooklyL10nGlobal.csrf_token,
                blog_id: $input.data('blog_id'),
                name: name
            }, function (response) {
                $input.prop('disabled', false);
                if (response.success) {
                    $input.val('');
                    $this.closest('small').remove();
                }
                booklyAlert(response.data.alert);
            });
        }
    });
});