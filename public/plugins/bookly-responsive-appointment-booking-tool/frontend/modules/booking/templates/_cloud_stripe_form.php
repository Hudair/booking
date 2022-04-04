<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
?>
<div class="bookly-gateway-buttons pay-cloud_stripe bookly-box bookly-nav-steps" style="display:none">
    <form action="" method="post" class="bookly-cloud_stripe-form" data-gateway="cloud_stripe">
        <input type="hidden" name="bookly_fid" value="<?php echo esc_attr( $form_id ) ?>"/>
        <input type="hidden" name="bookly_action" value="stripe-cloud-checkout"/>
        <input type="hidden" name="response_url" value="<?php echo esc_url( $page_url ) ?>"/>
        <button class="bookly-back-step bookly-js-back-step bookly-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40">
            <span class="ladda-label"><?php echo esc_html( Common::getTranslatedOption( 'bookly_l10n_button_back' ) ) ?></span>
        </button>
        <div class="<?php echo get_option( 'bookly_app_align_buttons_left' ) ? 'bookly-left' : 'bookly-right' ?>">
            <button class="bookly-next-step bookly-js-next-step bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
                <span class="ladda-label"><?php echo esc_html( Common::getTranslatedOption( 'bookly_l10n_step_payment_button_next' ) ) ?></span>
            </button>
        </div>
    </form>
</div>