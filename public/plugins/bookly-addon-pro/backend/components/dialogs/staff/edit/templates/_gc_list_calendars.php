<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="form-group bookly-js-google-calendars-list">
    <label for="bookly-calendar-id"><?php esc_html_e( 'Calendar', 'bookly' ) ?></label>
    <select class="form-control custom-select" name="google_calendar_id" id="bookly-calendar-id">
        <option value=""><?php esc_html_e( '-- Select calendar --', 'bookly' ) ?></option>
        <?php foreach ( $calendars as $id => $calendar ) : ?>
            <option value="<?php echo esc_attr( $id ) ?>"<?php selected( $selected_calendar_id == $id ) ?>>
                <?php echo esc_html( $calendar['summary'] ) ?>
            </option>
        <?php endforeach ?>
    </select>
    <small class="form-text text-muted"><?php esc_html_e( 'When you connect a calendar all future and past events will be synchronized according to the selected synchronization mode. This may take a few minutes. Please wait.', 'bookly' ) ?></small>
</div>