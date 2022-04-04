<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Cloud\Recharge\Amounts\Auto\Button;
?>
<div class="bookly-js-auto-recharge-container">
    <div class="form-row mb-3">
        <div class="col"><hr/></div>
        <div class="col-auto"><h5 class="text-muted"><?php esc_html_e( 'or', 'bookly' ) ?></h5></div>
        <div class="col"><hr/></div>
    </div>
    <div class="card bg-light">
        <div class="card-body pb-1">
            <div class="text-center font-weight-bolder mb-2"><?php esc_html_e( 'Turn on Auto-Recharge and get even more', 'bookly' ) ?></div>
            <div class="mx-auto w-50">
                <?php Button::renderSelector() ?>
            </div>
            <h6 class="text-center mt-2"><?php printf( __( 'We will only charge you when your balance falls below %s', 'bookly' ), '<b>$10</b>' ) ?></h6>
        </div>
    </div>
</div>