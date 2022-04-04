<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Modules\Customers\Page;
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Components\Controls\Inputs;
?>
<div id="bookly-import-customers-dialog" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <form enctype="multipart/form-data" action="<?php echo Common::escAdminUrl( Page::pageSlug() ) ?>" method="POST">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php esc_html_e( 'Import', 'bookly' ) ?></h5>
                    <button type="button" class="close" data-dismiss="bookly-modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <h5><?php esc_html_e( 'Note', 'bookly' ) ?></h5>
                    <p>
                        <?php esc_html_e( 'You may import list of clients in CSV format. You can choose the columns contained in your file. The sequence of columns should coincide with the specified one.', 'bookly' )  ?>
                    </p>
                    <div class="form-group">
                        <label for="import_customers_file"><?php esc_html_e( 'Select file', 'bookly' ) ?></label>
                        <input name="import_customers_file" id="import_customers_file" type="file" />
                    </div>
                    <div class="form-group">
                        <?php Inputs::renderCheckBox( get_option( 'bookly_l10n_label_name' ), null, true, array( 'name' => 'full_name' ) ) ?>
                        <?php Inputs::renderCheckBox( get_option( 'bookly_l10n_label_first_name' ), null, false, array( 'name' => 'first_name' ) ) ?>
                        <?php Inputs::renderCheckBox( get_option( 'bookly_l10n_label_last_name' ), null, false, array( 'name' => 'last_name' ) ) ?>
                        <?php Inputs::renderCheckBox( get_option( 'bookly_l10n_label_phone' ), null, true, array( 'name' => 'phone' ) ) ?>
                        <?php Inputs::renderCheckBox( get_option( 'bookly_l10n_label_email' ), null, true, array( 'name' => 'email' ) ) ?>
                        <?php Inputs::renderCheckBox( __( 'Date of birth', 'bookly' ), null, true, array( 'name' => 'birthday' ) ) ?>
                    </div>
                    <div class="form-group">
                        <label for="import_customers_delimiter"><?php esc_html_e( 'Delimiter', 'bookly' ) ?></label>
                        <select name="import_customers_delimiter" id="import_customers_delimiter" class="form-control">
                            <option value=","><?php esc_html_e( 'Comma (,)', 'bookly' ) ?></option>
                            <option value=";"><?php esc_html_e( 'Semicolon (;)', 'bookly' ) ?></option>
                        </select>
                    </div>
                    <input type="hidden" name="import-customers">
                </div>
                <div class="modal-footer">
                    <?php Buttons::renderSubmit( null, null, __( 'Import', 'bookly' ) ) ?>
                </div>
            </div>
        </form>
    </div>
</div>