jQuery(function ($) {
    let $notice = $('#bookly-lite-rebranding-notice');
    $notice.on('close.bs.alert', function () {
        $.post(ajaxurl, {action: $notice.data('action'), csrf_token : BooklyLiteL10n.csrfToken});
    });
});