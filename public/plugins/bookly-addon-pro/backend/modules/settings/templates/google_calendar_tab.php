<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Settings\Inputs;
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Modules\Settings\Proxy;
use Bookly\Backend\Modules\Settings\Codes;
use Bookly\Backend\Components\Ace;
use Bookly\Lib\Config;
use BooklyPro\Lib\Google;
?>
<div class="tab-pane" id="bookly_settings_google_calendar">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'google_calendar' ) ) ?>">
        <div class="card-body">
            <div class="form-group">
                <h4><?php esc_html_e( 'Instructions', 'bookly' ) ?></h4>
                <p><?php esc_html_e( 'To find your client ID and client secret, do the following:', 'bookly' ) ?></p>
                <ol>
                    <li><?php _e( 'Go to the <a href="https://console.developers.google.com/" target="_blank">Google Developers Console</a>.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'Select a project, or create a new one.', 'bookly' ) ?></li>
                    <li><?php _e( 'Click in the upper left part to see a sliding sidebar. Next, click <b>API Manager</b>. In the list of APIs look for <b>Calendar API</b> and make sure it is enabled.', 'bookly' ) ?></li>
                    <li><?php _e( 'In the sidebar on the left, select <b>Credentials</b>.', 'bookly' ) ?></li>
                    <li><?php _e( 'Go to <b>OAuth consent screen</b> tab and give a name to the product, then click <b>Save</b>.', 'bookly' ) ?></li>
                    <li><?php _e( 'Go to <b>Credentials</b> tab and in <b>New credentials</b> drop-down menu select <b>OAuth client ID</b>.', 'bookly' ) ?></li>
                    <li><?php _e( 'Select <b>Web application</b> and create your project\'s OAuth 2.0 credentials by providing the necessary information. For <b>Authorized redirect URIs</b> enter the <b>Redirect URI</b> found below on this page. Click <b>Create</b>.', 'bookly' ) ?></li>
                    <li><?php _e( 'In the popup window look for the <b>Client ID</b> and <b>Client secret</b>. Use them in the form below on this page.', 'bookly' ) ?></li>
                    <li><?php printf( __( 'Important: for two-way sync, your website must use HTTPS. Google Calendar API will be able to send notifications to HTTPS address only if there is a valid SSL certificate installed on your web server. Follow the steps in this <a href="%s" target="_blank">document</a> to <b>verify and register your domain</b>.', 'bookly' ), 'https://developers.google.com/calendar/v3/push' ) ?></li>
                    <li><?php _e( 'Go to Staff Members, select a staff member and click <b>Connect</b> which is located at the bottom of the page.', 'bookly' ) ?></li>
                </ol>
            </div>
            <?php Inputs::renderText( 'bookly_gc_client_id', __( 'Client ID', 'bookly' ), __( 'The client ID obtained from the Developers Console', 'bookly' ) ) ?>
            <?php Inputs::renderText( 'bookly_gc_client_secret', __( 'Client secret', 'bookly' ), __( 'The client secret obtained from the Developers Console', 'bookly' ) ) ?>
            <div class="form-group">
                <label for="bookly-redirect-uri"><?php esc_html_e( 'Redirect URI', 'bookly' ) ?></label>
                <input id="bookly-redirect-uri" class="form-control" type="text" readonly
                       value="<?php echo Google\Client::generateRedirectURI() ?>" onclick="this.select();"
                       style="cursor: pointer;"
                />
                <small class="text-muted form-text"><?php esc_html_e( 'Enter this URL as a redirect URI in the Developers Console', 'bookly' ) ?></small>
            </div>
            <?php if ( Config::advancedGoogleCalendarActive() ) : ?>
                <?php Proxy\AdvancedGoogleCalendar::renderSettings() ?>
            <?php else : ?>
                <?php Selects::renderSingle( 'bookly_gc_sync_mode', __( 'Synchronization mode', 'bookly' ), __( 'With "One-way" sync Bookly pushes new appointments and any further changes to Google Calendar. With "Two-way front-end only" sync Bookly will additionally fetch events from Google Calendar and remove corresponding time slots before displaying the Time step of the booking form (this may lead to a delay when users click Next to get to the Time step).', 'bookly' ), array(
                    array(
                        '1-way',
                        __( 'One-way', 'bookly' ),
                    ),
                    array( '1.5-way', __( 'Two-way front-end only', 'bookly' ) ),
                ) ) ?>
            <?php endif ?>
            <div class="border-left ml-4 pl-3">
                <?php Selects::renderSingle( 'bookly_gc_limit_events', __( 'Limit number of fetched events', 'bookly' ), __( 'If there is a lot of events in Google Calendar sometimes this leads to a lack of memory in PHP when Bookly tries to fetch all events. You can limit the number of fetched events here.', 'bookly' ), $fetch_limits ) ?>
            </div>
            <?php Inputs::renderText( 'bookly_gc_event_title', __( 'Template for event title', 'bookly' ), __( 'Configure what information should be placed in the title of Google Calendar event. Available codes are {service_name}, {category_name}, {staff_name} and {client_names}.', 'bookly' ) ) ?>
            <div class="form-group">
                <label for="bookly_gc_event_description"><?php esc_html_e( 'Template for event description', 'bookly' ) ?></label>
                <?php Ace\Editor::render( 'bookly-settings-google-calendar', 'bookly_gc_event_description', Codes::getJson( 'google_calendar' ), get_option( 'bookly_gc_event_description', '' ) ) ?>
                <input type="hidden" name="bookly_gc_event_description" value="<?php echo esc_attr( get_option( 'bookly_gc_event_description', '' ) ) ?>">
            </div>
        </div>

        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>