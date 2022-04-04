<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Settings\Proxy\Pro;
?>
<div class="tab-pane" id="bookly_settings_purchase_code">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'purchase_code' ) ) ?>" id="purchase_code">
        <div class="card-body">
            <div class="form-group">
                <h4><?php esc_html_e( 'Instructions', 'bookly' ) ?></h4>
                <p><?php _e( 'Upon providing the purchase code you will have access to free updates of Bookly. Updates may contain functionality improvements and important security fixes. For more information on where to find your purchase code see this <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code-" target="_blank">page</a>.', 'bookly' ) ?></p>
                <?php if ( $grace_remaining_days > 0 ) :
                    $days_text = array( '{days}' => sprintf( _n( '%d day', '%d days', $grace_remaining_days, 'bookly' ), $grace_remaining_days ) ) ?>
                    <p><?php echo strtr( esc_html__( 'If you do not provide a valid purchase code within {days}, access to your bookings will be disabled.', 'bookly' ), $days_text ) ?></p>
                <?php endif ?>
            </div>
            <?php Pro::renderPurchaseCode() ?>
        </div>

        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>