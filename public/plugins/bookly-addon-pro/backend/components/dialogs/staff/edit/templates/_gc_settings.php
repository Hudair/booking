<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Modules\Settings\Page as SettingsPage;
use Bookly\Lib\Utils\Common;
use BooklyPro\Backend\Components\Dialogs\Staff\Edit\ProxyProviders\Local;
?>
<div class="form-group bookly-js-google-calendar-row">
    <label><?php esc_html_e( 'Google Calendar integration', 'bookly' ) ?></label>
    <div>
        <?php if ( isset ( $auth_url ) ) : ?>
            <?php if ( $auth_url ) : ?>
                <a href="<?php echo esc_url( $auth_url ) ?>" class="bookly-google-signin-button"></a>
            <?php else : ?>
                <?php printf( __( 'Please configure Google Calendar <a href="%s">settings</a> first', 'bookly' ), Common::escAdminUrl( SettingsPage::pageSlug(), array( 'tab' => 'google_calendar' ) ) ) ?>
            <?php endif ?>
        <?php else : ?>
            <?php esc_html_e( 'Connected', 'bookly' ) ?> (
            <span class="custom-control custom-checkbox d-inline-block">
                <input class="custom-control-input" id="google_disconnect" type="checkbox" name="google_disconnect" value="1">
                <label class="custom-control-label" for="google_disconnect"><?php esc_html_e( 'disconnect', 'bookly' ) ?></label>
            </span>
            )
        <?php endif ?>
    </div>
    <small class="form-text text-muted"><?php esc_html_e( 'Synchronize staff member appointments with Google Calendar.', 'bookly' ) ?></small>
</div>
<?php if ( ! isset ( $auth_url ) ) Local::renderGoogleCalendarsList( $calendars, $selected_calendar_id ) ?>