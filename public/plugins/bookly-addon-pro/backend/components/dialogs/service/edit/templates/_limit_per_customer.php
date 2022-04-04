<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var array $service
 */
?>
<div class="form-group">
    <label for="bookly-limit-period">
        <?php esc_html_e( 'Limit appointments per customer', 'bookly' ) ?>
    </label>
    <select id="bookly-limit-period" class="form-control custom-select" name="limit_period">
        <option value="off"><?php esc_html_e( 'OFF', 'bookly' ) ?></option>
        <option value="upcoming"<?php selected( 'upcoming', $service['limit_period'] ) ?>><?php esc_html_e( 'upcoming', 'bookly' ) ?></option>
        <option value="day"<?php selected( 'day', $service['limit_period'] ) ?>><?php esc_html_e( 'per 24 hours', 'bookly' ) ?></option>
        <option value="calendar_day"<?php selected( 'calendar_day', $service['limit_period'] ) ?>><?php esc_html_e( 'per day', 'bookly' ) ?></option>
        <option value="week"<?php selected( 'week', $service['limit_period'] ) ?>><?php esc_html_e( 'per 7 days', 'bookly' ) ?></option>
        <option value="calendar_week"<?php selected( 'calendar_week', $service['limit_period'] ) ?>><?php esc_html_e( 'per week', 'bookly' ) ?></option>
        <option value="month"<?php selected( 'month', $service['limit_period'] ) ?>><?php esc_html_e( 'per 30 days', 'bookly' ) ?></option>
        <option value="calendar_month"<?php selected( 'calendar_month', $service['limit_period'] ) ?>><?php esc_html_e( 'per month', 'bookly' ) ?></option>
        <option value="year"<?php selected( 'year', $service['limit_period'] ) ?>><?php esc_html_e( 'per 365 days', 'bookly' ) ?></option>
        <option value="calendar_year"<?php selected( 'calendar_year', $service['limit_period'] ) ?>><?php esc_html_e( 'per year', 'bookly' ) ?></option>
    </select>
    <small class="form-text text-muted"><?php esc_html_e( 'This setting allows you to limit the number of appointments that can be booked by a customer in any given period. Restriction may end after a fixed period or with the beginning of the next calendar period - new day, week, month, etc.', 'bookly' ) ?></small>
</div>
<div class="form-group border-left ml-4 pl-3"<?php if ( $service['limit_period'] == 'off' ) : ?> style="display: none;"<?php endif ?>>
    <label for="bookly-appointments-limit">
        <?php esc_html_e( 'Limit', 'bookly' ) ?>
    </label>
    <input id="bookly-appointments-limit" class="form-control" type="number" min="0" step="1" name="appointments_limit" value="<?php echo esc_attr( $service['appointments_limit'] ) ?>" />
</div>