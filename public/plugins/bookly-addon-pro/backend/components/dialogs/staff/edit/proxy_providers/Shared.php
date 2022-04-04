<?php
namespace BooklyPro\Backend\Components\Dialogs\Staff\Edit\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Staff\Edit\Proxy;
use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Staff;
use BooklyPro\Lib;

/**
 * Class Shared
 * @package BooklyPro\Backend\Components\Dialogs\Staff\Edit\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function editStaffAdvanced( array $data, Staff $staff )
    {
        if ( $gc_errors = BooklyLib\Session::get( 'staff_google_auth_error' ) ) {
            foreach ( (array) json_decode( $gc_errors, true ) as $error ) {
                $data['alert']['error'][] = $error;
            }
            BooklyLib\Session::destroy( 'staff_google_auth_error' );
        }

        $auth_url = null;
        $calendars = array();
        $selected_calendar_id = null;
        if ( $staff->getGoogleData() == '' ) {
            if ( Lib\Config::getGoogleCalendarSyncMode() !== null ) {
                $google  = new Lib\Google\Client();
                $auth_url = $google->createAuthUrl( $staff->getId() );
            } else {
                $auth_url = false;
            }
        } else {
            $google = new Lib\Google\Client();
            if ( $google->auth( $staff, true ) && ( $list = $google->getCalendarList() ) !== false ) {
                $calendars = $list;
                $selected_calendar_id = $google->data()->calendar->id;
            } else {
                foreach ( $google->getErrors() as $error ) {
                    $data['alert']['error'][] = $error;
                }
            }
        }

        $data['tpl']['gc'] = compact( 'staff', 'auth_url', 'calendars', 'selected_calendar_id' );

        return $data;
    }

    /**
     * @inheritDoc
     */
    public static function renderStaffDetails( $staff )
    {
        $categories = Lib\Entities\StaffCategory::query()->sortBy( 'position' )->fetchArray();
        $gateways = BooklyLib\Utils\Common::getGateways();

        self::renderTemplate( 'staff_details', compact( 'categories', 'staff', 'gateways' ) );
    }

    /**
     * @inheritDoc
     */
    public static function preUpdateStaffDetails( array $data, Staff $staff, array $params )
    {
        if ( BooklyLib\Utils\Common::isCurrentUserAdmin() ) {
            if ( isset( $params['wp_user_id'] ) && $params['wp_user_id'] === 'create' ) {
                $exists_active_notification = BooklyLib\Entities\Notification::query()
                    ->where( 'type', BooklyLib\Entities\Notification::TYPE_STAFF_NEW_WP_USER )
                    ->where( 'active', 1 )
                    ->limit( 1 )
                    ->count();
                if ( $exists_active_notification ) {
                    // Try to create WordPress user
                    try {
                        $wp_user = Lib\Utils\Common::createWPUser( $params, $password, 'staff' );
                        $wp_user->set_role( get_option( 'bookly_staff_new_account_role' ) );
                        $staff->setWpUserId( $wp_user->ID );

                        Lib\Notifications\NewWpUser\Sender::sendAuthToStaff( $staff, $wp_user->display_name, $password );
                    } catch ( \Exception $e ) {
                        $data['alerts']['error'][] = $e->getMessage();
                    }
                } else {
                    $data['alerts']['error'][] = __( 'Please setup and enable New staff member\'s WordPress user login details notification', 'bookly' );
                }
            }

            if ( self::parameter( 'gateways' ) === 'custom' && self::hasParameter( 'gateways_list' ) ) {
                $gateways = json_encode( self::parameter( 'gateways_list' ) );
            } else {
                $gateways = null;
            }

            $staff->setGateways( $gateways );
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public static function preUpdateStaffAdvanced( array $data, Staff $staff, array $params )
    {
        if ( array_key_exists( 'google_disconnect', $params ) && $params['google_disconnect'] == '1' ) {
            $google = new Lib\Google\Client();
            if ( $google->auth( $staff ) ) {
                if ( BooklyLib\Config::advancedGoogleCalendarActive() ) {
                    $google->calendar()->stopWatching( false );
                }
                $google->revokeToken();
            }
            $staff->setGoogleData( null );
        } elseif ( isset ( $params['google_calendar_id'] ) ) {
            $calendar_id = $params['google_calendar_id'];
            $google      = new Lib\Google\Client();
            $update_google_data = false;
            if ( $google->auth( $staff, true ) ) {
                if ( ( $staff->getVisibility() === 'archive' )
                    && ( $params['visibility'] !== 'archive' )
                ) {
                    // Change visibility from archive
                    if ( BooklyLib\Proxy\Pro::getGoogleCalendarSyncMode() === '2-way' ) {
                        $google->calendar()->clearSyncToken()->sync();
                        $google->calendar()->watch();
                        $update_google_data = true;
                    }
                } elseif ( ( $staff->getVisibility() !== 'archive' )
                    && ( $params['visibility'] === 'archive' )
                ) {
                    // Change visibility to archive
                    if ( BooklyLib\Config::advancedGoogleCalendarActive() ) {
                        $google->calendar()->clearSyncToken();
                        $update_google_data = true;
                    }
                } elseif ( $calendar_id !== $google->data()->calendar->id ) {
                    // Calendar changed
                    if ( $staff->getVisibility() === 'archive' ) {
                        $data['alerts']['error'][] = 'Google Calendar: ' . __( 'Can\'t change calendar for archived staff', 'bookly' );
                        return $data;
                    } elseif ( $calendar_id != '' ) {
                        if ( ! $google->validateCalendarId( $calendar_id ) ) {
                            $data['alerts']['error'][] = 'Google Calendar: ' . implode( '<br>', $google->getErrors() );
                            return $data;
                        }
                    } else {
                        $calendar_id = null;
                    }
                    if ( BooklyLib\Config::advancedGoogleCalendarActive() ) {
                        $google->calendar()->clearSyncToken()->stopWatching( false );
                    }
                    $google->data()->calendar->id = $calendar_id;
                    $update_google_data = true;
                }
                if ( $update_google_data ) {
                    $google_data = $google->data();
                    $staff->setGoogleData( $google_data->toJson() );
                }
            }
        }
        if ( isset( $params['working_time_limit'] ) && $params['working_time_limit'] === '' ) {
            $staff->setWorkingTimeLimit( null );
        }
        if ( isset( $params['time_zone'] ) && $params['time_zone'] === '' ) {
            $staff->setTimeZone( null );
        }
        if ( array_key_exists( 'zoom_oauth_disconnect', $params ) && $params['zoom_oauth_disconnect'] == '1' ) {
            Lib\Zoom\OAuth::createForStaff( $staff )->revokeToken();
            $staff->setZoomAuthentication( 'default' );
        }

        return $data;
    }

}