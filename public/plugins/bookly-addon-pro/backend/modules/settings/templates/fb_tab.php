<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Settings\Inputs;
?>
<div class="tab-pane" id="bookly_settings_facebook">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'facebook' ) ) ?>">
        <div class="card-body">
            <div class="form-group">
                <h4><?php esc_html_e( 'Instructions', 'bookly' ) ?></h4>
                <p><?php esc_html_e( 'To set up Facebook integration, do the following:', 'bookly' ) ?></p>
                <ol>
                    <li><?php _e( 'Follow the steps at <a href="https://developers.facebook.com/docs/apps/register" target="_blank">https://developers.facebook.com/docs/apps/register</a> to create a Developer Account, register and configure your <b>Facebook App</b>. Then you\'ll need to submit your app for review. Learn more about the review process and what\'s required to pass review in the <a href="https://developers.facebook.com/docs/facebook-login/review" target="_blank">Login Review Guide</a>.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'Below the App Details Panel click Add Platform button, select Website and enter your website URL.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'Go to your App Dashboard. In the left side navigation panel of the App Dashboard, click Settings > Basic to view the App Details Panel with your App ID. Use it in the form below.', 'bookly' ) ?></li>
                </ol>
            </div>
            <?php Inputs::renderText( 'bookly_fb_app_id', __( 'App ID', 'bookly' ) ) ?>
        </div>

        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>