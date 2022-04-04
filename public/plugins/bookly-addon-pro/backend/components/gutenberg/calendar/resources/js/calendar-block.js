(function (wp, $) {
    let el = wp.element.createElement,
        components = wp.components,
        blockControls = wp.editor.BlockControls,
        inspectorControls = wp.editor.InspectorControls,
        htmlToElem = function (html) {
            return wp.element.RawHTML({children: html});
        }
    ;

    wp.blocks.registerBlockType('bookly/calendar', {
        title: BooklyL10nCalendar.block.title,
        description: BooklyL10nCalendar.block.description,
        icon: el('svg', { width: '20', height: '20', viewBox: "0 0 64 64" },
            el('path', {style: {fill: "rgb(0, 0, 0)"}, d: "M 8 0 H 56 A 8 8 0 0 1 64 8 V 22 H 0 V 8 A 8 8 0 0 1 8 0 Z"}),
            el('path', {style: {fill: "rgb(244, 102, 47)"}, d: "M 0 22 H 64 V 56 A 8 8 0 0 1 56 64 H 8 A 8 8 0 0 1 0 56 V 22 Z"}),
            el('rect', {style: {fill: "rgb(98, 86, 86)"}, x: 6, y: 6, width: 52, height: 10}),
            el('rect', {style: {fill: "rgb(242, 227, 227)"}, x: 12, y: 30, width: 40, height: 24}),
        ),
        category: 'bookly-blocks',
        keywords: [
            'bookly',
            'booking',
        ],
        supports: {
            customClassName: false,
            html: false
        },
        attributes: {
            short_code: {
                type: 'string',
                default: '[bookly-calendar]'
            },
            location:{
                type: 'string',
                default: ''
            },
            service:{
                type: 'string',
                default: ''
            },
            staff:{
                type: 'string',
                default: ''
            }
        },
        edit: function (props) {
            let inspectorElements = [],
                $select_location = $('#bookly-js-select-location'),
                $select_service = $('#bookly-js-select-service'),
                $select_employee = $('#bookly-js-select-staff'),
                attributes = props.attributes,
                staff = BooklyL10nCalendar.casest.staff,
                services = BooklyL10nCalendar.casest.services,
                locations = BooklyL10nCalendar.casest.locations,
                options = []
            ;
            options['locations'] = [{value: '', label: BooklyL10nCalendar.any}];
            options['services'] = [{value: '', label: BooklyL10nCalendar.any}];
            options['staff'] = [{value: '', label: BooklyL10nCalendar.any}];

            function getOptions(data) {
                let options = [];
                data = Object.keys(data).map(function (key) { return data[key]; });

                data.sort(function (a, b) {
                    if (parseInt(a.pos) < parseInt(b.pos))
                        return -1;
                    if (parseInt(a.pos) > parseInt(b.pos))
                        return 1;
                    return 0;
                });

                data.forEach(function (element) {
                    options.push({value: element.id, label: element.name});
                });

                return options;
            }

            function setSelect($select, data, value) {
                // reset select
                $('option:not([value=""])', $select).remove();
                // and fill the new data
                let docFragment = document.createDocumentFragment();

                function valuesToArray(obj) {
                    return Object.keys(obj).map(function (key) { return obj[key]; });
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

                $.each(data, function(key, object) {
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
                let _location_id = (BooklyL10nCalendar.locationCustom == 1 && location_id) ? location_id : 0,
                    _staff = {},
                    _services = {}
                ;
                $.each(BooklyL10nCalendar.casest.staff, function (id, staff_member) {
                    if (!location_id || BooklyL10nCalendar.casest.locations[location_id].staff.hasOwnProperty(id)) {
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
                    $.each(BooklyL10nCalendar.casest.services, function (id, service) {
                        if (!staff_id || BooklyL10nCalendar.casest.staff[staff_id].services.hasOwnProperty(id)) {
                            _services[id] = service;
                        }
                    });
                } else {
                    let service_ids = [];
                    $.each(BooklyL10nCalendar.casest.staff, function (st_id) {
                        $.each(BooklyL10nCalendar.casest.staff[st_id].services, function (s_id) {
                            if (BooklyL10nCalendar.casest.staff[st_id].services[s_id].locations.hasOwnProperty(_location_id)) {
                                service_ids.push(s_id);
                            }
                        });
                    });
                    $.each(BooklyL10nCalendar.casest.services, function (id, service) {
                        if ($.inArray(id, service_ids) > -1) {
                            if (!staff_id || BooklyL10nCalendar.casest.staff[staff_id].services.hasOwnProperty(id)) {
                                _services[id] = service;
                            }
                        }
                    });
                }

                setSelect($select_service, _services, service_id);
                setSelect($select_employee, _staff, staff_id);
            }

            function getShortCode(props, attributes) {
                let short_code = '[bookly-calendar',
                    hide = [];
                if (attributes.location !== '') {
                    short_code += ' location_id="' + attributes.location + '"';
                }
                if (attributes.staff !== '') {
                    short_code += ' staff_id="' + attributes.staff + '"';
                }
                if (attributes.service !== '') {
                    short_code += ' service_id="' + attributes.service + '"';
                }
                short_code += ']';

                props.setAttributes({short_code: short_code});

                return short_code;
            }

            getOptions(services)
                .forEach(function (element) {
                    options['services'].push(element)
                });

            getOptions(staff)
                .forEach(function (element) {
                    options['staff'].push(element)
                });

            // Add Locations
            if (BooklyL10nCalendar.addons.locations == '1') {
                getOptions(locations)
                    .forEach(function (element) {
                        options['locations'].push(element);
                    });
                inspectorElements.push(el(components.SelectControl, {
                    id: 'bookly-js-select-location',
                        label: BooklyL10nCalendar.location,
                        value: attributes.location,
                        options: options.locations,
                        onChange: function (selectControl) {
                            let location_id = selectControl,
                                service_id = $select_service.val() || '',
                                staff_id = $select_employee.val() || ''
                            ;

                            // Validate selected values.
                            if (location_id != '') {
                                if (staff_id != '' && !locations[location_id].staff.hasOwnProperty(staff_id)) {
                                    staff_id = '';
                                }
                                if (service_id != '') {
                                    let valid = false;
                                    $.each(locations[location_id].staff, function(id) {
                                        if (staff[id].services.hasOwnProperty(service_id)) {
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

                            return props.setAttributes({location: selectControl})
                        }
                    }
                ));
            } else {
                props.setAttributes({location: ''});
            }

            // Add service
            inspectorElements.push(el(components.SelectControl, {
                id   : 'bookly-js-select-service',
                label: BooklyL10nCalendar.service,
                value: attributes.service,
                options: options.services,
                onChange: function (selectControl) {
                    let location_id = $select_location.val()||'',
                        service_id  = selectControl,
                        staff_id    = $select_employee.val()||''
                    ;
                    // Validate selected values.
                    if (service_id != '') {
                        if (staff_id != '' && !BooklyL10nCalendar.casest.staff[staff_id].services.hasOwnProperty(service_id)) {
                            staff_id = '';
                        }
                    }
                    setSelects(location_id, service_id, staff_id);
                    return props.setAttributes({service: selectControl})
                }
            }));

            // Add staff
            inspectorElements.push(el(components.SelectControl, {
                id: 'bookly-js-select-staff',
                label: BooklyL10nCalendar.staff,
                value: attributes.staff,
                options: options.staff,
                onChange: function (selectControl) {
                    var location_id = $select_location.val() || '',
                        service_id = $select_service.val() || '',
                        staff_id = selectControl
                     ;

                    setSelects(location_id, service_id, staff_id);
                    return props.setAttributes({staff: selectControl})
                }
            }));

            inspectorElements.push(el('div', {}, htmlToElem(BooklyL10nCalendar.help)));

            return [
                el(blockControls, {key: 'controls'}),
                el(inspectorControls, {key: 'inspector'},
                    el(components.PanelBody, {initialOpen: true},
                        inspectorElements
                    )
                ),
                el('div', {},
                    getShortCode(props, props.attributes)
                )
            ]
        },

        save: function (props) {
            return (
                el('div', {},
                    props.attributes.short_code
                )
            )
        }
    })
})(
  window.wp,
  jQuery
);