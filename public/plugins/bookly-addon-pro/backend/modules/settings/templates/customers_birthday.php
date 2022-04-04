<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;

Selects::renderSingle(
    'bookly_cst_required_birthday',
    __( 'Make birthday mandatory', 'bookly' ),
    __( 'If enabled, a customer will be required to enter a date of birth to proceed with a booking.', 'bookly' )
);