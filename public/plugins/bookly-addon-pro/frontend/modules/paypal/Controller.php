<?php
namespace BooklyPro\Frontend\Modules\Paypal;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Payment;
use Bookly\Lib\Notifications\Cart\Sender;
use Bookly\Lib\UserBookingData;
use Bookly\Lib\Utils\Common;
use BooklyPro\Lib\Payment\PayPal;

/**
 * Class Controller
 * @package BooklyPro\Frontend\Modules\Paypal
 */
class Controller extends BooklyLib\Base\Component
{
    /**
     * Init Express Checkout transaction.
     */
    public static function ecInit()
    {
        $form_id = self::parameter( 'bookly_fid' );
        if ( $form_id ) {
            // Create a PayPal object.
            $paypal   = new PayPal();
            $userData = new UserBookingData( $form_id );

            if ( $userData->load() ) {
                $cart_info = $userData->cart->getInfo( Payment::TYPE_PAYPAL );
                $cart_info->setGatewayTaxCalculationRule( 'tax_increases_the_cost' );

                $product = new \stdClass();
                $product->name  = $userData->cart->getItemsTitle( 126 );
                $product->price = $cart_info->getGatewayAmount();
                $product->qty   = 1;
                $paypal->setProduct( $product );
                $paypal->setTotalTax( $cart_info->getGatewayTax() );

                // and send the payment request.
                $paypal->sendECRequest( $form_id );
            }
        }
    }

    /**
     * Process Express Checkout return request.
     */
    public static function ecReturn()
    {
        $form_id = self::parameter( 'bookly_fid' );
        $PayPal  = new PayPal();
        $error_message = '';

        if ( self::hasParameter( 'token' ) && self::hasParameter( 'PayerID' ) ) {
            $token = self::parameter( 'token' );
            $data = array( 'TOKEN' => $token );
            // Send the request to PayPal.
            $response = $PayPal->sendNvpRequest( 'GetExpressCheckoutDetails', $data );
            if ( $response == null ) {
                $error_message = $PayPal->getError();
            } elseif ( ( strtoupper( $response['ACK'] ) == 'SUCCESS' )
                    && ( $response['CURRENCYCODE'] == get_option( 'bookly_pmt_currency' ) ) )
            {
                $data['PAYERID'] = self::parameter( 'PayerID' );
                $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';

                foreach ( array( 'L_PAYMENTREQUEST_0_AMT0', 'L_PAYMENTREQUEST_0_NAME0', 'L_PAYMENTREQUEST_0_QTY0', 'PAYMENTREQUEST_0_AMT', 'PAYMENTREQUEST_0_CURRENCYCODE', 'PAYMENTREQUEST_0_ITEMAMT', 'PAYMENTREQUEST_0_TAXAMT', ) as $parameter ) {
                    if ( array_key_exists( $parameter, $response ) ) {
                        $data[ $parameter ] = $response[ $parameter ];
                    }
                }

                // We need to execute the "DoExpressCheckoutPayment" at this point to Receive payment from user.
                $response = $PayPal->sendNvpRequest( 'DoExpressCheckoutPayment', $data );
                if ( $response === null ) {
                    $error_message = $PayPal->getError();
                } elseif ( 'SUCCESS' == strtoupper( $response['ACK'] ) || 'SUCCESSWITHWARNING' == strtoupper( $response['ACK'] ) ) {
                    // Get transaction info
                    $response = $PayPal->sendNvpRequest( 'GetTransactionDetails', array( 'TRANSACTIONID' => $response['PAYMENTINFO_0_TRANSACTIONID'] ) );
                    if ( $response === null ) {
                        $error_message = $PayPal->getError();
                    } elseif ( 'SUCCESS' == strtoupper( $response['ACK'] ) || 'SUCCESSWITHWARNING' == strtoupper( $response['ACK'] ) ) {
                        $payment = new Payment();
                        $payment
                            ->setType( Payment::TYPE_PAYPAL )
                            ->setStatus( Payment::STATUS_COMPLETED );
                        $userData = new UserBookingData( $form_id );
                        if ( $userData->load() ) {
                            $cart_info = $userData->cart->getInfo( Payment::TYPE_PAYPAL );

                            $coupon = $userData->getCoupon();
                            if ( $coupon ) {
                                $coupon->claim();
                                $coupon->save();
                            }
                            $paid     = (float) $response['AMT'];
                            $expected = (float) $cart_info->getPayNow();
                            if ( abs( $expected - $paid ) < 0.01 ) {
                                $payment
                                    ->setCartInfo( $cart_info )
                                    ->save();
                                $order = $userData->save( $payment );
                                $payment->setDetailsFromOrder( $order, $cart_info )->save();
                                Sender::send( $order );
                            }
                        } else {
                            // Information about customer's cart (order) is no longer available.
                            $payment
                                ->setTotal( $response['AMT'] )
                                ->setPaid( $response['AMT'] )
                                ->setTax( $response['TAXAMT'] )
                                ->save();
                        }
                        $userData->setPaymentStatus( Payment::TYPE_PAYPAL, 'success' );
                        $userData->sessionSave();

                        @wp_redirect( remove_query_arg( PayPal::$remove_parameters, Common::getCurrentPageURL() ) );
                        exit;
                    } else {
                        $error_message = $response['L_LONGMESSAGE0'];
                    }
                } else {
                    $error_message = $response['L_LONGMESSAGE0'];
                }
            }
        } else {
            $error_message = __( 'Invalid token provided', 'bookly' );
        }

        if ( ! empty( $error_message ) ) {
            header( 'Location: ' . wp_sanitize_redirect( add_query_arg( array(
                    'bookly_action' => 'paypal-ec-error',
                    'bookly_fid' => $form_id,
                    'error_msg'  => urlencode( $error_message ),
                ), Common::getCurrentPageURL()
                ) ) );
            exit;
        }
    }

    /**
     * Process Express Checkout cancel request.
     */
    public static function ecCancel()
    {
        $userData = new UserBookingData( self::parameter( 'bookly_fid' ) );
        $userData->load();
        $userData->setPaymentStatus( Payment::TYPE_PAYPAL, 'cancelled' );
        $userData->sessionSave();

        @wp_redirect( remove_query_arg( PayPal::$remove_parameters, Common::getCurrentPageURL() ) );
        exit;
    }

    /**
     * Process Express Checkout error request.
     */
    public static function ecError()
    {
        $userData = new UserBookingData( self::parameter( 'bookly_fid' ) );
        $userData->load();
        $userData->setPaymentStatus( Payment::TYPE_PAYPAL, 'error', self::parameter( 'error_msg' ) );
        $userData->sessionSave();

        @wp_redirect( remove_query_arg( PayPal::$remove_parameters, Common::getCurrentPageURL() ) );
        exit;
    }
}