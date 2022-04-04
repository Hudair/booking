<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls;
/**
 * @var array $service
 */
$final_step_url_enabled = (int) ( $service['final_step_url'] !== '' );
?>
<div class="form-group">
    <?php Controls\Inputs::renderRadioGroup( __( 'Final step URL', 'bookly' ), __( 'Set the URL of a page that the user will be forwarded to after successful booking. If disabled then the default Done step is displayed.', 'bookly' ), array(), $final_step_url_enabled, array( 'name' => 'bookly_services_final_step_url_mode' ) ) ?>
</div>
<div class="form-group border-left mt-3 ml-4 pl-3 bookly-js-final-step-url" <?php if ( ! $final_step_url_enabled ) : ?>style="display: none"<?php endif ?>>
    <label for="bookly_url_final_step_url"><?php esc_html_e( 'Enter a URL', 'bookly' ) ?></label>
    <input class="form-control"
           type="text" name="final_step_url" id="bookly_url_final_step_url"
           value="<?php echo esc_attr( $service['final_step_url'] ) ?>"
    />
</div>