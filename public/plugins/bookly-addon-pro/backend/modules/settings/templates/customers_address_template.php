<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Ace;
use Bookly\Backend\Modules\Settings\Codes;

?>
<div class="form-group">
    <label for="bookly-settings-customers-editor"><?php esc_html_e( 'Customer address', 'bookly' ) ?></label>
    <?php Ace\Editor::render( 'bookly-settings-customers', 'bookly-settings-customers-editor', Codes::getJson( 'customer_address' ), get_option( 'bookly_l10n_cst_address_template', '' ), 'bookly-ace-editor-h80' ) ?>
    <input type="hidden" name="bookly_l10n_cst_address_template" value="<?php echo esc_attr( get_option( 'bookly_l10n_cst_address_template', '' ) ) ?>"/>
</div>