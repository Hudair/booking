<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Lib;
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0">Data management</h4> <span class="text-muted">php: <?php echo PHP_VERSION ?> | db: <?php echo esc_html( $db['version'] ) ?></span>
    </div>

    <div class="form-row">
        <div class="col-12 col-sm-auto mb-3">
            <form action="<?php echo admin_url( 'admin-ajax.php?action=bookly_export_data' ) ?>" method="POST">
                <?php Inputs::renderCsrf() ?>
                <button id="bookly-export" type="submit" class="btn btn-success">
                    <span class="ladda-label">Export data</span>
                </button>
            </form>
        </div>
        <div class="col-12 col-sm-auto mb-3">
            <form id="bookly_import" action="<?php echo admin_url( 'admin-ajax.php?action=bookly_import_data' ) ?>" method="POST" enctype="multipart/form-data">
                <?php Inputs::renderCsrf() ?>
                <div id="bookly-import" class="btn btn-primary btn-file">
                    <span class="ladda-label">Import data</span>
                    <input type="file" id="bookly_import_file" name="import" class="w-100">
                </div>
            </form>
        </div>
        <div class="col-12 col-sm-auto ml-auto mb-3">
            <div class="dropdown">
                <button class="btn btn-default dropdown-toggle" type="button" data-spinner-size="40" data-style="zoom-in" data-spinner-color="rgb(62, 66, 74)" id="tools-dropdown" data-toggle="dropdown" aria-haspopup="true"
                        aria-expanded="true">
                    <span class="ladda-label">Tools</span>
                    <span class="caret"></span>
                </button>
                <div class="dropdown-menu bookly-js-tools" aria-labelledby="dropdownMenu1">
                    <?php echo Lib\Utils\Common::stripScripts( $tools ) ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-auto mb-3">
            <?php Bookly\Backend\Components\Controls\Buttons::render( 'bookly-all-test', 'btn-default', 'Tests', array( 'data-spinner-color' => 'rgb(62, 66, 74)' ) ) ?>
        </div>
        <div class="col-12 col-sm-auto mb-3">
            <?php Bookly\Backend\Components\Controls\Buttons::render( 'bookly-fix-all-silent', $trouble ? 'btn-success' : 'btn-default', 'Fix database schema…' ) ?>
        </div>
    </div>

    <div class="card">
        <div class="card-body" id="accordion" role="tablist" aria-multiselectable="true">
            <?php foreach ( $debug as $tableName => $table ) : ?>
                <div class="card bookly-collapse my-1">
                    <div class="card-header py-1 d-flex align-items-center bookly-js-table <?php echo esc_attr( $table['status'] == 1 ? '' : ( $table['status'] == 2 ? 'bg-danger' : 'bg-info' ) ) ?>" role="tab" id="<?php echo esc_attr( $tableName ) ?>">
                        <a role="button" class="collapsed" role="button" data-toggle="collapse" href="#table-<?php echo esc_attr( $tableName ) ?>" aria-expanded="true" aria-controls="<?php echo esc_attr( $tableName ) ?>">
                            <?php echo esc_html( $tableName ) ?>
                        </a>
                        <?php if ( ! $table['status'] ) : ?>
                            <button class="btn btn-success btn-sm py-0 ml-auto" type="button" data-action="fix-create-table">create</button>
                        <?php endif ?>
                    </div>
                    <div class="card-body collapse" id="table-<?php echo esc_attr( $tableName ) ?>">
                        <?php if ( $table['status'] ) : ?>
                            <h5>Columns</h5>
                            <table class="table table-condensed table-striped table-sm">
                                <thead>
                                <tr>
                                    <th>Column name</th>
                                    <th width="50">Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ( $table['fields'] as $field => $status ) : ?>
                                    <tr class="<?php echo esc_attr( $status ? 'bg-default' : 'bg-danger' ) ?>">
                                        <td><?php echo esc_html( $field ) ?>
                                            <?php if ( isset( $table['info'][ $field ] ) ) : ?>
                                                <div class="float-right">
                                                    <?php foreach ( $table['info'][ $field ] as $key ) : ?>
                                                        <span class="badge badge-warning" style="margin: 0 5px;"><?php echo esc_html( $key ) ?></span>
                                                    <?php endforeach ?>
                                                </div>
                                            <?php endif ?>
                                        </td>
                                        <td><?php echo esc_html( $status ? 'OK' : '<button class="btn btn-success btn-sm py-0" type="button" data-action="fix-column">FIX…</button>' ) ?></td>
                                    </tr>
                                <?php endforeach ?>
                                <?php if ( isset( $table['fields_3d'] ) ) : ?>
                                    <tr>
                                        <th>Unknown columns</th>
                                        <th width="50">Action</th>
                                    </tr>
                                    <?php foreach ( $table['fields_3d'] as $field => $data ) : ?>
                                        <tr class="bg-warning">
                                            <td><span class="field" data-entity="<?php echo esc_attr( $data['class'] ) ?>"><?php echo esc_html( $field ) ?></span>
                                                <div class="float-right">
                                                    <span class="badge badge-light" style="margin: 0 5px;">type: <?php echo esc_html( $data['type'] ) ?></span>
                                                    <?php if ( $data['is_nullabe'] == '0' ) : ?>
                                                        <span class="badge badge-light" style="margin: 0 5px;">not null</span>
                                                    <?php endif ?>
                                                    <?php if ( $data['default'] ) : ?>
                                                        <span class="badge badge-light" style="margin: 0 5px;">default: <?php echo esc_html( $data['default'] ) ?></span>
                                                    <?php endif ?>
                                                </div>
                                            </td>
                                            <td>
                                                <button class="btn btn-success btn-sm py-0" type="button" data-action="drop-column">DROP…</button>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                <?php endif ?>
                                </tbody>
                            </table>
                            <?php if ( $table['constraints'] ) : ?>
                                <h5>Constraints</h5>
                                <table class="table table-condensed table-striped table-sm">
                                    <thead>
                                    <tr>
                                        <th>Column name</th>
                                        <th>Referenced table name</th>
                                        <th>Referenced column name</th>
                                        <th width="50">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ( $table['constraints'] as $key => $constraint ) : ?>
                                        <tr class="<?php echo esc_attr( $constraint['status'] ? 'bg-default' : 'bg-danger' ) ?>">
                                            <td><?php echo esc_html( $constraint['column_name'] ) ?></td>
                                            <td><?php echo esc_html( $constraint['referenced_table_name'] ) ?></td>
                                            <td><?php echo esc_html( $constraint['referenced_column_name'] ) ?></td>
                                            <td><?php echo esc_html( $constraint['status'] ? 'OK' : '<button class="btn btn-success btn-sm py-0" type="button" data-action="fix-constraint">FIX…</button>' ) ?></td>
                                        </tr>
                                    <?php endforeach ?>
                                    </tbody>
                                </table>
                            <?php endif ?>
                            <?php if ( $table['constraints_3d'] ) : ?>
                                <h5>Third-party constraints</h5>
                                <table class="table table-condensed table-sm">
                                    <thead>
                                    <tr>
                                        <th>Column name</th>
                                        <th>Reference</th>
                                        <th>Name</th>
                                        <th width="50">Status</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ( $table['constraints_3d'] as $key => $constraint ) : ?>
                                        <tr class="<?php echo esc_attr( $constraint['status'] ? 'default' : 'danger' ) ?>">
                                            <td><?php echo esc_html( $constraint['column_name'] ) ?></td>
                                            <td><?php echo esc_html( $constraint['referenced_table_name'] . '.' . $constraint['referenced_column_name'] ) ?>
                                                <?php if ( ! $constraint['reference_exists'] ) : ?>
                                                <div class="float-right"><span class="badge badge-warning" style="margin: 0 5px;">not exist</span><?php endif ?></div>
                                            </td>
                                            <td><?php echo esc_html( $constraint['constraint_name'] ) ?></td>
                                            <td><?php if ( $constraint['status'] ) : ?>
                                                    OK
                                                <?php else : ?>
                                                    <button class="btn btn-sm py-0 <?php echo esc_attr( $constraint['reference_exists'] ? 'btn-danger' : 'btn-success' ) ?>" type="button" data-action="drop-constraint">DROP…</button>
                                                <?php endif ?>
                                            </td>
                                        </tr>
                                    <?php endforeach ?>
                                    </tbody>
                                </table>
                            <?php endif ?>
                        <?php else : ?>
                            Table does not exist
                        <?php endif ?>
                    </div>
                </div>
            <?php endforeach ?>
        </div>
    </div>
    <div id="bookly-js-add-constraint" class="bookly-modal bookly-fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add constraint</h5>
                    <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="bookly-js-loading" style="height: 120px;"></div>
                    <div class="bookly-js-loading">
                    <pre>
   ALTER TABLE `<span id="bookly-js-table"></span>`
ADD CONSTRAINT
   FOREIGN KEY (`<span id="bookly-js-column"></span>`)
    REFERENCES `<span id="bookly-js-ref_table"></span>` (`<span id="bookly-js-ref_column"></span>`)
     ON DELETE <select id="bookly-js-DELETE_RULE">
            <option></option>
            <option value="RESTRICT">RESTRICT</option>
            <option value="CASCADE">CASCADE</option>
            <option value="SET NULL">SET NULL</option>
            <option value="NO ACTIONS">NO ACTIONS</option>
            </select>
     ON UPDATE <select id="bookly-js-UPDATE_RULE">
            <option></option>
            <option value="RESTRICT">RESTRICT</option>
            <option value="CASCADE">CASCADE</option>
            <option value="SET NULL">SET NULL</option>
            <option value="NO ACTIONS">NO ACTIONS</option>
            </select></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="pull-left">
                        <div class="btn-group bookly-js-fix-consistency">
                            <button type="button" class="btn btn-danger bookly-js-auto ladda-button" data-spinner-size="40" data-style="zoom-in" data-action="fix-consistency"><span class="ladda-label">Consistency…</span></button>
                            <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <div class="dropdown-menu">
                                <a class="bookly-js-update dropdown-item" href="#" data-action="fix-consistency">UPDATE `<span class="bookly-js-table"></span>` SET `<span class="bookly-js-ref_column"></span>` = NULL WHERE `<span class="bookly-js-ref_column"></span>` NOT IN (…)</a>
                                <a class="bookly-js-delete dropdown-item" href="#" data-action="fix-consistency">DELETE FROM `<span class="bookly-js-table"></span>` WHERE `<span class="bookly-js-ref_column"></span>` NOT IN (…)</a>
                            </div>
                        </div>
                    </div>
                    <?php Buttons::render( null, 'bookly-js-delete btn-danger pull-left', 'Delete rows…', array( 'style' => 'display:none' ) ) ?>
                    <?php Buttons::render( null, 'bookly-js-save btn-success', 'Add constraint' ) ?>
                    <?php Buttons::renderCancel( 'Close' ) ?>
                </div>
            </div>
        </div>
    </div>
    <div id="bookly-js-add-field" class="bookly-modal bookly-fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add column</h5>
                    <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="bookly-js-loading" style="height: 120px;"></div>
                    <div class="bookly-js-loading">
                        <pre></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php Buttons::render( null, 'bookly-js-save btn-success', 'Add column' ) ?>
                    <?php Buttons::renderCancel( 'Close' ) ?>
                </div>
            </div>
        </div>
    </div>
    <div id="bookly-js-create-table" class="bookly-modal bookly-fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create table</h5>
                    <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="bookly-js-loading" style="height: 120px;"></div>
                    <div class="bookly-js-loading">
                        <pre></pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php Buttons::render( null, 'bookly-js-save btn-success', 'Create table' ) ?>
                    <?php Buttons::renderCancel( 'Close' ) ?>
                </div>
            </div>
        </div>
    </div>
    <div id="bookly-js-drop-constraint" class="bookly-modal bookly-fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Drop foreign key</h5>
                    <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="bookly-js-loading"><pre>
     ALTER TABLE `<span id="bookly-js-table"></span>`
DROP FOREIGN KEY `<span id="bookly-js-constraint"></span>`</pre>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php Buttons::render( null, 'bookly-js-save btn-success', 'Drop' ) ?>
                    <?php Buttons::renderCancel( 'Close' ) ?>
                </div>
            </div>
        </div>
    </div>
    <div id="bookly-js-drop-column" class="bookly-modal bookly-fade" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Drop column with foreign keys</h5>
                    <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="mb-4 h6">If there are foreign keys for <b id="bookly-js-column"></b>, they will be dropped with the column.</div>
                    <pre>
ALTER TABLE `<span id="bookly-js-table"></span>`
DROP COLUMN `<span id="bookly-js-column"></span>`</pre>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="bookly-js-entity">
                    <?php Buttons::render( null, 'bookly-js-save btn-success', 'Drop' ) ?>
                    <?php Buttons::renderCancel( 'Close' ) ?>
                </div>
            </div>
        </div>
    </div>
</div>