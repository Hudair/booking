<?php
namespace BooklyPro\Backend\Modules\Settings;

use Bookly\Backend\Modules\Settings\Page as SettingsPage;
use Bookly\Backend\Modules\Staff\Page as StaffPage;
use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

/**
 * Class Ajax
 * @package BooklyPro\Backend\Modules\Settings
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array(
            'getZoomAuthorizationUrl' => array( 'staff' ),
            'requestZoomAccessToken' => array( 'staff' ),
        );
    }

    public static function getZoomAuthorizationUrl()
    {
        // staff_id optional parameter
        $staff_id     = self::parameter( 'staff_id' );
        $redirect_uri = add_query_arg( array(
            'action'     => 'bookly_pro_request_zoom_access_token',
            'staff_id'   => $staff_id,
            'csrf_token' => BooklyLib\Utils\Common::getCsrfToken(),
        ), admin_url( 'admin-ajax.php' ) );

        $staff = BooklyLib\Entities\Staff::find( $staff_id );
        // When click Zoom connect set authorization OAuth
        if ( $staff ) {
            set_site_transient( 'bookly_zoom_return_url_' . $staff_id,
                self::parameter( 'layout' ) == 'frontend'
                    ? self::parameter( 'page_url' )
                    : add_query_arg( array( 'page' => StaffPage::pageSlug(), 'tab' => 'advanced', 'staff_id' => $staff_id ), admin_url( 'admin.php' ) )
            );
            $staff->setZoomAuthentication( Lib\Zoom\Authentication::TYPE_OAuth )->save();
        }

        wp_send_json_success( array( 'authorization_url' => self::oauth( $staff_id )->getAuthorizationUrl( $redirect_uri ) ) );
    }

    public static function requestZoomAccessToken()
    {
        // staff_id optional parameter
        $staff_id = self::parameter( 'staff_id' );
        $redirect_uri = add_query_arg( array(
            'action'     => 'bookly_pro_request_zoom_access_token',
            'staff_id'   => $staff_id,
            'csrf_token' => self::parameter( 'csrf_token' ),
        ), admin_url( 'admin-ajax.php' ) );

        $oauth = self::oauth( $staff_id );
        if ( $staff_id ) {
            $zoom_config_url = get_site_transient( 'bookly_zoom_return_url_' . $staff_id );
            delete_transient( 'bookly_zoom_return_url_' . $staff_id );
        } else {
            $zoom_config_url = add_query_arg( array( 'page' => SettingsPage::pageSlug(), 'tab' => 'online_meetings' ), admin_url( 'admin.php' ) );
        }

        if ( $oauth->requestAccessToken( $redirect_uri ) ) {
            if ( ( $staff_id === null ) && ( Lib\Config::zoomAuthentication() != Lib\Zoom\Authentication::TYPE_OAuth ) ) {
                update_option( 'bookly_zoom_authentication', Lib\Zoom\Authentication::TYPE_OAuth );
                // Reset zoom authorization to default for all staff
                BooklyLib\Entities\Staff::query()->update()->set( 'zoom_authentication', Lib\Zoom\Authentication::TYPE_DEFAULT )->execute();
            }
            wp_redirect( $zoom_config_url );
        } else {
            wp_redirect( $zoom_config_url . '#zoom-failed' );
        }
    }

    public static function disconnectZoom()
    {
        // staff_id optional parameter
        self::oauth( self::parameter( 'staff_id' ) )->revokeToken()
            ? wp_send_json_success()
            : wp_send_json_error();
    }

    /**
     * @param int|null $staff_id
     * @return Lib\Zoom\OAuth
     */
    private static function oauth( $staff_id = null )
    {
        if ( $staff_id ) {
            $staff = new BooklyLib\Entities\Staff();
            $staff->load( $staff_id );

            return Lib\Zoom\OAuth::createForStaff( $staff );
        }

        return Lib\Zoom\OAuth::createDefault();
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        $excluded_actions = static::parameter( 'csrf_token' )
            ? array()
            : array( 'requestZoomAccessToken' );

        return in_array( $action, $excluded_actions ) || parent::csrfTokenValid( $action );
    }
}