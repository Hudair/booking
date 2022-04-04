<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Frontend\Modules\Booking\Proxy;
use BooklyPro\Lib\Payment\PayPal;
?>
<div class="bookly-gateway-buttons pay-paypal bookly-box bookly-nav-steps" style="display:none">
    <?php if ( $type == PayPal::TYPE_EXPRESS_CHECKOUT ) :
        PayPal::renderECForm( $form_id );
    elseif ( $type == PayPal::TYPE_PAYMENTS_STANDARD ) :
        Proxy\PaypalPaymentsStandard::renderPaymentForm( $form_id, $page_url );
    elseif ( $type == PayPal::TYPE_CHECKOUT ) :
        Proxy\PaypalCheckout::renderPaymentForm( $form_id, $page_url );
    endif ?>
</div>