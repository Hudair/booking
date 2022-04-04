jQuery(function($) {
    let $addConstraintModal  = $('#bookly-js-add-constraint'),
        $dropConstraintModal = $('#bookly-js-drop-constraint'),
        $dropColumnModal = $('#bookly-js-drop-column'),
        $columnModal = $('#bookly-js-add-field'),
        $tableModal = $('#bookly-js-create-table'),
        $tools = $('.bookly-js-tools'),
        $toolsDropDown = $('#tools-dropdown'),
        $status,
        $buttonAction
    ;

    $('.collapse').collapse('hide');

    $('#bookly_import_file').change(function() {
        if($(this).val()) {
            $('#bookly_import').submit();
        }
    });
    $('#bookly-fix-all-silent').on('click', function () {
        if (confirm('Execute automatic fixing issues found in database schema?')) {
            let ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action: 'bookly_fix_data_base_schema',
                    csrf_token: BooklyL10n.csrfToken
                },
                dataType : 'json',
                success  : function (response) {
                    booklyAlert({success: [response.data.message]});
                    if (!response.success) {
                        booklyAlert({error: response.data.errors});
                    }
                    ladda.stop();
                },
                error: function () {
                    booklyAlert({error: ['Error: in query execution.']});
                    ladda.stop();
                },
            }).always(function () {
                setTimeout(function () {
                    if (confirm('Reload page?')) {
                        location.reload();
                    }
                }, 3000);
            });
        }
    });

    $('[data-action=fix-constraint]')
        .on('click', function (e) {
            e.preventDefault();
            $status = $(this).closest('td');
            let $tr = $(this).closest('tr'),
                table = $tr.closest('.card').find('.bookly-js-table').attr('id'),
                column = $tr.find('td:eq(0)').html(),
                ref_table = $tr.find('td:eq(1)').html(),
                ref_column = $tr.find('td:eq(2)').html()
            ;
            $('.bookly-js-loading:first-child', $addConstraintModal).addClass('bookly-loading').removeClass('collapse');
            $('.bookly-js-loading:last-child', $addConstraintModal).addClass('collapse');
            $('.bookly-js-fix-consistency', $addConstraintModal).hide();
            $addConstraintModal.booklyModal();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action    : 'bookly_get_constraint_data',
                    table     : table,
                    column    : column,
                    ref_table : ref_table,
                    ref_column: ref_column,
                    csrf_token: BooklyL10n.csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        $('#bookly-js-table, .bookly-js-table', $addConstraintModal).html(table);
                        $('#bookly-js-column, .bookly-js-column', $addConstraintModal).html(column);
                        $('#bookly-js-ref_table, .bookly-js-ref_table', $addConstraintModal).html(ref_table);
                        $('#bookly-js-ref_column, .bookly-js-ref_column', $addConstraintModal).html(ref_column);
                        $('#bookly-js-DELETE_RULE', $addConstraintModal).val(response.data.DELETE_RULE);
                        $('#bookly-js-UPDATE_RULE', $addConstraintModal).val(response.data.UPDATE_RULE);
                    } else {
                        $('#bookly-js-DELETE_RULE', $addConstraintModal).val('');
                        $('#bookly-js-DELETE_RULE', $addConstraintModal).val('');
                    }
                    $('.bookly-js-loading', $addConstraintModal).toggleClass('collapse');
                }
            });
        });

    $('[data-action=drop-constraint]')
        .on('click', function (e) {
            e.preventDefault();
            $buttonAction = $(this);
            $status = $(this).closest('td');
            let $tr = $(this).closest('tr'),
                table     = $tr.closest('.card').find('.bookly-js-table').attr('id'),
                constrain = $tr.find('td:eq(2)').html()
            ;
            $dropConstraintModal.booklyModal();
            $('#bookly-js-table', $dropConstraintModal).html(table);
            $('#bookly-js-constraint', $dropConstraintModal).html(constrain);
        });

    $('[data-action=drop-column]')
        .on('click', function (e) {
            e.preventDefault();
            $buttonAction = $(this);
            $status = $(this).closest('td');
            let $tr = $(this).closest('tr'),
                table  = $tr.closest('.card').find('.bookly-js-table').attr('id'),
                $field = $('span.field', $tr)
            ;
            $dropColumnModal.booklyModal();
            $('#bookly-js-table', $dropColumnModal).html(table);
            $('#bookly-js-column', $dropColumnModal).html($field.html());
            $('#bookly-js-entity', $dropColumnModal).html($field.data('entity'));
        });

    $dropColumnModal
        .on('click', '.bookly-js-save', function () {
            let ladda = Ladda.create(this),
                entity = $('#bookly-js-entity', $dropColumnModal).html(),
                column = $('#bookly-js-column', $dropColumnModal).html();
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action: 'bookly_drop_column',
                    entity: entity,
                    column: column,
                    csrf_token: BooklyL10n.csrfToken
                },
                dataType : 'json',
                success  : function (response) {
                    if (response.success) {
                        booklyAlert({success: [response.data.message]});
                        $dropColumnModal.booklyModal('hide');
                        $buttonAction.closest('tr').remove();
                    } else {
                        booklyAlert({error : [response.data.message]});
                    }
                    ladda.stop();
                },
                error: function () {
                    booklyAlert({error: ['Error: in query execution.']});
                    ladda.stop();
                }
            });
        });

    $addConstraintModal
        .on('click', '.bookly-js-save', function () {
            let ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action      : 'bookly_add_constraint',
                    table       : $('#bookly-js-table', $addConstraintModal).html(),
                    column      : $('#bookly-js-column', $addConstraintModal).html(),
                    ref_table   : $('#bookly-js-ref_table', $addConstraintModal).html(),
                    ref_column  : $('#bookly-js-ref_column', $addConstraintModal).html(),
                    delete_rule : $('#bookly-js-DELETE_RULE', $addConstraintModal).val(),
                    update_rule : $('#bookly-js-UPDATE_RULE', $addConstraintModal).val(),
                    csrf_token  : BooklyL10n.csrfToken
                },
                dataType : 'json',
                success  : function (response) {
                    if (response.success) {
                        booklyAlert({success: [response.data.message]});
                        $addConstraintModal.booklyModal('hide');
                        $status.html('OK');
                    } else {
                        booklyAlert({error : [response.data.message]});
                        $('.bookly-js-fix-consistency', $addConstraintModal).show();
                    }
                    ladda.stop();
                },
                error: function () {
                    booklyAlert({error: ['Error: Constraint not created.']});
                    ladda.stop();
                }
            });
        })
        .on('click', '[data-action=fix-consistency]', function (e) {
            e.preventDefault();
            let $button     = $(this),
                table       = $('#bookly-js-table', $addConstraintModal).html(),
                column      = $('#bookly-js-column', $addConstraintModal).html(),
                ref_table   = $('#bookly-js-ref_table', $addConstraintModal).html(),
                ref_column  = $('#bookly-js-ref_column', $addConstraintModal).html(),
                data = {
                    action     : 'bookly_fix_consistency',
                    table      : $('#bookly-js-table', $addConstraintModal).html(),
                    column     : $('#bookly-js-column', $addConstraintModal).html(),
                    ref_table  : $('#bookly-js-ref_table', $addConstraintModal).html(),
                    ref_column : $('#bookly-js-ref_column', $addConstraintModal).html(),
                    csrf_token : BooklyL10n.csrfToken,
                    rule       : ''
                },
                query       = '',
                ladda       = ''
            ;
            if ($button.hasClass('bookly-js-auto')) {
                data.rule = $('#bookly-js-DELETE_RULE', $addConstraintModal).val();
                ladda     = Ladda.create(this);
            } else {
                if ($button.hasClass('bookly-js-delete')) {
                    data.rule = 'CASCADE';
                } else if ($button.hasClass('bookly-js-update')) {
                    data.rule = 'SET NULL';
                }
                ladda = Ladda.create($('button[data-action=fix-consistency]')[0]);
            }

            switch (data.rule) {
                case 'NO ACTIONS':
                case 'RESTRICT':
                    booklyAlert({success: ['No manipulation actions were performed']});
                    return false;
                case 'CASCADE':
                    query = 'DELETE FROM `' + table + "`\n" + '          WHERE `' + column + '` NOT IN ( SELECT `' + ref_column + '` FROM `' + ref_table + '` )';
                    break;
                case 'SET NULL':
                    query = 'UPDATE TABLE `' + table + "`\n" + '                SET `' + column + '` = NULL' + "\n" + '           WHERE `' + column + '` NOT IN ( SELECT `' + ref_column + '` FROM `' + ref_table + '` )';
                    break;
            }

            if (confirm('IF YOU DON\'T KNOW WHAT WILL HAPPEN AFTER THIS QUERY EXECUTION? Click cancel.' + "\n\n---------------------------------------------------------------------------------------------------------------------------------\n\n" + query + "\n\n")) {
                ladda.start();
                $.ajax({
                    url  : ajaxurl,
                    type : 'POST',
                    data : data,
                    dataType : 'json',
                    success  : function (response) {
                        if (response.success) {
                            booklyAlert({success: [response.data.message]});
                            $('.bookly-js-fix-consistency', $addConstraintModal).hide();
                        } else {
                            booklyAlert({error : [response.data.message]});
                        }
                        ladda.stop();
                    }
                });
            }
        });

    $('[data-action=fix-column]')
        .on('click', function (e) {
            e.preventDefault();
            $status = $(this).closest('td');
            let $tr = $(this).closest('tr'),
                table = $tr.closest('.card').find('.bookly-js-table').attr('id'),
                column = $tr.find('td:eq(0)').html().trim()
            ;
            $('.bookly-js-loading:first-child', $columnModal).addClass('bookly-loading').removeClass('collapse');
            $('.bookly-js-loading:last-child', $columnModal).addClass('collapse');
            $('.bookly-js-fix-consistency', $columnModal).hide();
            $columnModal.booklyModal();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action    : 'bookly_get_field_data',
                    table     : table,
                    column    : column,
                    csrf_token: BooklyL10n.csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        let sql = 'ALTER TABLE `' + table + '`' +
                            "\n ADD COLUMN `" + column + '` ' + response.data;
                        $('pre', $columnModal).html(sql);
                    } else {
                        $('pre', $columnModal).html('');
                    }
                    $('.bookly-js-loading', $columnModal).toggleClass('collapse');
                }
            });
        });

    $columnModal
        .on('click', '.bookly-js-save', function () {
            let ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action      : 'bookly_execute_query',
                    query       : $('pre', $columnModal).html(),
                    csrf_token  : BooklyL10n.csrfToken
                },
                dataType : 'json',
                success  : function (response) {
                    if (response.success) {
                        booklyAlert({success: [response.data.message]});
                        $columnModal.booklyModal('hide');
                        $status.html('OK');
                        $status.closest('tr').removeClass('bg-danger');
                    } else {
                        booklyAlert({error : [response.data.message]});
                    }
                    ladda.stop();
                },
                error: function () {
                    booklyAlert({error: ['Error: in query execution.']});
                    ladda.stop();
                }
            });
        });

    $('[data-action=fix-create-table]')
        .on('click', function (e) {
            e.preventDefault();
            $buttonAction = $(this);
            let table = $buttonAction.parent().attr('id');
            $('.bookly-js-loading:first-child', $tableModal).addClass('bookly-loading').removeClass('collapse');
            $('.bookly-js-loading:last-child', $tableModal).addClass('collapse');
            $tableModal.booklyModal();
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action    : 'bookly_get_field_data',
                    table     : table,
                    column    : 'id',
                    csrf_token: BooklyL10n.csrfToken
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        let field = response.data.replace(' primary key', ','),
                            sql = 'CREATE TABLE `' + table + '` (' +
                            "\n `id` " + field +
                            "\nPRIMARY KEY (`id`))" +
                            "\nENGINE = INNODB" +
                            "\n" + BooklyL10n.charsetCollate + ";";
                        $('pre', $tableModal).html(sql);
                    } else {
                        $('pre', $tableModal).html('');
                    }
                    $('.bookly-js-loading', $tableModal).toggleClass('collapse');
                }
            });
        });

    $tableModal
        .on('click', '.bookly-js-save', function () {
            let ladda = Ladda.create(this);
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action      : 'bookly_execute_query',
                    query       : $('pre', $tableModal).html(),
                    csrf_token  : BooklyL10n.csrfToken
                },
                dataType : 'json',
                success  : function (response) {
                    if (response.success) {
                        booklyAlert({success: [response.data.message]});
                        $tableModal.booklyModal('hide');
                        $buttonAction.closest('.panel').find('.panel-body').html('Refresh the current page');
                        $buttonAction.remove();
                    } else {
                        booklyAlert({error : [response.data.message]});
                    }
                    ladda.stop();
                },
                error: function () {
                    booklyAlert({error: ['Error: in query execution.']});
                    ladda.stop();
                }
            });
        });

    $dropConstraintModal
        .on('click', '.bookly-js-save', function () {
            let ladda = Ladda.create(this),
                table = $('#bookly-js-table', $dropConstraintModal).html(),
                constrain = $('#bookly-js-constraint', $dropConstraintModal).html();
            ladda.start();
            $.ajax({
                url  : ajaxurl,
                type : 'POST',
                data : {
                    action    : 'bookly_execute_query',
                    query     : 'ALTER TABLE `' + table + '` DROP FOREIGN KEY `' + constrain + '`',
                    csrf_token: BooklyL10n.csrfToken
                },
                dataType : 'json',
                success  : function (response) {
                    if (response.success) {
                        booklyAlert({success: [response.data.message]});
                        $dropConstraintModal.booklyModal('hide');
                        $buttonAction.closest('tr').remove();
                    } else {
                        booklyAlert({error : [response.data.message]});
                    }
                    ladda.stop();
                },
                error: function () {
                    booklyAlert({error: ['Error: in query execution.']});
                    ladda.stop();
                }
            });
        });

    $tools.on('click', '[data-action]', function (e) {
        e.preventDefault();
        let ladda = Ladda.create($toolsDropDown[0]),
            data = $(this).data();
        ladda.start();
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'bookly_run_tool',
                tool_data: data,
                tool_name: data.tool,
                csrf_token: BooklyL10n.csrfToken
            },
            dataType: 'json',
            error: function () {
                booklyAlert({error: [test + ' error: in query execution.']});
            }
        }).then(function(response){
            booklyAlert(response.data.alerts);
            ladda.stop();
        });
    });

    $('#bookly-all-test').on('click', function () {
        let ladda = Ladda.create(this),
            count = BooklyL10n.tests.length,
            error_count = 0,
            errors = []
        ladda.start();
        ladda.setProgress(0.03);

        BooklyL10n.tests.forEach(function(test) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'bookly_run_test',
                    test_name: test
                },
                dataType: 'json',
                error: function () {
                    booklyAlert({error: [test + ' error: in query execution.']});
                }
            }).then(function(response) {
                if (!response.success) {
                    error_count += 1;
                    booklyAlert({error: ['Test: ' + response.data.test_name + '<p><pre>' + response.data.error + '</pre></p>']});
                }

                count -= 1;
                ladda.setProgress(1 - count / BooklyL10n.tests.length);
                if (count <= 0) {
                    ladda.stop();
                    booklyAlert({success: [(BooklyL10n.tests.length - error_count) + '/' + BooklyL10n.tests.length + ' tests complete successfully']});
                }
            });
        })
    }).trigger('click');
});