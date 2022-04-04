<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
if ( ! isset( $download_invoice ) ) {
    $download_invoice = '';
}
echo Common::stripScripts( $progress_tracker );
?>
    <div class="bookly-box"><?php echo Common::html( $info_text ) ?></div>
<?php if ( get_option( 'bookly_app_show_start_over' ) || get_option( 'bookly_app_show_download_ics' ) || $download_invoice ) : ?>
    <div class="bookly-box bookly-nav-steps">
        <?php if ( get_option( 'bookly_app_show_start_over' ) ): ?>
            <div class="<?php echo get_option( 'bookly_app_align_buttons_left' ) ? 'bookly-left' : 'bookly-right' ?>">
                <button class="bookly-nav-btn bookly-js-start-over bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
                    <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_step_done_button_start_over' ) ?></span>
                </button>
            </div>
        <?php endif ?>
        <?php if ( get_option( 'bookly_app_show_download_ics' ) ): ?>
            <div class="<?php echo get_option( 'bookly_app_align_buttons_left' ) ? 'bookly-left' : 'bookly-right' ?>">
                <button class="bookly-nav-btn bookly-js-download-ics bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
                    <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_button_download_ics' ) ?></span>
                </button>
            </div>
        <?php endif ?>
        <?php echo Common::stripScripts( $download_invoice ) ?>
    </div>
<?php endif ?>