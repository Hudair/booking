jQuery(function ($) {
    'use strict';
    let $logout = $('#bookly-logout');

    // Logout button from panel.
    $logout.on('click', function () {
        let ladda = Ladda.create(this);
        ladda.start();
        $.ajax({
            method: 'POST',
            url: ajaxurl,
            data: {
                action: 'bookly_cloud_logout',
                csrf_token: BooklyCloudPanelL10n.csrfToken,
            },
            dataType: 'json',
            success: function () {
                window.location = BooklyCloudPanelL10n.productsUrl;
            }
        });
    });
});