/**
 * jQuery booklyDropdown.
 */
(function ($) {
    let id = 0;
    let methods = {
        init: function (options) {
            let opts = $.extend({}, $.fn.booklyDropdown.defaults, options);

            return this.filter('ul').each(function () {
                if ($(this).data('booklyDropdown')) {
                    return;
                }
                let obj = {
                    $container:  $('<div class="dropdown"/>'),
                    $button:     $('<button type="button" class="btn btn-default dropdown-toggle d-flex align-items-center w-100" data-toggle="dropdown"/>'),
                    $counter:    $('<span class="flex-grow-1 text-left mr-1"/>'),
                    $ul:         $(this),
                    $selectAll:  $('<input type="checkbox" class="custom-control-input"/>').attr('id', 'bookly-js-dropdown-' + (++id)),
                    $groups:     $(),
                    $options:    $(),
                    preselected: [],  // initially selected options
                    refresh:     function () {
                        let $selected = obj.$options.filter(':checked');
                        obj.$selectAll.prop('checked', false);
                        obj.$groups.prop('checked', false);
                        if ($selected.length === 0) {
                            obj.$counter.text(obj.txtNothingSelected);
                        } else if ($selected.length === obj.$options.length) {
                            obj.$counter.text(obj.txtAllSelected);
                            obj.$selectAll.prop('checked', true);
                            obj.$groups.prop('checked', true);
                        } else {
                            if ($selected.length === 1) {
                                obj.$counter.text($selected.next().text());
                            } else {
                                obj.$counter.text($selected.length + '/' + obj.$options.length);
                            }
                            obj.$groups.each(function () {
                                let $this = $(this);
                                $this.prop('checked', $this.data('group-checkboxes').filter(':not(:checked)').length === 0);
                            });
                        }
                    }
                };
                // Texts.
                obj.txtSelectAll       = obj.$ul.data('txt-select-all') || opts.txtSelectAll;
                obj.txtAllSelected     = obj.$ul.data('txt-all-selected') || opts.txtAllSelected;
                obj.txtNothingSelected = obj.$ul.data('txt-nothing-selected') || opts.txtNothingSelected;

                obj.$container
                    .addClass(obj.$ul.data('container-class') || opts.containerClass)
                    .append(
                        obj.$button
                            // Icon.
                            .append($('<i class="mr-1 fa-fw"/>').addClass(obj.$ul.data('icon-class') || opts.iconClass))
                            // Counter.
                            .append(obj.$counter)
                    )
                    .append(
                        obj.$ul
                            .addClass('dropdown-menu dropdown-menu-' + (obj.$ul.data('align') || opts.align))
                            // Options (checkboxes).
                            .append($.map(opts.options, function (option) {
                                return $('<li/>')
                                    .data({
                                        'input-name': option.inputName || opts.inputsName,
                                        'value':      option.value || '',
                                        'selected':   option.selected || false
                                    })
                                    .text(option.name)
                                ;
                            }))
                            .find('li')
                                .addClass('dropdown-item')
                                .wrapInner('<div class="custom-control custom-checkbox ml-4"><label class="custom-control-label"></label></div>')
                                .each(function () {
                                    let $li       = $(this),
                                        $checkbox = $('<input type="checkbox" class="custom-control-input"/>').attr('id', 'bookly-js-dropdown-' + (++id)),
                                        $ul       = $li.find('ul:first')
                                    ;
                                    if ($li.is('[data-flatten-if-single]') && obj.$ul.children().length === 1) {
                                        $li.replaceWith($ul.children());
                                        return true;
                                    }
                                    if ($ul.length > 0) {
                                        $ul.appendTo($li);
                                        $ul.addClass('p-0');
                                        obj.$groups = obj.$groups.add($checkbox);
                                    } else {
                                        $checkbox
                                            .attr('name', $li.data('input-name'))
                                            .val($li.data('value'))
                                            .prop('checked', !!$li.data('selected'))
                                        ;
                                        obj.$options = obj.$options.add($checkbox);
                                        if ($checkbox.prop('checked')) {
                                            obj.preselected.push($checkbox.val());
                                        }
                                    }
                                    $li.find('label:first').attr('for', $checkbox.attr('id')).before($checkbox);
                                })
                            .end()
                            // Select all.
                            .prepend(
                                $('<li class="dropdown-item"/>')
                                    .append(
                                        $('<div class="custom-control custom-checkbox"/>')
                                            .append(obj.$selectAll)
                                            .append(
                                                $('<label class="custom-control-label"/>')
                                                    .attr('for', obj.$selectAll.attr('id'))
                                                    .append(obj.txtSelectAll)
                                            )
                                    )
                            )
                            // Replace with container.
                            .replaceWith(obj.$container)
                            // Do not close on click.
                            .on('click', function (e) {
                                e.stopPropagation();
                            })
                    )
                    // Events.
                    .on('change', 'input:checkbox', function () {
                        var $this = $(this);
                        if ($this.is(obj.$selectAll)) {
                            obj.$options.prop('checked', this.checked);
                            opts.onChange.call(obj.$ul, obj.$options.map(function () { return this.value; }).get(), this.checked, true);
                        } else if ($this.is(obj.$groups)) {
                            $this.data('group-checkboxes').prop('checked', this.checked);
                            opts.onChange.call(obj.$ul, $this.data('group-checkboxes').map(function () { return this.value; }).get(), this.checked, false);
                        } else {
                            opts.onChange.call(obj.$ul, [this.value], this.checked, false);
                        }
                        obj.refresh();
                        obj.$ul.trigger('changed');
                    })
                ;

                // Attach a handler to an event for the container
                obj.$container.bind('dropdown.change', function () {
                    opts.onChange.call(obj.$ul, obj.$options.map(function () { return this.value; }).get(), this.checked, false);
                });

                // Link group checkboxes with sub-items.
                obj.$groups.each(function () {
                    let $this       = $(this),
                        $checkboxes = $this.closest('li').find('ul input:checkbox')
                    ;
                    $this.data('group-checkboxes', $checkboxes);
                });

                obj.refresh();
                obj.$ul.data('booklyDropdown', obj);
            });
        },
        deselect: function (values) {
            if (!Array.isArray(values)) {
                values = [values];
            }
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.$options.each(function () {
                    if ($.inArray(this.value, values) > -1) {
                        this.checked = false;
                    }
                });
                obj.refresh();
            });
        },
        deselectAll: function () {
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.$options.prop('checked', false);
                obj.refresh();
            });
        },
        getSelected: function () {
            var obj = this.filter('ul').data('booklyDropdown'),
                res = []
            ;
            if (obj) {
                obj.$options.filter(':checked').each(function () {
                    res.push(this.value);
                });
            }

            return res;
        },
        getSelectedAllState: function () {
            var obj = this.filter('ul').data('booklyDropdown');
            return obj.$selectAll.prop('checked');
        },
        getSelectedExt: function () {
            var obj = this.filter('ul').data('booklyDropdown'),
                res = []
            ;
            if (obj) {
                obj.$options.filter(':checked').each(function () {
                    res.push({value: this.value, name: $(this).next('label').text()});
                });
            }

            return res;
        },
        hide: function () {
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.$container.hide();
            });
        },
        refresh: function () {
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.refresh();
            });
        },
        reset: function () {
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.$options.each(function () {
                    this.checked = $.inArray(this.value, obj.preselected) > -1;
                });
                obj.refresh();
            });
        },
        select: function (values) {
            if (!Array.isArray(values)) {
                values = [values];
            }
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.$options.each(function () {
                    if ($.inArray(this.value, values) > -1) {
                        this.checked = true;
                    }
                });
                obj.refresh();
            });
        },
        selectAll: function () {
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.$options.prop('checked', true);
                obj.refresh();
            });
        },
        setSelected: function (values) {
            if (!Array.isArray(values)) {
                values = [values];
            }
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.$options.each(function () {
                    this.checked = $.inArray(this.value, values) > -1;
                });
                obj.refresh();
            });
        },
        show: function () {
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.$container.css('display', '');
            });
        },
        toggle: function () {
            return this.filter('ul').each(function () {
                var obj = $(this).data('booklyDropdown');
                if (!obj) {
                    return;
                }
                obj.$button.dropdown('toggle');
            });
        }
    };

    $.fn.booklyDropdown = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('No method ' +  method + ' for jQuery.booklyDropdown');
        }
    };

    $.fn.booklyDropdown.defaults = {
        align: $('body').hasClass('rtl') ? 'right ' : 'left',
        containerClass: '',
        iconClass: 'far fa-user',
        txtSelectAll: 'All',
        txtAllSelected: 'All selected',
        txtNothingSelected: 'Nothing selected',
        inputsName: '',
        options: [],
        onChange: function (values, selected, all) {}
    };
})(jQuery);