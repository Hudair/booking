<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
echo $progress_tracker;
?>
<div class="bookly-box bookly-well">
    <div class="bookly-round"><i class="bookly-icon-sm bookly-icon-i"></i></div>
    <div>
        <?php echo $info ?>
    </div>
</div>
<div class='bookly-box bookly-nav-steps'>
    <button class="bookly-back-step bookly-js-back-step bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_button_back' ) ?></span>
    </button>
    <div class="<?php echo get_option( 'bookly_app_align_buttons_left' ) ? 'bookly-left' : 'bookly-right' ?>">
        <button class="bookly-next-step bookly-btn ladda-button" disabled="disabled">
            <span class="ladda-label"><?php echo Common::getTranslatedOption( 'bookly_l10n_step_payment_button_next' ) ?></span>
        </button>
    </div>
</div>