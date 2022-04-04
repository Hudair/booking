<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Modules\Appearance\Codes;
use Bookly\Backend\Components\Editable\Elements;
?>
<div class="bookly-box bookly-js-payment-several-apps">
    <?php Elements::renderText( 'bookly_l10n_info_payment_step_several_apps', Codes::getJson( 7, true ) ) ?>
</div>
<div class="bookly-box bookly-js-payment-100percents-off-price">
    <?php Elements::renderText( 'bookly_l10n_info_payment_step_with_100percents_off_price', Codes::getJson( 7 ) ) ?>
</div>