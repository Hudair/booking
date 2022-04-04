<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
?>
<div id="bookly-export-dialog" class="bookly-modal bookly-fade" tabindex=-1>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'Export to CSV', 'bookly' ) ?></h5>
                <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="bookly-csv-delimiter"><?php esc_html_e( 'Delimiter', 'bookly' ) ?></label>
                    <select id="bookly-csv-delimiter" class="form-control custom-select">
                        <option value=","><?php esc_html_e( 'Comma (,)', 'bookly' ) ?></option>
                        <option value=";"><?php esc_html_e( 'Semicolon (;)', 'bookly' ) ?></option>
                    </select>
                </div>
                <div class="form-group mb-0">
                    <div class="custom-control custom-checkbox">
                        <input id="bookly-js-export-select-all" class="bookly-js-required custom-control-input" type="checkbox" checked />
                        <label class="custom-control-label" for="bookly-js-export-select-all"><?php esc_html_e( 'Select all', 'bookly' ) ?></label>
                    </div>
                </div>
                <div class="form-group ml-3 bookly-js-columns">
                    <?php Inputs::renderCheckBox( Common::getTranslatedOption( 'bookly_l10n_label_employee' ), 0, true ) ?>
                    <?php Inputs::renderCheckBox( Common::getTranslatedOption( 'bookly_l10n_label_service' ), 1, true ) ?>
                    <?php Inputs::renderCheckBox( __( 'Total', 'bookly' ), 2, true ) ?>
                    <?php Inputs::renderCheckBox( __( 'Approved', 'bookly' ), 3, true ) ?>
                    <?php Inputs::renderCheckBox( __( 'Pending', 'bookly' ), 4, true ) ?>
                    <?php Inputs::renderCheckBox( __( 'Rejected', 'bookly' ), 5, true ) ?>
                    <?php Inputs::renderCheckBox( __( 'Cancelled', 'bookly' ), 6, true ) ?>
                    <?php Inputs::renderCheckBox( __( 'Total', 'bookly' ), 7, true ) ?>
                    <?php Inputs::renderCheckBox( __( 'New', 'bookly' ), 8, true ) ?>
                    <?php Inputs::renderCheckBox( __( 'Revenue', 'bookly' ), 9, true ) ?>
                </div>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderSubmit( null, null, __( 'Export to CSV', 'bookly' ), array( 'data-dismiss' => 'bookly-modal' ) ) ?>
            </div>
        </div>
    </div>
</div>