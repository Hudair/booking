<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Inputs;

Inputs::renderText( 'bookly_url_cancel_confirm_page_url', __( 'Appointment cancellation confirmation URL', 'bookly' ), __( 'Set the URL of an appointment cancellation confirmation page that is shown to clients when they press cancellation link.', 'bookly' ) );
