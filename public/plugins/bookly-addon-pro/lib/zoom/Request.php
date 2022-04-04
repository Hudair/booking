<?php
namespace BooklyPro\Lib\Zoom;

use Bookly\Lib\Entities\Staff;
use BooklyPro\Lib\Config;

/**
 * Class Request
 * @package BooklyPro\Lib\Zoom
 */
class Request
{
    /** @var BaseAuth */
    protected $client;

    /** @var string */
    protected $api_point = 'https://api.zoom.us/v2/';

    /** @var array */
    protected $errors;

    /**
     * Request constructor.
     *
     * @param Staff $staff
     */
    public function __construct( $staff )
    {
        if ( $staff ) {
            switch ( $staff->getZoomAuthentication() ) {
                case Authentication::TYPE_DEFAULT:
                    $this->initDefaultClient();
                    break;
                case Authentication::TYPE_JWT:
                    $this->client = JWT::createForStaff( $staff );
                    break;
                case Authentication::TYPE_OAuth:
                    $this->client = OAuth::createForStaff( $staff );
                    break;
            }
        } else {
            $this->initDefaultClient();
        }
    }

    protected function initDefaultClient()
    {
        $this->client = Config::zoomAuthentication() === Authentication::TYPE_JWT
            ? JWT::createDefault()
            : OAuth::createDefault();
    }

    /**
     * Request
     *
     * @param string $http_method
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function request( $http_method, $method, array $fields = array() )
    {
        $url = $this->api_point . $method;
        $body = null;

        if ( $http_method == 'GET' ) {
            $url = add_query_arg( $fields, $url );
        } else {
            $body = json_encode( $fields, JSON_PRETTY_PRINT );
        }

        $response = wp_remote_request( $url, array(
            'method' => $http_method,
            'headers' => $this->client->headers(),
            'body' => $body,
        ) );

        return $this->result( $response );
    }

    /**
     * Get
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function get( $method, $fields = array() )
    {
        return $this->request( 'GET', $method, $fields );
    }

    /**
     * Post
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function post( $method, $fields )
    {
        return $this->request( 'POST', $method, $fields );
    }

    /**
     * Patch
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function patch( $method, $fields )
    {
        return $this->request( 'PATCH', $method, $fields );
    }

    /**
     * Put
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function put( $method, $fields )
    {
        return $this->request( 'PUT', $method, $fields );
    }

    /**
     * Delete
     *
     * @param string $method
     * @param array $fields
     * @return array|false
     */
    protected function delete( $method, $fields = array() )
    {
        return $this->request( 'DELETE', $method, $fields );
    }

    /**
     * Result
     *
     * @param array|\WP_Error $response
     * @return array|false
     */
    protected function result( $response )
    {
        $this->errors = array();

        if ( ! is_wp_error( $response ) ) {
            if ( isset ( $response['body'] ) ) {
                $body = json_decode( (string) $response['body'], true );
                if ( $response['response']['code'] < 300 ) {
                    return $body !== null ? $body : true;
                } else {
                    if ( $body !== null ) {
                        if ( isset ( $body['message'] ) ) {
                            $this->errors[] = $body['message'];
                        } else {
                            $this->errors[] = 'Unknown error';
                        }
                    } else {
                        $this->errors[] = 'Invalid JSON';
                    }
                }
            } else {
                $this->errors[] = 'Empty body';
            }
        } else {
            $this->errors = $response->get_error_messages();
        }

        return false;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function errors()
    {
        return $this->errors;
    }
}