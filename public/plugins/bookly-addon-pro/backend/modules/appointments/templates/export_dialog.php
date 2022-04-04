<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
?>
<div id="bookly-export-dialog" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <form action="<?php echo admin_url( 'admin-ajax.php?action=bookly_pro_export_appointments' ) ?>" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php esc_html_e( 'Export to CSV', 'bookly' ) ?></h5>
                    <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="bookly-csv-delimiter"><?php esc_html_e( 'Delimiter', 'bookly' ) ?></label>
                        <select id="bookly-csv-delimiter" class="form-control custom-select" name="delimiter">
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
                        <?php foreach ( $datatables['settings']['columns'] as $column => $show ) : ?>
                            <?php if ( $show ) : ?>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input" id="bookly-ea-<?php echo $column?>" name="exp[<?php echo $column ?>]" type="checkbox" checked/>
                                    <label class="custom-control-label" for="bookly-ea-<?php echo $column?>"><?php echo $datatables['titles'][ $column ] ?></label>
                                </div>
                            <?php endif ?>
                        <?php endforeach ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="filter"/>
                    <?php Inputs::renderCsrf() ?>
                    <?php Buttons::renderSubmit( null, null, __( 'Export to CSV', 'bookly' ) ) ?>
                </div>
            </div>
        </form>
    </div>
</div>