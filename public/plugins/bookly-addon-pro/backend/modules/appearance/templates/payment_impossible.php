<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Editable\Elements;
use Bookly\Backend\Modules\Appearance\Codes;
?>
<div class="bookly-js-payment-impossible">
    <div class="bookly-box bookly-well">
        <div class="bookly-round"><i class="bookly-icon-sm bookly-icon-i"></i></div>
        <div>
            <?php Elements::renderText( 'bookly_l10n_info_payment_step_without_intersected_gateways', Codes::getJson( 7 ) ) ?>
        </div>
    </div>
</div>