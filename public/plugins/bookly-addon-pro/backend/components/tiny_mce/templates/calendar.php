<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Config;
use Bookly\Lib\Utils;
/** @var array $casest */
?>
<div id="bookly-tinymce-calendar" style="display: none">
    <form id="bookly-short-code-form">
        <table>
            <?php if ( Config::locationsActive() ) : ?>
                <tr>
                    <td>
                        <label for="bookly-select-location"><?php esc_html_e( 'Location', 'bookly' ) ?></label>
                    </td>
                    <td>
                        <select id="bookly-select-location" class="form-control custom-select">
                            <option value=""><?php esc_html_e( 'All', 'bookly' ) ?></option>
                        </select>
                    </td>
                </tr>
            <?php endif ?>
            <tr>
                <td>
                    <label for="bookly-select-staff"><?php esc_html_e( 'Staff', 'bookly' ) ?></label>
                </td>
                <td>
                    <select id="bookly-select-staff" class="form-control custom-select">
                        <option value=""><?php esc_html_e( 'All', 'bookly' ) ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="bookly-select-service"><?php esc_html_e( 'Service', 'bookly' ) ?></label>
                </td>
                <td>
                    <select id="bookly-select-service" class="form-control custom-select">
                        <option value=""><?php esc_html_e( 'All', 'bookly' ) ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <span class="dashicons dashicons-info-outline"></span>
                    <span><?php printf( __( 'Check status of this option in Settings > Calendar > <a href="%s" target="_blank"/>Display front-end calendar</a>', 'bookly' ), Utils\Common::escAdminUrl( \Bookly\Backend\Modules\Settings\Page::pageSlug(), array( 'tab' => 'calendar#bookly_cal_frontend_enabled' ) ) ) ?></span>
                </td>
            </tr>
            <tr>
                <td></td>
                <td class="wp-core-ui">
                    <button class="button button-primary bookly-js-insert-shortcode" type="button"><?php esc_html_e( 'Insert', 'bookly' ) ?></button>
                </td>
            </tr>
        </table>
    </form>
</div>
<script type='text/javascript'>
    jQuery(function ($) {
        let casest = <?php echo json_encode( $casest ) ?>,
            locationCustom = <?php echo (int) \Bookly\Lib\Proxy\Locations::servicesPerLocationAllowed() ?>,
            $form = $('#bookly-tinymce-calendar form'),
            $insert = $('button.bookly-js-insert-shortcode', $form),
            $select_location = $('#bookly-select-location', $form),
            $select_service = $('#bookly-select-service', $form),
            $select_employee = $('#bookly-select-staff', $form)
        ;

        function setSelect($select, data, value) {
            // reset select
            $('option:not([value=""])', $select).remove();
            // and fill the new data
            let docFragment = document.createDocumentFragment();

            function valuesToArray(obj) {
                return Object.keys(obj).map(function (key) {
                    return obj[key];
                });
            }

            function compare(a, b) {
                if (parseInt(a.pos) < parseInt(b.pos))
                    return -1;
                if (parseInt(a.pos) > parseInt(b.pos))
                    return 1;
                return 0;
            }

            // sort select by position
            data = valuesToArray(data).sort(compare);

            $.each(data, function (key, object) {
                let option = document.createElement('option');
                option.value = object.id;
                option.text = object.name;
                docFragment.appendChild(option);
            });
            $select.append(docFragment);
            // set default value of select
            $select.val(value);
        }

        function setSelects(location_id, service_id, staff_id) {
            let _location_id = (locationCustom == 1 && location_id) ? location_id : 0,
                _staff = {},
                _services = {}
            ;
            $.each(casest.staff, function (id, staff_member) {
                if (!location_id || casest.locations[location_id].staff.hasOwnProperty(id)) {
                    if (!service_id) {
                        $.each(staff_member.services, function (s_id) {
                            _staff[id] = staff_member;
                            return false;
                        });
                    } else if (staff_member.services.hasOwnProperty(service_id)) {
                        if (staff_member.services[service_id].locations.hasOwnProperty(_location_id)) {
                            if (staff_member.services[service_id].locations[_location_id].price != null) {
                                _staff[id] = {
                                    id: id,
                                    name: staff_member.name,
                                    pos: staff_member.pos
                                };
                            } else {
                                _staff[id] = {
                                    id: id,
                                    name: staff_member.name,
                                    pos: staff_member.pos
                                };
                            }
                        }
                    }
                }
            });
            if (!location_id) {
                $.each(casest.services, function (id, service) {
                    if (!staff_id || casest.staff[staff_id].services.hasOwnProperty(id)) {
                        _services[id] = service;
                    }
                });
            } else {
                let service_ids = [];
                $.each(casest.staff, function (st_id) {
                    $.each(casest.staff[st_id].services, function (s_id) {
                        if (casest.staff[st_id].services[s_id].locations.hasOwnProperty(_location_id)) {
                            service_ids.push(s_id);
                        }
                    });
                });
                $.each(casest.services, function (id, service) {
                    if ($.inArray(id, service_ids) > -1) {
                        if (!staff_id || casest.staff[staff_id].services.hasOwnProperty(id)) {
                            _services[id] = service;
                        }
                    }
                });
            }

            setSelect($select_service, _services, service_id);
            setSelect($select_employee, _staff, staff_id);
        }

        // Location select change
        $select_location.on('change', function () {
            let location_id = this.value,
                service_id = $select_service.val() || '',
                staff_id = $select_employee.val() || ''
            ;

            // Validate selected values.
            if (location_id != '') {
                if (staff_id != '' && !casest.locations[location_id].staff.hasOwnProperty(staff_id)) {
                    staff_id = '';
                }
                if (service_id != '') {
                    let valid = false;
                    $.each(casest.locations[location_id].staff, function (id) {
                        if (casest.staff[id].services.hasOwnProperty(service_id)) {
                            valid = true;
                            return false;
                        }
                    });
                    if (!valid) {
                        service_id = '';
                    }
                }
            }
            setSelects(location_id, service_id, staff_id);
        });
        // Service select change
        $select_service.on('change', function () {
            let location_id = $select_location.val() || '',
                service_id = this.value,
                staff_id = $select_employee.val() || ''
            ;

            // Validate selected values.
            if (service_id != '') {
                if (staff_id != '' && !casest.staff[staff_id].services.hasOwnProperty(service_id)) {
                    staff_id = '';
                }
            }
            setSelects(location_id, service_id, staff_id);
        });

        // Staff select change
        $select_employee.on('change', function () {
            let location_id = $select_location.val() || '',
                service_id = $select_service.val() || '',
                staff_id = this.value
            ;

            setSelects(location_id, service_id, staff_id);
        });
        // Set up draft selects.
        setSelect($select_location, casest.locations);
        setSelect($select_service, casest.services);
        setSelect($select_employee, casest.staff);


        $insert.on('click', function (e) {
            e.preventDefault();

            let shortcode = '[bookly-calendar',
                location_id = $('option:selected', $select_location).val(),
                staff_id = $('option:selected', $select_employee).val(),
                service_id = $('option:selected', $select_service).val()
            ;
            if (location_id) {
                shortcode += ' location_id="' + location_id + '"';
            }
            if (staff_id) {
                shortcode += ' staff_id="' + staff_id + '"';
            }
            if (service_id) {
                shortcode += ' service_id="' + service_id + '"';
            }

            window.send_to_editor(shortcode + ']');
            window.parent.tb_remove();
            return false;
        });
    });
</script>