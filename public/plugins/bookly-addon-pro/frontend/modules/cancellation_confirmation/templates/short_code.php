<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $color = get_option( 'bookly_app_color', '#f4662f' );
?>
<style>
    .bookly-btn-default {
        padding: 9px 18px!important;
        border: 1px solid #b1bbc4 !important;
        min-width: 118px;
        display: block;
        text-align: center;
        border-radius: 4px!important;
        background-color: #fff;
        cursor: pointer!important;
        height: auto!important;
        outline: none!important;
        float: left;
        width: auto;
    }

    .bookly-cancellation-reason {
        margin: 15px 0!important;
    }

    .bookly-btn-default.bookly-btn-active {
        border: 1px solid <?php echo $color ?> !important;
        background-color: <?php echo $color ?>!important;
    }

    .bookly-btn-default:hover {
        text-decoration: unset!important;
    }

    .bookly-btn-default, .bookly-btn-default > span {
        color: #212529 !important;
        font-size: 18px!important;
        line-height: 17px!important;
        font-weight: bold!important;
        text-transform: uppercase!important;
    }
    .bookly-btn-default.bookly-btn-active > span {
        color: #fff!important;
    }
</style>
<div id="bookly-tbs" class="bookly-js-cancellation-confirmation">
    <div class="bookly-js-cancellation-confirmation-buttons">
        <?php if ( isset( $attributes['reason'] ) && $attributes['reason'] ) : ?>
        <div class="bookly-cancellation-reason">
            <label for="bookly-cancellation-reason"><?php esc_html_e( 'Cancellation reason', 'bookly' ) ?></label>
            <input type="text" id="bookly-cancellation-reason" class="bookly-js-cancellation-confirmation-reason"/>
        </div>
        <?php endif ?>
        <a href="<?php echo admin_url( 'admin-ajax.php?action=bookly_cancel_appointment&token=' . $token ) ?>" class="bookly-btn-default bookly-js-cancellation-confirmation-yes" style="margin-right: 12px">
            <span><?php esc_html_e( 'Confirm cancellation', 'bookly' ) ?></span>
        </a>
        <a href="#" class="bookly-js-cancellation-confirmation-no bookly-btn-default bookly-btn-active">
            <span><?php esc_html_e( 'Do not cancel', 'bookly' ) ?></span>
        </a>
    </div>
    <div class="bookly-js-cancellation-confirmation-message bookly-row" style="display: none">
        <p class="bookly-bold">
            <?php esc_html_e( 'Thank you for being with us', 'bookly' ) ?>
        </p>
    </div>
</div>
<script type="text/javascript">
    // Click on 'Do not cancel' button
    var links = document.getElementsByClassName('bookly-js-cancellation-confirmation-no');
    for (var i = 0; i < links.length; i++) {
        if (links[i].onclick == undefined) {
            links[i].onclick = function (e) {
                e.preventDefault();
                var container = this.closest('.bookly-js-cancellation-confirmation'),
                    buttons = container.getElementsByClassName('bookly-js-cancellation-confirmation-buttons')[0],
                    message = container.getElementsByClassName('bookly-js-cancellation-confirmation-message')[0];
                buttons.style.display = 'none';
                message.style.display = 'inline';
            };
        }
    }
    // Click on 'Confirm cancellation' button
    links = document.getElementsByClassName('bookly-js-cancellation-confirmation-yes');
    for (i = 0; i < links.length; i++) {
        if (links[i].onclick == undefined) {
            links[i].onclick = function (e) {
                e.preventDefault();
                var url = this.href,
                    $reason = this.closest('.bookly-js-cancellation-confirmation-buttons').querySelector('.bookly-js-cancellation-confirmation-reason');
                if ($reason !== null) {
                    url += String.fromCharCode(38) + 'reason=' + encodeURIComponent($reason.value)
                }
                window.location.replace(url)
            };
        }
    }
</script>