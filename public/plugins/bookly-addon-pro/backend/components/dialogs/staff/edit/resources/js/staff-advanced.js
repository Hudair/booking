(function ($) {

    var Advanced = function ($container, options) {
        let obj = this,
            hash = window.location.href.split('#')
        ;

        jQuery.extend(obj.options, options);

        if (hash.length > 1) {
            if (hash[1] === 'zoom-failed') {
                obj.options.booklyAlert({error: [obj.options.l10n.zoomFailed]});
                if ('pushState' in history) {
                    history.pushState('', document.title, hash[0]);
                }
            }
        }

        let $form = $('form', $container),
            $unsaved_changes = $('.bookly-js-unsaved-changes'),
            $unsaved_changes_save = $('.bookly-js-save-changes', $unsaved_changes),
            $unsaved_changes_ignore = $('.bookly-js-ignore-changes', $unsaved_changes),
            has_changes = false
        ;

        $container
        .on('change', 'select,input,textarea', function () {
            has_changes = true;
        })
        .on('click', '.bookly-js-google-calendar-row a', function (e) {
            var url = $(this).attr('href');
            if (has_changes) {
                e.preventDefault();
                $unsaved_changes.booklyModal('show');
                $unsaved_changes.data('url', url);
            }
        })
        .on('click', '.bookly-js-outlook-calendar-row a', function (e) {
            var url = $(this).attr('href');
            if (has_changes) {
                e.preventDefault();
                $unsaved_changes.booklyModal('show');
                $unsaved_changes.data('url', url);
            }
        })
        .on('change', '[name=google_disconnect]', function () {
            has_changes = true;
            $('.bookly-js-google-calendars-list', $form).toggle(!this.checked);
        }).on('change', '[name=outlook_disconnect]', function () {
            has_changes = true;
            $('.bookly-js-outlook-calendars-list', $form).toggle(!this.checked);
        })
        // Save staff member details.
        .on('click', '#bookly-advanced-save', function (e) {
            e.preventDefault();
            let ladda = Ladda.create(this);
            ladda.start();
            saveAdvanced(function (response) {
                ladda.stop();
                if (response.success) {
                    obj.options.saving({success: [obj.options.l10n.saved]});
                }
            });
        })
        .on('change', '[name="zoom_authentication"]', function () {
            $('.bookly-js-zoom-settings', $form).toggle(false);
            $('.bookly-js-zoom-' + this.value, $form).toggle(true);
            obj.options.validation(this.value === 'oauth' && $('.bookly-js-zoom-disconnected').is(":visible"), obj.options.l10n.zoomOAuthConnectRequired);
        })
        .on('change', '[name="icalendar"]', function () {
            $('#bookly-icalendar-days-offset').toggle(this.value === '1');
        })
        .on('focus', '#bookly-icalendar-url', function () {
            $(this).select();
        })
        .on('click', '[type="reset"]', function (e) {
            $form[0].reset();
            has_changes = false;
            $('[name="zoom_authentication"]:checked', $form).trigger('change');
            $('[name="icalendar"]:checked', $form).trigger('change');
        })
        .on('click', '.bookly-js-zoom_oauth_connect', function (e) {
            e.preventDefault();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookly_pro_get_zoom_authorization_url',
                    staff_id: obj.options.get_staff_advanced.staff_id,
                    csrf_token: BooklyL10nGlobal.csrf_token,
                    layout: obj.options.get_staff_advanced.layout,
                    page_url: document.URL.split('#')[0]
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        window.location.href = response.data.authorization_url;
                    }
                }
            });
        });

        $('.bookly-js-zoom-' + $('[name="zoom_authentication"]', $form).val(), $form).show();

        $unsaved_changes_ignore.off().on('click', function () {
            window.location.href = $unsaved_changes.data('url');
        });
        $unsaved_changes_save.off().on('click', function () {
            let ladda = Ladda.create(this);
            ladda.start();
            saveAdvanced(function (response) {
                if (response.success) {
                    window.location.href = $unsaved_changes.data('url');
                } else {
                    obj.options.saving({error: [response.data.error]});
                }
                ladda.stop();
            });
        });

        function saveAdvanced(callback) {
            let data = $form.serializeArray();
            data.push({name: 'action', value: 'bookly_pro_update_staff_advanced'});
            data.push({name: 'csrf_token', value: BooklyL10nGlobal.csrf_token});
            data.push({name: 'staff_id', value: obj.options.get_staff_advanced.staff_id});
            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: data,
                dataType: 'json',
                xhrFields: {withCredentials: true},
                crossDomain: 'withCredentials' in new XMLHttpRequest(),
                success: function (response) {
                    if (obj.options.get_staff_advanced.layout === 'frontend') {
                        let $disconnected = $('.bookly-js-zoom-connected', $container);
                        if ($('input:checked', $disconnected).length > 0) {
                            $disconnected.hide();
                            $('.bookly-js-zoom-disconnected', $container).show();
                            $('#zoom_authentication').val('default').trigger('change');
                        }
                    }
                    obj.options.booklyAlert(response.data.alerts);
                    has_changes = false;
                    callback(response);
                }
            });
        }
    };

    Advanced.prototype.options = {
        get_staff_advanced: {
            action: 'bookly_pro_get_staff_advanced',
            staff_id: -1,
            layout: 'backend'
        },
        l10n: {},
        booklyAlert: window.booklyAlert,
        validation: function (has_error, info) {
            $(document.body).trigger('staff.validation', ['staff-advanced', has_error, info]);
        },
        saving: function (alerts) {
            $(document.body).trigger('staff.saving', [alerts]);
        },
    };

    window.BooklyStaffAdvanced = Advanced;
})(jQuery);