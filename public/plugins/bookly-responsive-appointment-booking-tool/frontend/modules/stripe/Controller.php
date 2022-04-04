<?php
namespace Bookly\Frontend\Modules\Stripe;

use Bookly\Lib;
use Bookly\Lib\Entities\Payment;
use Bookly\Lib\UserBookingData;
use Bookly\Lib\Utils\Common;

/**
 * Class Controller
 * @package Bookly\Frontend\Modules\Stripe
 */
class Controller extends Lib\Base\Component
{
    public static $remove_parameters = array( 'bookly_action', 'bookly_fid', 'error_msg' );

    /**
     * Create Stripe session
     */
    public static function createSession()
    {
        $response_url = self::parameter( 'response_url' );
        $form_id      = self::parameter( 'bookly_fid' );
        $userData     = new Lib\UserBookingData( $form_id );
        $userData->load();
        try {
            $payment = new Lib\Entities\Payment();
            $cart_info = $userData->cart->getInfo( Lib\Entities\Payment::TYPE_CLOUD_STRIPE );

            $payment
                ->setType( Lib\Entities\Payment::TYPE_CLOUD_STRIPE )
                ->setCartInfo( $cart_info )
                ->setStatus( Lib\Entities\Payment::STATUS_PENDING )
                ->save();

            $info     = array(
                'total'          => $cart_info->getGatewayAmount(),
                'description'    => $userData->cart->getItemsTitle(),
                'customer_email' => $userData->getEmail(),
                'metadata'       => array(
                    'payment_id' => $payment->getId(),
                ),
            );
            $api      = Lib\Cloud\API::getInstance();
            $response = $api->stripe
                ->createSession(
                    $info,
                    add_query_arg( array( 'bookly_action' => 'stripe-cloud-success', 'bookly_fid' => $form_id ), $response_url ),
                    add_query_arg( array( 'bookly_action' => 'stripe-cloud-cancel', 'bookly_fid' => $form_id ), $response_url )
                );
            if ( $response ) {
                $order = $userData->save( $payment );
                $payment
                    ->setDetailsFromOrder( $order, $cart_info )
                    ->save();
                $userData->sessionSave();

                wp_redirect( $response['redirect_url'] );
            } else {
                throw new \Exception( current( $api->getErrors() ) );
            }
        } catch ( \Exception $e ) {
            $payment->delete();
            $userData->setPaymentStatus( Lib\Entities\Payment::TYPE_CLOUD_STRIPE, 'error', $e->getMessage() );
            @wp_redirect( remove_query_arg( self::$remove_parameters, $response_url ) );
        }
        exit;
    }

    /**
     * Handle success request
     */
    public static function success()
    {
        $userData = new UserBookingData( self::parameter( 'bookly_fid' ) );
        if( $userData->load() ) {
            $userData->setPaymentStatus( Payment::TYPE_CLOUD_STRIPE, 'processing' )
                ->sessionSave();
        }

        @wp_redirect( remove_query_arg( self::$remove_parameters, Common::getCurrentPageURL() ) );
        exit;
    }

    /**
     * Cancel session
     */
    public static function cancelSession()
    {
        $userData = new Lib\UserBookingData( self::parameter( 'bookly_fid' ) );
        if ( $userData->load() ) {
            $userData->setFailedPaymentStatus( Lib\Entities\Payment::TYPE_CLOUD_STRIPE, 'cancelled' )
                ->sessionSave();
        }

        @wp_redirect( remove_query_arg( self::$remove_parameters, Lib\Utils\Common::getCurrentPageURL() ) );
        exit;
    }
}