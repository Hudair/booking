<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;
use BooklyPro\Backend\Components\Settings\Address;
use Bookly\Lib as BooklyLib;

Selects::renderSingle(
    'bookly_cst_required_address',
    __( 'Make address mandatory', 'bookly' ),
    __( 'If enabled, a customer will be required to enter address to proceed with a booking.', 'bookly' ),
    array(
        array( 0, __( 'Disabled', 'bookly' ) ),
        array( 1, __( 'Enabled', 'bookly' ) ),
    )
) ?>

<div class="form-group">
    <label for="bookly_cst_address_show_fields"><?php esc_html_e( 'Customer\'s address fields', 'bookly' ) ?></label>
    <div id="bookly_cst_address_show_fields">
        <?php Address::render() ?>
    </div>
    <small class="text-muted form-text"><?php esc_html_e( 'Choose address fields you want to request from the client.', 'bookly' ) ?></small>
</div>