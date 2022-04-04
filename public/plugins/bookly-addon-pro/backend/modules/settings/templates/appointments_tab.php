<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;
/** @var array $slot_lengths */
/** @var integer $time_delimiter */
?>
<?php Selects::renderSingle( 'bookly_appointments_main_value', __( 'First value for newly created appointments via backend', 'bookly' ), __( 'Select what value should be selected first when creating a new appointment via backend.', 'bookly' ), array( array( 'provider', __( 'Provider', 'bookly' ) ), array( 'service', __( 'Service', 'bookly' ) ) ) ) ?>
<?php Selects::renderSingle( 'bookly_appointments_displayed_time_slots', __( 'Displayed time slots', 'bookly' ), __( 'Select what time slots will be shown when creating a new appointment via backend.', 'bookly' ), array( array( 'all', __( 'All', 'bookly' ) ), array( 'appropriate', __( 'Only appropriate slots', 'bookly' ) ) ) ) ?>
<div class="form-group border-left mt-3 ml-4 pl-3">
    <label for="bookly_appointments_time_delimiter"><?php esc_html_e( 'Time delimiter', 'bookly' ) ?></label>
    <select id="bookly_appointments_time_delimiter" class="form-control custom-select" name="bookly_appointments_time_delimiter">
        <?php foreach ( $slot_lengths as $slot ) : ?>
            <option value="<?php echo $slot[0] ?>"<?php selected( $slot[0] == $time_delimiter ) ?>><?php echo $slot[1] ?></option>
        <?php endforeach ?>
    </select>
    <small class="form-text text-muted"><?php esc_html_e( 'This setting allows you to set a delimiter during a day for appointments created via backend.', 'bookly' ) ?></small>
</div>
<?php Selects::renderSingle( 'bookly_appointment_cancel_action', __( 'Cancel appointment action', 'bookly' ), __( 'Select what happens when customer clicks cancel appointment link. With "Delete" the appointment will be deleted from the calendar. With "Cancel" only appointment status will be changed to "Cancelled".', 'bookly' ), array( array( 'delete', __( 'Delete', 'bookly' ) ), array( 'cancel', __( 'Cancel', 'bookly' ) ) ) ) ?>