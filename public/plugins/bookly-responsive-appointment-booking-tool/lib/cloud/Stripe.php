<?php
namespace Bookly\Lib\Cloud;

use Bookly\Backend\Modules\CloudProducts;

/**
 * Class Stripe
 * @package Bookly\Lib\Cloud
 */
class Stripe extends Base
{
    const CONNECT        = '/1.0/users/%token%/products/stripe/connect';            //POST|DELETE
    const CREATE_SESSION = '/1.0/users/%token%/products/stripe/checkout/sessions';  //POST
    const RETRIEVE_EVENT = '/1.0/users/%token%/products/stripe/events/%event_id%';  //GET

    /**
     * @param array  $info
     * @param string $success_url
     * @param string $cancel_url
     * @return bool|mixed
     */
    public function createSession( $info, $success_url, $cancel_url )
    {
        $info['currency'] = get_option( 'bookly_pmt_currency' );
        $info = array(
            'order_data'  => $info,
            'success_url' => $success_url,
            'cancel_url'  => $cancel_url,
        );

        return $this->api->sendPostRequest( self::CREATE_SESSION, $info );
    }

    /**
     * Stripe connect
     *
     * @return bool|string
     */
    public function connect()
    {
        $data = array(
            'notify_url'  => add_query_arg( array( 'action' => 'bookly_cloud_stripe_notify' ), admin_url( 'admin-ajax.php' ) ),
            'success_url' => add_query_arg( array( 'page' => CloudProducts\Page::pageSlug() ), admin_url( 'admin.php' ) ) . '#cloud-product=stripe&status=activated',
            'cancel_url'  => add_query_arg( array( 'page' => CloudProducts\Page::pageSlug() ), admin_url( 'admin.php' ) ) . '#cloud-product=stripe&status=cancelled'
        );
        $response = $this->api->sendPostRequest( self::CONNECT, $data );
        if ( $response ) {
            return $response['redirect_url'];
        }

        return false;
    }

    /**
     * Disconnect Stripe account
     *
     * @return bool
     */
    public function disconnect()
    {
        return $this->api->sendDeleteRequest( self::CONNECT, array() );
    }

    /**
     * Retriev event
     *
     * @param string $event_id
     * @return array
     * @throws \Exception
     */
    public function retrieveEvent( $event_id )
    {
        $data     = array( '%event_id%' => $event_id );
        $response = $this->api->sendGetRequest( self::RETRIEVE_EVENT, $data );

        if ( $response ) {
            return $response['data'];
        } else {
            throw new \Exception();
        }
    }

    /**
     * @inheritDoc
     */
    public function translateError( $error_code )
    {
        switch ( $error_code ) {
            case 'ERROR_STRIPE_NOT_CONNECTED': return __( 'Stripe not connected', 'bookly' );
            case 'ERROR_STRIPE_ACCOUNT_NOT_FOUND': return __( 'Stripe account not found', 'bookly' );
            default: return null;
        }
    }
}