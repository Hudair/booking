<?php
namespace BooklyPro\Lib\Zoom;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Config;

/**
 * Class OAuth
 * @package BooklyPro\Lib\Zoom
 */
class OAuth extends BaseAuth
{
    const OAUTH_POINT = 'https://zoom.us/oauth/';

    /** @var array */
    protected $token;
    /** @var BooklyLib\Entities\Staff */
    protected $staff;

    /**
     * @inheritDoc
     */
    protected function init( $staff = null )
    {
        if ( $staff ) {
            $this
                ->setToken( (array) json_decode( $staff->getZoomOAuthToken() ) )
                ->setStaff( $staff );
        } else {
            $this->setToken( (array) Config::zoomOAuthToken() );
        }
    }

    /**
     * @inheritDoc
     */
    public function getBearerToken()
    {
        if ( $this->isAccessTokenExpired() ) {
            $this->refreshToken();
        }

        return isset( $this->token['access_token'] ) ? $this->token['access_token'] : '';
    }

    /**
     * Sets token
     *
     * @param array $token
     * @return $this
     */
    public function setToken( $token )
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param BooklyLib\Entities\Staff $staff
     * @return $this
     */
    public function setStaff( BooklyLib\Entities\Staff $staff )
    {
        $this->staff = $staff;

        return $this;
    }

    /**
     * @param string $redirect_uri
     * @return string
     */
    public function getAuthorizationUrl( $redirect_uri )
    {
        $data = array(
            'response_type' => 'code',
            'client_id'     => Config::zoomOAuthClientId(),
            'redirect_uri'  => $redirect_uri,
        );

        return self::OAUTH_POINT . 'authorize?' . http_build_query( $data );
    }

    /**
     * @param string $redirect_uri
     * @return bool
     */
    public function requestAccessToken( $redirect_uri )
    {
        $code = $_GET['code'];

        $data = array(
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirect_uri,
        );

        $url = self::OAUTH_POINT . 'token?' . http_build_query( $data );

        $response = wp_remote_post( $url, array( 'headers' => $this->getAuthorizationHeader() ) );
        if ( ! is_wp_error( $response ) && isset ( $response['response']['code'] ) ) {
            $json = json_decode( $response['body'], true );
            if ( $response['response']['code'] == 200 ) {
                $this->updateOAuthToken( $json );

                return true;
            }
        }

        return false;
    }

    /**
     * Revoke authorization token
     *
     * @return bool
     */
    public function revokeToken()
    {
        $url = self::OAUTH_POINT . 'revoke?' . http_build_query( array( 'token' => $this->getBearerToken() ) );

        $response = wp_remote_post( $url, array( 'headers' => $this->getAuthorizationHeader() ) );
        $this->updateOAuthToken( null );
        if ( isset( $response['body'] ) ) {
            $json = json_decode( $response['body'], true );

            return isset( $json['status'] ) && $json['status'] === 'success';
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function refreshToken()
    {
        if ( isset( $this->token['refresh_token'] ) ) {
            $url      = self::OAUTH_POINT . 'token?grant_type=refresh_token&refresh_token=' . $this->token['refresh_token'];
            $response = wp_remote_post( $url, array( 'headers' => $this->getAuthorizationHeader() ) );
            if ( ! is_wp_error( $response ) && isset ( $response['response']['code'] ) ) {
                $json = json_decode( $response['body'], true );
                if ( $response['response']['code'] == 200 ) {
                    $this->updateOAuthToken( $json );

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $token
     */
    private function updateOAuthToken( $token )
    {
        $this->setToken( $token );
        if ( $this->staff ) {
            $this->staff->setZoomOAuthToken( $this->token ? json_encode( $this->token ) : null )->save();
        } else {
            update_option( 'bookly_zoom_oauth_token', $this->token );
        }
    }

    /**
     * @return string
     */
    private function getAuthorizationHeader()
    {
        return 'Authorization: Basic ' . base64_encode( Config::zoomOAuthClientId() . ':' . Config::zoomOAuthClientSecret() );
    }

    /**
     * Check expiration
     * @return bool
     */
    private function isAccessTokenExpired()
    {
        if ( ! $this->token || ! isset( $this->token['created'] ) ) {
            return true;
        }

        // If the token is set to expire in the next 30 seconds.
        return ( $this->token['created'] + ( $this->token['expires_in'] - 30 ) ) < time();
    }
}