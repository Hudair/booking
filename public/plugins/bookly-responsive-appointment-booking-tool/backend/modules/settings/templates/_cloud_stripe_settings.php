<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components;
use Bookly\Lib\Utils\DateTime;
use Bookly\Lib\Plugin;
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Controls\Elements;
use Bookly\Lib\Entities\Payment;
?>
<div class="card bookly-collapse" data-slug="cloud_stripe">
    <div class="card-header d-flex align-items-center">
        <?php Elements::renderReorder() ?>
        <a href="#bookly_pmt_cloud_stripe" class="ml-2" role="button" data-toggle="collapse">
            Stripe Cloud
        </a>
        <img class="ml-auto" src="<?php echo plugins_url( 'frontend/modules/stripe/resources/images/stripe.png', Plugin::getMainFile() ) ?>" />
    </div>
    <div id="bookly_pmt_cloud_stripe" class="collapse show">
        <div class="card-body">
            <?php Selects::renderSingle( 'bookly_cloud_stripe_enabled' ) ?>
            <div class="bookly-cloud_stripe">
            <?php Components\Settings\Payments::renderPriceCorrection( Payment::TYPE_CLOUD_STRIPE ) ?>
            <?php
            $values = array( array( '0', __( 'OFF', 'bookly' ) ) );
            foreach ( array_merge( range( 1, 23, 1 ), range( 24, 168, 24 ), array( 336, 504, 672 ) ) as $hour ) {
                $values[] = array( $hour * HOUR_IN_SECONDS, DateTime::secondsToInterval( $hour * HOUR_IN_SECONDS ) );
            }
            Selects::renderSingle( 'bookly_cloud_stripe_timeout', __( 'Time interval of payment gateway', 'bookly' ), __( 'This setting determines the time limit after which the payment made via the payment gateway is considered to be incomplete. This functionality requires a scheduled cron job.', 'bookly' ), $values );
            ?>
            </div>
        </div>
    </div>
</div>