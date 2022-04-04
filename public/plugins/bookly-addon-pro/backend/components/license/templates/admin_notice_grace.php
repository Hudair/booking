<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap">
    <div class="alert alert-info">
        <button type="button" class="close" data-dismiss="alert" data-trigger="temporary-hide">&times;</button>
        <div class="form-row">
            <div class="mr-3"><i class="fas fa-info-circle fa-2x"></i></div>
            <div class="col">
                <span class="h4"><?php esc_html_e( 'Bookly Pro - License verification required', 'bookly' ) ?></span>
                <p></p>
                <p><?php esc_html_e( 'Please verify your license by providing a valid purchase code. Upon providing the purchase code you will get access to software updates, including feature improvements and important security fixes.', 'bookly' ) ?></p>
                <p><?php echo strtr( __( 'If you do not provide a valid purchase code within {days}, access to your bookings will be disabled. <a href="{url}">Details</a>', 'bookly' ), $replace_data ) ?></p>
            </div>
        </div>
    </div>
</div>