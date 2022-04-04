<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Editable\Elements;
?>
<div class="bookly-js-payment-tips">
    <div class="bookly-box bookly-list">
        <?php Elements::renderString( array( 'bookly_l10n_label_tips', 'bookly_l10n_tips_error' ) ) ?>
        <div class="bookly-inline-block">
            <input class="bookly-user-tips" type="text"/>
            <div class="bookly-btn bookly-inline-block">
                <?php Elements::renderString( array( 'bookly_l10n_button_apply_tips', 'bookly_l10n_button_applied_tips' ) ) ?>
            </div>
        </div>
    </div>
</div>