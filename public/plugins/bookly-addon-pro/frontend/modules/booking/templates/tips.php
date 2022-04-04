<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
/** @var BooklyLib\UserBookingData $userData */
?>
<div class="bookly-box bookly-list">
    <?php echo BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_tips' ) ?>
    <input class="bookly-user-tips" name="bookly-tips" type="number" value="<?php echo esc_attr( (float) $userData->getTips() ) ?>" min="0"/>
    <button class="bookly-btn ladda-button bookly-js-apply-tips" data-style="zoom-in" data-spinner-size="40" style="<?php if ( $userData->getTips() !== null ) : ?>display: none;<?php else : ?>display: inline-block;<?php endif ?>">
        <span class="ladda-label"><?php echo BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_button_apply_tips' ) ?></span><span class="spinner"></span>
    </button>
    <button class="bookly-btn ladda-button bookly-js-applied-tips" data-style="zoom-in" data-spinner-size="40" style="<?php if ( $userData->getTips() === null ) : ?>display: none;<?php else : ?>display: inline-block;<?php endif ?>">
        <span class="ladda-label"><?php echo BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_button_applied_tips' ) ?></span><span class="spinner"></span>
    </button>
    <div class="bookly-label-error bookly-js-tips-error"></div>
</div>