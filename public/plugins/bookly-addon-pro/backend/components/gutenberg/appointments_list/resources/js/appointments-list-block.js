(function (wp, $) {
    var el = wp.element.createElement,
        components        = wp.components,
        blockControls     = wp.editor.BlockControls,
        inspectorControls = wp.editor.InspectorControls,
        attributes        = {
            short_code: {
                type: 'string',
                default: '[bookly-appointments-list]'
            },
            titles: {
                type: 'boolean',
                default: true
            },
            column_category: {
                type: 'boolean',
                default: false
            },
            column_service: {
                type: 'boolean',
                default: true
            },
            column_staff: {
                type: 'boolean',
                default: false
            },
            column_date: {
                type: 'boolean',
                default: true
            },
            column_time: {
                type: 'boolean',
                default: true
            },
            column_price: {
                type: 'boolean',
                default: false
            },
            column_status: {
                type: 'boolean',
                default: false
            },
            column_online_meeting: {
                type: 'boolean',
                default: false
            },
            column_cancel: {
                type: 'boolean',
                default: false
            },
        }
    ;

    $.each(BooklyAppointmentListL10n.customFields, function (index, field) {
        if(field.type !== 'file') {
            var attr_name = 'cf_' + field.id;
            attributes[attr_name] = {
                type: 'boolean',
                default: false
            };
        }
    });

    wp.blocks.registerBlockType('bookly/appointments-list', {
        title: BooklyAppointmentListL10n.block.title,
        description: BooklyAppointmentListL10n.block.description,
        icon: el('svg', { width: '20', height: '20', viewBox: "0 0 64 64" },
            el('path', {style: {fill: "rgb(0, 0, 0)"}, d: "M 8 0 H 56 A 8 8 0 0 1 64 8 V 22 H 0 V 8 A 8 8 0 0 1 8 0 Z"}),
            el('path', {style: {fill: "rgb(244, 102, 47)"}, d: "M 0 22 H 64 V 56 A 8 8 0 0 1 56 64 H 8 A 8 8 0 0 1 0 56 V 22 Z"}),
            el('rect', {style: {fill: "rgb(98, 86, 86)"}, x: 6, y: 6, width: 52, height: 10}),
            el('rect', {style: {fill: "rgb(242, 227, 227)"}, x: 12, y: 30, width: 40, height: 24}),
            el('rect', {style: {fill: "rgb(124,252,0)", stroke: 'rgb(0, 0, 0)'}, x: 32, y: 32, width: 28, height: 28})
        ),
        category: 'bookly-blocks',
        keywords: [
            'bookly',
            'appointments',
        ],
        supports: {
            customClassName: false,
            html: false
        },
        attributes: attributes,
        edit: function (props) {
            var inspectorElements = [],
                attributes   = props.attributes
            ;

            function getShortCode(props, attributes) {
                var short_code = '[bookly-appointments-list',
                    columns = [],
                    custom_fields = []
                ;
                if (attributes.column_category) {
                    columns.push('category');
                }
                if (attributes.column_service) {
                    columns.push('service');
                }
                if (attributes.column_staff) {
                    columns.push('staff');
                }
                if (attributes.column_date) {
                    columns.push('date');
                }
                if (attributes.column_online_meeting) {
                    columns.push('online_meeting');
                }
                if (attributes.column_time) {
                    columns.push('time');
                }
                if (attributes.column_price) {
                    columns.push('price');
                }
                if (attributes.column_status) {
                    columns.push('status');
                }
                if (attributes.column_cancel) {
                    columns.push('cancel');
                }

                if (columns.length > 0){
                    short_code += ' columns="' + columns.join(',') + '"';
                }

                $.each(BooklyAppointmentListL10n.customFields, function (index, field) {
                    if(field.type !== 'file') {
                        var attr_name = 'cf_' + field.id;
                        if (attributes[attr_name]) {
                            custom_fields.push(field.id)
                        }
                    }
                });

                if (custom_fields.length > 0){
                    short_code += ' custom_fields="' + custom_fields.join(',') + '"';
                }

                if (attributes.titles) {
                    short_code += ' show_column_titles="1"';
                }

                short_code += ']';

                props.setAttributes({short_code: short_code});

                return short_code;
            }

            inspectorElements.push(el(components.PanelRow,
                {},
                el('label', {htmlFor: 'bookly-js-show-titles'}, BooklyAppointmentListL10n.titles),
                el(components.FormToggle, {
                    id: 'bookly-js-show-titles',
                    checked: attributes.titles,
                    onChange: function () {
                        return props.setAttributes({titles: !props.attributes.titles});
                    },
                })
            ));

            // Add row Columns     show
            inspectorElements.push(el(components.PanelRow,
                {},
                el('b', {}, BooklyAppointmentListL10n.columns),
                el('span', {}, BooklyAppointmentListL10n.show),
            ));

            $.each(BooklyAppointmentListL10n.tableColumns, function (column, label) {
                var attr_name = 'column_' + column,
                    attribute = {};
                attribute[attr_name] = !props.attributes[attr_name];

                inspectorElements.push(el(components.PanelRow,
                    {},
                    el('label', {htmlFor: 'bookly-js-show-' + column}, label),
                    el(components.FormToggle, {
                        id: 'bookly-js-show-' + column,
                        checked: attributes[attr_name],
                        onChange: function () {
                            return props.setAttributes(attribute);
                        },
                    })
                ));
            });

            if (BooklyAppointmentListL10n.customFields.length > 0) {
                // Add row Custom fields     show
                inspectorElements.push(el(components.PanelRow,
                    {},
                    el('b', {}, BooklyAppointmentListL10n.customFieldsTitle),
                    el('span', {}, BooklyAppointmentListL10n.show),
                ));

                $.each(BooklyAppointmentListL10n.customFields, function (index, field) {
                    if (field.type !== 'file') {
                        var attr_name = 'cf_' + field.id,
                            attribute = {};
                        attribute[attr_name] = !props.attributes[attr_name];
                        inspectorElements.push(el(components.PanelRow,
                            {},
                            el('label', {htmlFor: 'bookly-js-show-cf-' + field.id}, field.label),
                            el(components.FormToggle, {
                                id: 'bookly-js-show-cf-' + field.id,
                                checked: attributes[attr_name],
                                onChange: function () {
                                    return props.setAttributes(attribute);
                                },
                            })
                        ));
                    }
                });
            }

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