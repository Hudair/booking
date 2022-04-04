<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs" class="wrap">
    <div id="bookly-js-call-to-buy" class="alert alert-warning">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <div class="form-row">
            <div class="mr-3"><i class="fas fa-info-circle fa-2x"></i></div>
            <div class="col">
                <?php $remote = wp_remote_get( 'http://www.booking-wp-plugin.com/legal-notice.html', array( 'timeout' => 1, ) );
                if ( is_wp_error( $remote ) ) {
                    foreach ( (array) get_option( 'bookly_pr_data' ) as $row ) {
                        echo '<p>' . base64_decode( $row ) . '</p>';
                    }
                } else {
                    echo $remote['body'];
                } ?>
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(function ($) {
        $('#bookly-js-call-to-buy').on('close.bs.alert', function () {
            $.post(ajaxurl, {action: 'bookly_pro_dismiss_purchase_reminder', csrf_token: '<?php echo \Bookly\Lib\Utils\Common::getCsrfToken() ?>'});
        });
    });
</script>