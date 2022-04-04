<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Cloud\Recharge\Amounts\Manual;
use Bookly\Backend\Components\Cloud\Recharge\Amounts\Auto;
?>
<h4 class="text-center"><?php esc_html_e( 'Please select an amount and recharge your account', 'bookly' ) ?></h4>
<?php Manual\Button::renderRecharges() ?>
<?php Auto\Button::renderRecharges() ?>
<div class="row mt-3 text-center" style="color:#595959">
    <div class="col"><i class="fab fa-2x fa-cc-paypal"></i></div>
    <div class="col"><i class="fab fa-2x fa-cc-mastercard"></i></div>
    <div class="col"><i class="fab fa-2x fa-cc-visa"></i></div>
    <div class="col"><i class="fab fa-2x fa-cc-amex"></i></div>
    <div class="col"><i class="fab fa-2x fa-cc-discover"></i></div>
</div>