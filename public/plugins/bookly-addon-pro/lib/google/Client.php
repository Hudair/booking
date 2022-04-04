<?php
namespace BooklyPro\Lib\Google;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\Staff;
use Bookly\Backend\Modules\Staff\Page as StaffPage;
use BooklyPro\Lib\Plugin;

/**
 * Class Client
 * @package BooklyPro\Lib\Google
 */
class Client
{
    /** @var \BooklyGoogle_Client */
    protected $client;

    /** @var \BooklyGoogle_Service_Calendar */
    protected $service;

    /** @var Staff */
    protected $staff;

    /** @var Calendar|\BooklyAdvancedGoogleCalendar\Lib\Google\Calendar */
    protected $calendar;

    /** @var AuthData */
    protected $data;

    /** @var array */
    protected $errors = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        include_once Plugin::getDirectory() . '/lib/google/vendor/autoload.php';

        $this->client = new \BooklyGoogle_Client();
        $this->client->setClientId( get_option( 'bookly_gc_client_id' ) );
        $this->client->setClientSecret( get_option( 'bookly_gc_client_secret' ) );
    }

    /**
     * Authenticate Google Client with given staff.
     *
     * @param Staff $staff
     * @param bool  $force
     * @return bool
     */
    public function auth( Staff $staff, $force = false )
    {
        if ( $force || $staff->getVisibility() != 'archive' ) {
            $json = $staff->getGoogleData();
            if ( $json != '' ) {
                try {
                    $data = AuthData::fromJson( $json );
                    $this->client->setAccessToken( $data->token );
                    if ( $this->client->isAccessTokenExpired() ) {
                        $this->client->refreshToken( $this->client->getRefreshToken() );
                        $data->token = $this->client->getAccessToken();
                        $staff
                            ->setGoogleData( $data->toJson() )
                            ->save();
                    }
                    $this->service  = new \BooklyGoogle_Service_Calendar( $this->client );
                    $this->calendar = Config::advancedGoogleCalendarActive()
                        ? BooklyLib\Proxy\AdvancedGoogleCalendar::createApiCalendar( $this )
                        : new Calendar( $this );
                    $this->staff    = $staff;
                    $this->data     = $data;

                    return true;

                } catch ( \Exception $e ) {
                    $this->addError( 'Google Calendar: ' . $e->getMessage() );
                }
            }
        }

        return false;
    }

    /**
     * Authenticate Google Client with given staff ID.
     *
     * @param int $staff_id
     * @return bool
     */
    public function authWithStaffId( $staff_id )
    {
        return $this->auth( Staff::find( $staff_id ) );
    }

    /**
     * Get list of Google Calendars.
     *
     * @return array|false
     */
    public function getCalendarList()
    {
        try {
            $result = array();
            $params = array();

            do {
                // Fetch calendars.
                $calendars = $this->service->calendarList->listCalendarList( $params );

                /** @var \BooklyGoogle_Service_Calendar_CalendarListEntry $calendar */
                foreach ( $calendars->getItems() as $calendar ) {
                    if ( in_array( $calendar->getAccessRole(), array( 'writer', 'owner' ) ) ) {
                        $result[ $calendar->getId() ] = array(
                            'primary' => $calendar->getPrimary(),
                            'summary' => $calendar->getSummary(),
                        );
                    }
                }
                $params['pageToken'] = $calendars->getNextPageToken();

            } while ( $params['pageToken'] !== null );

            return $result;

        } catch ( \Exception $e ) {
            $this->addError( $e->getMessage() );
        }

        return false;
    }

    /**
     * Check whether given calendar ID belongs to Google Calendar associated with staff.
     *
     * @param string $calendar_id
     * @return bool
     */
    public function validateCalendarId( $calendar_id )
    {
        try {
            $this->service->calendarList->get( $calendar_id );

            return true;

        } catch ( \Exception $e ) {
            $this->addError( $e->getMessage() );
        }

        return false;
    }

    /**
     * Construct authorization request URI for given staff ID.
     *
     * @param int $staff_id
     * @return string
     */
    public function createAuthUrl( $staff_id )
    {
        $this->client->setRedirectUri( self::generateRedirectURI() );
        $this->client->addScope( 'https://www.googleapis.com/auth/calendar' );
        $this->client->setState( strtr( base64_encode( $staff_id ), '+/=', '-_,' ) );
        $this->client->setApprovalPrompt( 'force' );
        $this->client->setAccessType( 'offline' );

        return $this->client->createAuthUrl();
    }

    /**
     * Attempt to exchange given code for a valid Google Calendar access token.
     *
     * @param string $code
     * @return string|false
     */
    public function exchangeCodeForAccessToken( $code )
    {
        $this->client->setRedirectUri( self::generateRedirectURI() );

        try {
            return $this->client->authenticate( $code );
        } catch ( \Exception $e ) {
            $this->addError( $e->getMessage() );
        }

        return false;
    }

    /**
     * Revoke Google Calendar token.
     *
     * @return bool
     */
    public function revokeToken()
    {
        try {
//            /** @var string $data json with all tokens access_token, refresh_token and etc */
//            $data = $this->client->getAccessToken();
//            $access_token = json_decode( $data['access_token'], true );
//            $this->client->revokeToken( $access_token );

            return true;

        } catch ( \Exception $e ) {
            $this->addError( $e->getMessage() );
        }

        return false;
    }

    /**
     * Get staff.
     *
     * @return Staff
     */
    public function staff()
    {
        return $this->staff;
    }

    /**
     * Get service.
     *
     * @return \BooklyGoogle_Service_Calendar
     */
    public function service()
    {
        return $this->service;
    }

    /**
     * Get calendar.
     *
     * @return Calendar|\BooklyAdvancedGoogleCalendar\Lib\Google\Calendar
     */
    public function calendar()
    {
        return $this->calendar;
    }

    /**
     * Get data.
     *
     * @return AuthData
     */
    public function data()
    {
        return $this->data;
    }

    /**
     * Add error.
     *
     * @param string $error
     */
    public function addError( $error )
    {
        $this->errors[] = $error;
    }

    /**
     * Get errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Generate Redirect URI.
     *
     * @return string
     */
    public static function generateRedirectURI()
    {
        return admin_url( 'admin.php?page=' . StaffPage::pageSlug() );
    }
}