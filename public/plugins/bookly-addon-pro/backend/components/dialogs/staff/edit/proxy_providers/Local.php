<?php
namespace BooklyPro\Backend\Components\Dialogs\Staff\Edit\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Staff\Edit\Proxy;
use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Config;
use BooklyPro\Lib\Zoom\Authentication;

/**
 * Class Local
 * @package BooklyPro\Backend\Components\Dialogs\Staff\Edit\ProxyProviders
 */
class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function enqueueAssets()
    {
        self::enqueueScripts( array(
            'module'   => array(
                /** bookly-staff-details.js enqueue in
                 * @see \Bookly\Backend\Components\Dialogs\Staff\Edit\Dialog::render
                 */
                'js/staff-edit-component.js' => array( 'bookly-backend-globals', 'bookly-staff-details.js' ),
                'js/staff-advanced.js' => array( 'bookly-staff-edit-component.js' ),
                'js/archive.js' => array( 'bookly-staff-advanced.js' ),
            ),
        ) );

        self::enqueueStyles( array(
            'module' => array( 'css/staff.css', ),
        ) );

        wp_localize_script( 'bookly-archive.js', 'BooklyL10nStaffEdit', array(
            'areYouSure' => __( 'Are you sure?', 'bookly' ),
            'saved' => __( 'Settings saved.', 'bookly' ),
            'activeStaffId' => self::parameter( 'staff_id' ),
            'zoomFailed' => __( 'Zoom connection failed', 'bookly' ),
            'zoomOAuthConnectRequired' => __( 'Zoom: OAuth2.0 connection needed', 'bookly' ),
        ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderArchivingComponents()
    {
        self::renderTemplate( 'archive_dialog' );
    }

    /**
     * @inheritDoc
     */
    public static function getAdvancedHtml( $staff, $tpl_data, $for_backend = true )
    {
        $zoom_authentication = Config::zoomAuthentication();
        $zoom = array(
            'credentials_required' => true,
            'options' => array(
                array( 'value' => Authentication::TYPE_DEFAULT, 'title' => __( 'Default', 'bookly' ), 'selected' => Authentication::TYPE_DEFAULT == $staff->getZoomAuthentication() ),
            ),
        );
        if ( $zoom_authentication === Authentication::TYPE_JWT ) {
            $zoom['credentials_required'] = ! ( Config::zoomJwtApiKey() && Config::zoomJwtApiSecret() );
            $zoom['options'][] = array( 'value' => $zoom_authentication, 'title' => 'JSON Web Tokens (JWT)', 'selected' => $zoom_authentication == $staff->getZoomAuthentication() );
        } elseif ( $zoom_authentication === Authentication::TYPE_OAuth ) {
            $zoom['credentials_required'] = ! ( Config::zoomOAuthClientId() && Config::zoomOAuthClientSecret() && Config::zoomOAuthToken() );
            $zoom['options'][] = array( 'value' => $zoom_authentication, 'title' => 'OAuth 2.0', 'selected' => $zoom_authentication == $staff->getZoomAuthentication() );
        }

        return self::renderTemplate( 'advanced_settings', compact( 'staff', 'tpl_data', 'for_backend', 'zoom' ), false );
    }

    /**
     * @inheritDoc
     */
    public static function renderAdvancedTab()
    {
        self::renderTemplate( 'advanced_tab' );
    }

    /**
     * @inheritDoc
     */
    public static function renderCreateWPUser()
    {
        printf( '<option value="create">%s</option>', esc_html__( 'Create WordPress user', 'bookly' ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderGoogleCalendarsList( array $calendars, $selected_calendar_id )
    {
        self::renderTemplate( '_gc_list_calendars', compact( 'calendars', 'selected_calendar_id' ) );
    }
}