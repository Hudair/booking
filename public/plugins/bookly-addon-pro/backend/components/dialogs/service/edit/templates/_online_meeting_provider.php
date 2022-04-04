<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Modules\Settings\Page as SettingsPage;
use Bookly\Lib\Utils\Common;
/**
 * @var array $service
 */
?>
<div class="form-group">
    <label for="bookly-online-meetings">
        <?php esc_html_e( 'Create online meetings', 'bookly' ) ?>
    </label>
    <select id="bookly-online-meetings" class="form-control custom-select" name="online_meetings">
        <option value="off"><?php esc_html_e( 'OFF', 'bookly' ) ?></option>
        <option value="zoom"<?php selected( 'zoom', $service['online_meetings'] ) ?>>Zoom</option>
        <option value="google_meet"<?php selected( 'google_meet', $service['online_meetings'] ) ?>>Google Meet</option>
        <option value="jitsi"<?php selected( 'jitsi', $service['online_meetings'] ) ?>>Jitsi Meet</option>
    </select>
    <small class="form-text text-muted"><?php printf( __( 'If this setting is enabled then online meetings will be created for new appointments with the selected online meeting provider. Make sure that the provider is configured properly in Settings > <a href="%s">Online Meetings</a>', 'bookly' ), Common::escAdminUrl( SettingsPage::pageSlug(), array( 'tab' => 'online_meetings' ) ) ) ?></small>
    <small class="form-text text-muted"><?php esc_html_e( 'If you choose Google Meet then meetings will be created for those staff members who have Google Calendar configured.', 'bookly' ) ?></small>
</div>