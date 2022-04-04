<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** @var array $service */
/** @var array $min_time_prior_cancel */
/** @var array $min_time_prior_booking */
?>
<div class="form-group">
    <label for="bookly-min-time-prior-booking_<?php echo $service['id'] ?>">
        <?php esc_html_e( 'Minimum time requirement prior to booking', 'bookly' ) ?>
    </label>
    <select id="bookly-min-time-prior-booking_<?php echo $service['id'] ?>" class="form-control custom-select" name="min_time_prior_booking">
        <?php foreach ( $min_time_prior_booking as $option ) : ?>
            <option value="<?php echo $option[0] ?>" <?php selected( $option[0], $service['min_time_prior_booking'] ) ?>><?php echo $option[1] ?></option>
        <?php endforeach ?>
    </select>
    <small class="form-text text-muted"><?php esc_html_e( 'Set how late appointments can be booked (for example, require customers to book at least 1 hour before the appointment time).', 'bookly' ) ?></small>
</div>
<div class="form-group">
    <label for="bookly-min-time-prior-cancel_<?php echo $service['id'] ?>">
        <?php esc_html_e( 'Minimum time requirement prior to canceling', 'bookly' ) ?>
    </label>
    <select id="bookly-min-time-prior-cancel_<?php echo $service['id'] ?>" class="form-control custom-select" name="min_time_prior_cancel">
        <?php foreach ( $min_time_prior_cancel as $option ) : ?>
            <option value="<?php echo $option[0] ?>" <?php selected( $option[0], $service['min_time_prior_cancel'] ) ?>><?php echo $option[1] ?></option>
        <?php endforeach ?>
    </select>
    <small class="form-text text-muted"><?php esc_html_e( 'Set how late appointments can be cancelled (for example, require customers to cancel at least 1 hour before the appointment time).', 'bookly' ) ?></small>
</div>