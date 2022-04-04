jQuery(function ($) {
    let container = {
            $calendar: $('#bookly_settings_calendar'),
            $log: $('#bookly_settings_logs')
        },
        $helpBtn = $('#bookly-help-btn'),
        $businessHours = $('#business-hours'),
        $companyLogo = $('#bookly-js-company-logo'),
        $finalStepUrl = $('.bookly-js-final-step-url'),
        $finalStepUrlMode = $('#bookly_settings_final_step_url_mode'),
        $participants = $('#bookly_appointment_participants'),
        $defaultCountry = $('#bookly_cst_phone_default_country'),
        $defaultCountryCode = $('#bookly_cst_default_country_code'),
        $gcSyncMode = $('#bookly_gc_sync_mode'),
        $gcLimitEvents = $('#bookly_gc_limit_events'),
        $gcFullSyncOffset = $('#bookly_gc_full_sync_offset_days_before'),
        $gcFullSyncTitles = $('#bookly_gc_full_sync_titles'),
        $gcForceUpdateDescription = $('#bookly_gc_force_update_description'),
        $ocSyncMode = $('#bookly_oc_sync_mode'),
        $ocLimitEvents = $('#bookly_oc_limit_events'),
        $ocFullSyncOffset = $('#bookly_oc_full_sync_offset_days_before'),
        $ocFullSyncTitles = $('#bookly_oc_full_sync_titles'),
        $currency = $('#bookly_pmt_currency'),
        $formats = $('#bookly_pmt_price_format'),
        $logsDateFilter = $('#bookly-logs-date-filter', container.$log),
        $logsTable = $('#bookly-logs-table', container.$log),
        $logsSearch = $('#bookly-log-search', container.$log),
        $calOneParticipant = $('[name="bookly_cal_one_participant"]'),
        $calManyParticipants = $('[name="bookly_cal_many_participants"]'),
        $woocommerceInfo = $('[name="bookly_l10n_wc_cart_info_value"]'),
        $customerAddress = $('[name="bookly_l10n_cst_address_template"]'),
        $gcDescription = $('[name="bookly_gc_event_description"]'),
        $ocDescription = $('[name="bookly_oc_event_description"]'),
        $displayedTimeSlots = $('#bookly_appointments_displayed_time_slots'),
        $timeDelimiter = $('#bookly_appointments_time_delimiter'),
        $colorPicker = $('.bookly-js-color-picker', container.$calendar),
        $coloringMode = $('#bookly_cal_coloring_mode', container.$calendar),
        $colorsBy = $('.bookly-js-colors-by', container.$calendar)
    ;

    booklyAlert(BooklyL10n.alert);

    Ladda.bind('button[type=submit]', {timeout: 2000});

    // Appointments tab.
    $displayedTimeSlots.change(function () {
        if (this.value == 'all') {
            $timeDelimiter.closest('.form-group').show();
        } else {
            $timeDelimiter.closest('.form-group').hide();
        }
    }).trigger('change');

    // Customers tab.
    $.each($.fn.intlTelInput.getCountryData(), function (index, value) {
        $defaultCountry.append('<option value="' + value.iso2 + '" data-code="' + value.dialCode + '">' + value.name + ' +' + value.dialCode + '</option>');
    });
    $defaultCountry.val(BooklyL10n.default_country);
    $defaultCountry.on('change', function () {
        $defaultCountryCode.val($defaultCountry.find('option:selected').data('code'));
    });
    let $sortableAddress = $('#bookly_cst_address_show_fields');
    if ($sortableAddress.length) {
        Sortable.create($sortableAddress[0], {
            handle: '.bookly-js-draghandle'
        });
    }
    $('#bookly-customer-reset').on('click', function (event) {
        $defaultCountry.val($defaultCountry.data('country'));
    });

    $customerAddress.data('default', $calOneParticipant.val());
    let customerAddressEditor = $('#bookly-settings-customers-editor').booklyAceEditor();
    customerAddressEditor.booklyAceEditor('onChange', function () {
        $customerAddress.val(customerAddressEditor.booklyAceEditor('getValue'));
    });

    $('#bookly_settings_customers button[type="reset"]').on('click', function () {
        customerAddressEditor.booklyAceEditor('setValue', $customerAddress.data('default'));
    });

    // Google Calendar tab.
    $gcSyncMode.on('change', function () {
        $gcLimitEvents.closest('.form-group').toggle(this.value == '1.5-way');
        $gcFullSyncOffset.closest('.form-group').toggle(this.value == '2-way');
        $gcFullSyncTitles.closest('.form-group').toggle(this.value == '2-way');
        $gcForceUpdateDescription.closest('.form-group').toggle(this.value == '2-way');
    }).trigger('change');

    $gcDescription.data('default', $gcDescription.val());
    let gcDescriptionEditor = $('#bookly_gc_event_description').booklyAceEditor();
    gcDescriptionEditor.booklyAceEditor('onChange', function () {
        $gcDescription.val(gcDescriptionEditor.booklyAceEditor('getValue'));
    });

    $('#bookly_settings_google_calendar button[type="reset"]').on('click', function () {
        gcDescriptionEditor.booklyAceEditor('setValue', $gcDescription.data('default'));
    });

    // Outlook Calendar tab.
    $ocSyncMode.on('change', function () {
        $ocLimitEvents.closest('.form-group').toggle(this.value == '1.5-way');
        $ocFullSyncOffset.closest('.form-group').toggle(this.value == '2-way');
        $ocFullSyncTitles.closest('.form-group').toggle(this.value == '2-way');
    }).trigger('change');

    $ocDescription.data('default', $ocDescription.val());
    let ocDescriptionEditor = $('#bookly_oc_event_description').booklyAceEditor();
    ocDescriptionEditor.booklyAceEditor('onChange', function () {
        $ocDescription.val(ocDescriptionEditor.booklyAceEditor('getValue'));
    });

    $('#bookly_settings_outlook_calendar button[type="reset"]').on('click', function () {
        ocDescriptionEditor.booklyAceEditor('setValue', $ocDescription.data('default'));
    });

    // Calendar tab.
    $participants.on('change', function () {
        $('#bookly_cal_one_participant').hide();
        $('#bookly_cal_many_participants').hide();
        $('#' + this.value).show();
    }).trigger('change');
    $('#bookly_settings_calendar button[type=reset]').on('click', function () {
        setTimeout(function () {
            $participants.trigger('change');
        }, 50);
    });

    $calOneParticipant.data('default', $calOneParticipant.val());
    $calManyParticipants.data('default', $calManyParticipants.val());
    let calendarEditorOneParticipant = $('#bookly_cal_editor_one_participant').booklyAceEditor();
    calendarEditorOneParticipant.booklyAceEditor('onChange', function () {
        $calOneParticipant.val(calendarEditorOneParticipant.booklyAceEditor('getValue'));
    });

    let calendarEditorManyParticipants = $('#bookly_cal_editor_many_participants').booklyAceEditor();
    calendarEditorManyParticipants.booklyAceEditor('onChange', function () {
        $calManyParticipants.val(calendarEditorManyParticipants.booklyAceEditor('getValue'));
    });

    $('#bookly_settings_calendar button[type="reset"]').on('click', function () {
        calendarEditorOneParticipant.booklyAceEditor('setValue', $calOneParticipant.data('default'));
        calendarEditorManyParticipants.booklyAceEditor('setValue', $calManyParticipants.data('default'));
    });

    // Woocommerce tab.
    $woocommerceInfo.data('default', $woocommerceInfo.val());
    let woocommerceEditor = $('#bookly_wc_cart_info').booklyAceEditor();
    woocommerceEditor.booklyAceEditor('onChange', function () {
        $woocommerceInfo.val(woocommerceEditor.booklyAceEditor('getValue'));
    });

    $('#bookly_settings_woo_commerce button[type="reset"]').on('click', function () {
        woocommerceEditor.booklyAceEditor('setValue', $woocommerceInfo.data('default'));
    });

    // Company tab.
    $companyLogo.find('.bookly-js-delete').on('click', function () {
        let $thumb = $companyLogo.find('.bookly-js-image');
        $thumb.attr('style', '');
        $companyLogo.find('[name=bookly_co_logo_attachment_id]').val('');
        $companyLogo.find('.bookly-thumb').removeClass('bookly-thumb-with-image');
        $(this).hide();
    });
    $companyLogo.find('.bookly-js-edit').on('click', function () {
        let frame = wp.media({
            library: {type: 'image'},
            multiple: false
        });
        frame.on('select', function () {
            let selection = frame.state().get('selection').toJSON(),
                img_src
            ;
            if (selection.length) {
                if (selection[0].sizes['thumbnail'] !== undefined) {
                    img_src = selection[0].sizes['thumbnail'].url;
                } else {
                    img_src = selection[0].url;
                }
                $companyLogo.find('[name=bookly_co_logo_attachment_id]').val(selection[0].id);
                $companyLogo.find('.bookly-js-image').css({'background-image': 'url(' + img_src + ')', 'background-size': 'cover'});
                $companyLogo.find('.bookly-js-delete').show();
                $companyLogo.find('.bookly-thumb').addClass('bookly-thumb-with-image');
                $(this).hide();
            }
        });

        frame.open();
    });
    $('#bookly-company-reset').on('click', function () {
        var $div = $('#bookly-js-company-logo .bookly-js-image'),
            $input = $('[name=bookly_co_logo_attachment_id]');
        $div.attr('style', $div.data('style'));
        $input.val($input.data('default'));
    });

    // Cart tab.
    let $sortableCart = $('#bookly_cart_show_columns');
    if ($sortableCart.length) {
        Sortable.create($sortableCart[0], {
            handle: '.bookly-js-draghandle'
        });
    }
    // Payments tab.
    Sortable.create($('#bookly-payment-systems')[0], {
        handle: '.bookly-js-draghandle',
        onChange: function () {
            let order = [];
            $('#bookly_settings_payments .card[data-slug]').each(function () {
                order.push($(this).data('slug'));
            });
            $('#bookly_settings_payments [name="bookly_pmt_order"]').val(order.join(','));
        },
    });
    $currency.on('change', function () {
        $formats.find('option').each(function () {
            var decimals = this.value.match(/{price\|(\d)}/)[1],
                price = BooklyL10n.sample_price
            ;
            if (decimals < 3) {
                price = price.slice(0, -(decimals == 0 ? 4 : 3 - decimals));
            }
            var html = this.value
                .replace('{sign}', '')
                .replace('{symbol}', $currency.find('option:selected').data('symbol'))
                .replace(/{price\|\d}/, price)
            ;
            html += ' (' + this.value
                .replace('{sign}', '-')
                .replace('{symbol}', $currency.find('option:selected').data('symbol'))
                .replace(/{price\|\d}/, price) + ')'
            ;
            this.innerHTML = html;
        });
    }).trigger('change');

    $('#bookly_paypal_enabled').change(function () {
        $('.bookly-paypal-ec').toggle(this.value == 'ec');
        $('.bookly-paypal-ps').toggle(this.value == 'ps');
        $('.bookly-paypal-checkout').toggle(this.value == 'checkout');
        $('.bookly-paypal').toggle(this.value != '0');
        $('#bookly_paypal_timeout').closest('.form-group').toggle(this.value != 'ec');
    }).change();

    $('#bookly_authorize_net_enabled').change(function () {
        $('.bookly-authorize-net').toggle(this.value != '0');
    }).change();

    $('#bookly_stripe_enabled').change(function () {
        $('.bookly-stripe').toggle(this.value == 1);
    }).change();

    $('#bookly_2checkout_enabled').change(function () {
        $('.bookly-2checkout').toggle(this.value != '0');
    }).change();

    $('#bookly_payu_biz_enabled').change(function () {
        $('.bookly-payu_biz').toggle(this.value != '0');
    }).change();

    $('#bookly_payu_latam_enabled').change(function () {
        $('.bookly-payu_latam').toggle(this.value != '0');
    }).change();

    $('#bookly_payson_enabled').change(function () {
        $('.bookly-payson').toggle(this.value != '0');
    }).change();

    $('#bookly_mollie_enabled').change(function () {
        $('.bookly-mollie').toggle(this.value != '0');
    }).change();

    $('#bookly_payu_biz_sandbox').change(function () {
        var live = this.value != 1;
        $('.bookly-payu_biz > .form-group:eq(1)').toggle(live);
        $('.bookly-payu_biz > .form-group:eq(2)').toggle(live);
    }).change();

    $('#bookly_payu_latam_sandbox').change(function () {
        var live = this.value != 1;
        $('.bookly-payu_latam > .form-group:eq(1)').toggle(live);
        $('.bookly-payu_latam > .form-group:eq(2)').toggle(live);
        $('.bookly-payu_latam > .form-group:eq(3)').toggle(live);
    }).change();

    $('#bookly_cloud_stripe_enabled').change(function () {
        $('.bookly-cloud_stripe').toggle(this.value != '0');
    }).change();

    $('#bookly-payments-reset').on('click', function (event) {
        setTimeout(function () {
            $('#bookly_pmt_currency,#bookly_paypal_enabled,#bookly_authorize_net_enabled,#bookly_stripe_enabled,#bookly_2checkout_enabled,#bookly_payu_biz_enabled,#bookly_payu_latam_enabled,#bookly_payson_enabled,#bookly_mollie_enabled,#bookly_payu_biz_sandbox,#bookly_payu_latam_sandbox,#bookly_cloud_stripe_enabled').change();
        }, 0);
    });

    // URL tab.
    if ($finalStepUrl.find('input').val()) {
        $finalStepUrlMode.val(1);
    }
    $finalStepUrlMode.change(function () {
        if (this.value == 0) {
            $finalStepUrl.hide().find('input').val('');
        } else {
            $finalStepUrl.show();
        }
    });

    // Holidays Tab.
    var d = new Date();
    $('.bookly-js-annual-calendar').jCal({
        day: new Date(d.getFullYear(), 0, 1),
        days: 1,
        showMonths: 12,
        scrollSpeed: 350,
        events: BooklyL10n.holidays,
        action: 'bookly_settings_holiday',
        csrf_token: BooklyL10nGlobal.csrf_token,
        dayOffset: parseInt(BooklyL10n.firstDay),
        loadingImg: BooklyL10n.loading_img,
        dow: BooklyL10n.days,
        ml: BooklyL10n.months,
        we_are_not_working: BooklyL10n.we_are_not_working,
        repeat: BooklyL10n.repeat,
        close: BooklyL10n.close
    });
    $('.bookly-js-jCalBtn').on('click', function (e) {
        e.preventDefault();
        var trigger = $(this).data('trigger');
        $('.bookly-js-annual-calendar').find($(trigger)).trigger('click');
    });

    // Business Hours tab.
    $('.bookly-js-parent-range-start', $businessHours)
        .on('change', function () {
            var $parentRangeStart = $(this),
                $rangeRow = $parentRangeStart.parents('.bookly-js-range-row');
            if ($parentRangeStart.val() == '') {
                $('.bookly-js-invisible-on-off', $rangeRow).addClass('invisible');
            } else {
                $('.bookly-js-invisible-on-off', $rangeRow).removeClass('invisible');
                rangeTools.hideInaccessibleEndTime($parentRangeStart, $('.bookly-js-parent-range-end', $rangeRow));
            }
        }).trigger('change');
    // Reset.
    $('#bookly-hours-reset', $businessHours).on('click', function () {
        $('.bookly-js-parent-range-start', $businessHours).each(function () {
            $(this).val($(this).data('default_value')).trigger('change');
        });
    });

    // Change link to Help page according to activated tab.
    let help_link = $helpBtn.attr('href');
    $('#bookly-sidebar a[data-toggle="bookly-pill"]').on('shown.bs.tab', function (e) {
        $helpBtn.attr('href', help_link + e.target.getAttribute('href').substring(1).replace(/_/g, '-'));
    });

    // Activate tab.
    $('a[href="#bookly_settings_' + BooklyL10n.current_tab + '"]').booklyTab('show');

    // Logs
    $('#bookly_logs_enabled').on('change', function () {
        $('#bookly-logs-table-wrap', container.$log).toggle($(this).find('option:selected').val() === '1');
    }).trigger('change');

    $('#bookly-delete-logs').on('click', function () {
        if (confirm(BooklyL10n.are_you_sure)) {
            var ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookly_delete_logs',
                    csrf_token: BooklyL10nGlobal.csrf_token,
                },
                dataType: 'json',
                success: function () {
                    ladda.stop();
                    dt.ajax.reload();
                }
            });
        }
    });

    let pickers = {
        dateFormat: 'YYYY-MM-DD',
        creationDate: {
            startDate: moment().subtract(30, 'days'),
            endDate: moment(),
        },
    };
    var picker_ranges = {};
    picker_ranges[BooklyL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    picker_ranges[BooklyL10n.dateRange.today] = [moment(), moment()];
    picker_ranges[BooklyL10n.dateRange.last_7] = [moment().subtract(7, 'days'), moment()];
    picker_ranges[BooklyL10n.dateRange.last_30] = [moment().subtract(30, 'days'), moment()];
    picker_ranges[BooklyL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
    picker_ranges[BooklyL10n.dateRange.lastMonth] = [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')];

    $logsDateFilter.daterangepicker({
            parentEl: $logsDateFilter.closest('.card-body'),
            startDate: pickers.creationDate.startDate,
            endDate: pickers.creationDate.endDate,
            ranges: picker_ranges,
            showDropdowns: true,
            linkedCalendars: false,
            autoUpdateInput: false,
            locale: $.extend({}, BooklyL10n.dateRange, BooklyL10n.datePicker)
        },
        function (start, end) {
            $logsDateFilter
                .data('date', start.format(pickers.dateFormat) + ' - ' + end.format(pickers.dateFormat))
                .find('span')
                .html(start.format(BooklyL10n.dateRange.format) + ' - ' + end.format(BooklyL10n.dateRange.format));
        }
    );

    var dt = $logsTable.DataTable({
        order: [0],
        info: false,
        paging: true,
        searching: false,
        lengthChange: false,
        processing: true,
        responsive: true,
        pageLength: 25,
        pagingType: 'numbers',
        serverSide: true,
        ajax: {
            url: ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({action: 'bookly_get_logs', csrf_token: BooklyL10nGlobal.csrf_token}, {
                    filter: {
                        created_at: $logsDateFilter.data('date'),
                        search: $logsSearch.val()
                    }
                }, d);
            }
        },
        columns: [
            {data: 'created_at'},
            {data: 'action'},
            {data: 'target'},
            {data: 'target_id'},
            {data: 'author'},
            {
                data: 'details',
                render: function (data, type, row, meta) {
                    try {
                        return JSON.stringify(JSON.parse(data), null, 2).replace(/\n/g, '<br/>');
                    } catch (e) {
                        return data;
                    }
                },
                className: 'none'
            },
            {data: 'comment'},
            {data: 'ref', className: 'none'},
        ],
        dom: "<'row'<'col-sm-12'tr>><'row float-left mt-3'<'col-sm-12'p>>",
        language: {
            zeroRecords: BooklyL10n.zeroRecords,
            processing: BooklyL10n.processing
        }
    });

    $logsDateFilter.on('apply.daterangepicker', function () {dt.ajax.reload();});
    $logsSearch.on('keyup', function () {
        dt.search(this.value).draw();
    })
    .on('keydown', function (e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            return false;
        }
    });

    // Tab calendar
    $coloringMode
        .on('change', function () {
            $colorsBy.hide();
            $('.bookly-js-colors-' + this.value).show()
        }).trigger('change');

    initColorPicker($colorPicker);

    function initColorPicker($jquery_collection) {
        $jquery_collection.wpColorPicker();
        $jquery_collection.each(function () {
            $(this).data('last-color', $(this).val());
            $('.wp-color-result-text', $(this).closest('.bookly-color-picker')).text($(this).data('title'));
        });
    }

    $('#bookly-calendar-reset', container.$calendar)
        .on('click', function (event) {
            $colorPicker.each(function () {
                $(this).wpColorPicker('color', $(this).data('last-color'));
            });
            setTimeout(function () {
                $coloringMode.trigger('change')
            }, 0);
        });
});