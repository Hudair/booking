<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var array $service
 * @var array $preferences
 * @var array $staff_preference
 */
?>
<div class="form-group">
    <label for="staff_preference_<?php echo $service['id'] ?>">
        <?php esc_html_e( 'Providers preference for ANY', 'bookly' ) ?>
    </label>
    <select id="staff_preference_<?php echo $service['id'] ?>" class="form-control custom-select" name="staff_preference" data-default="[<?php echo $staff_preference[0] ?>]">
        <?php foreach ( $preferences as $rule => $name ) : ?><option value="<?php echo $rule ?>" <?php selected( $rule == $service['staff_preference'] ) ?>><?php echo $name ?></option><?php endforeach ?>
    </select>
    <small class="form-text text-muted"><?php esc_html_e( 'Allows you to define the rule of staff members auto assignment when ANY option is selected', 'bookly' ) ?></small>
</div>
<div class="form-group bookly-js-preferred-staff-order border-left ml-4 pl-3">
    <label for="staff_preferred_<?php echo $service['id'] ?>"><?php esc_html_e( 'Providers', 'bookly' ) ?></label><br/>
    <div class="bookly-js-preferred-staff-list row"></div>
</div>
<div class="border-left ml-4 pl-3">
    <div class="form-group bookly-js-preferred-period">
        <label for="staff_preferred_period_before_<?php echo $service['id'] ?>"><?php esc_html_e( 'Period (before and after)', 'bookly' ) ?></label>
        <div class="form-row">
            <div class="col-6">
                <input id="staff_preferred_period_before_<?php echo $service['id'] ?>" class="form-control" min="0" step="1" name="staff_preferred_period_before" value="<?php echo (int) $settings['period']['before'] ?>" type="number" />
            </div>
            <div class="col-6">
                <input id="staff_preferred_period_after_<?php echo $service['id'] ?>" class="form-control" min="0" step="1" name="staff_preferred_period_after" value="<?php echo (int) $settings['period']['after'] ?>" type="number" />
            </div>
        </div>
        <small class="form-text text-muted"><?php esc_html_e( 'Set number of days before and after appointment that will be taken into account when calculating provider\'s occupancy. 0 means the day of booking.', 'bookly' ) ?></small>
    </div>
    <div class="form-group bookly-js-preferred-random-staff">
        <label for="bookly-preferred-random-staff"><?php esc_html_e( 'Pick random staff member in case of uncertainty', 'bookly' ) ?></label>
        <div class="custom-control custom-radio">
            <input type="radio" id="bookly-preferred-random-staff-0" name="staff_preferred_random" value="0"<?php checked( (int) $settings['random'] != 1 ) ?> class="custom-control-input" />
            <label for="bookly-preferred-random-staff-0" class="custom-control-label"><?php esc_html_e( 'Disabled', 'bookly' ) ?></label>
        </div>
        <div class="custom-control custom-radio">
            <input type="radio" id="bookly-preferred-random-staff-1" name="staff_preferred_random" value="1"<?php checked( (int) $settings['random'], 1 ) ?> class="custom-control-input" />
            <label for="bookly-preferred-random-staff-1" class="custom-control-label"><?php esc_html_e( 'Enabled', 'bookly' ) ?></label>
        </div>
        <small class="form-text text-muted"><?php esc_html_e( 'Enable this option to pick a random staff member if both meet the criteria chosen in "Providers preference for ANY". Otherwise the selection order is unknown.', 'bookly' ) ?></small>
    </div>
</div>