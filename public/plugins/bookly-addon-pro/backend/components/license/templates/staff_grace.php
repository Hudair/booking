<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div>
    <p><?php esc_html_e( 'Thank you for choosing Bookly Pro as your booking solution.', 'bookly' ) ?></p>
    <p><?php esc_html_e( 'Please contact your website administrator in order to verify the license.', 'bookly' ) ?></p>
    <p><?php echo strtr( esc_html__( 'If you do not verify the license within {days}, access to your bookings will be disabled.', 'bookly' ), $days_text ) ?></p>
</div>
<div class="btn-group-vertical align-left" role="group">
    <button type="button" class="btn btn-link" data-trigger="temporary-hide"><span class="text-warning"><i class="fas fa-fw fa-times-circle mr-1"></i><?php esc_html_e( 'Proceed to Bookly Pro without license verification', 'bookly' ) ?></span></button>
</div>