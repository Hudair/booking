<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** @var \Bookly\Lib\Cloud\API $cloud */
$balance = $cloud->account->getBalance();

if ( $balance <= 0 ) {
    $btn_class = 'btn-danger';
    $txt_class = 'text-danger';
} elseif ( $balance > 0 && $balance < 10 ) {
    $btn_class = 'btn-warning';
    $txt_class = '';
} else {
    $btn_class = 'btn-success';
    $txt_class = 'text-success';
}
?>
<div class="btn-group mr-2">
    <div class="border border-right-0 rounded pl-2 d-flex align-items-center">
        <h6 class="small m-0"><b><?php _e( 'current<br/>balance', 'bookly' ) ?></b></h6>
    </div>
    <div class="border border-left-0 px-2 d-flex align-items-center">
        <span class="lead <?php echo esc_attr( $txt_class ) ?>">$<?php echo number_format( $balance, 2 ) ?></span>
    </div>
    <button type="button" class="btn <?php echo esc_attr( $btn_class ) ?> text-nowrap bookly-js-recharge-dialog-activator">
        <i class="fas fa-coins"></i><span class="d-none d-md-inline ml-2"><?php esc_html_e( 'Recharge', 'bookly' ) ?></span>
    </button>
</div>
