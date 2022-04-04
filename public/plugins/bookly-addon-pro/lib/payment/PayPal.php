<?php
namespace BooklyPro\Lib\Payment;

use Bookly\Lib\Entities\Payment;
use Bookly\Lib\Utils\Common;

/**
 * Class PayPal
 * @package BooklyPro\Lib\Payment
 */
class PayPal
{
    const TYPE_EXPRESS_CHECKOUT  = 'ec';
    const TYPE_PAYMENTS_STANDARD = 'ps';
    const TYPE_CHECKOUT          = 'checkout';

    const URL_POSTBACK_IPN_LIVE = 'https://www.paypal.com/cgi-bin/webscr';
    const URL_POSTBACK_IPN_SANDBOX = 'https://www.sandbox.paypal.com/cgi-bin/webscr';

    // Array for cleaning PayPal request
    static public $remove_parameters = array( 'bookly_action', 'bookly_fid', 'error_msg', 'token', 'PayerID',  'type' );

    /** @var  string */
    private $error;

    /**
     * The array of products for checkout
     *
     * @var \stdClass
     */
    protected $product;

    /** @var  float */
    protected $tax = 0;
    /**
     * Send the Express Checkout NVP request
     *
     * @param $form_id
     */
    public function sendECRequest( $form_id )
    {
        $current_url = Common::getCurrentPageURL();

        // create the data to send on PayPal
        $data = array(
            'BRANDNAME'    => get_option( 'bookly_co_name' ),
            'SOLUTIONTYPE' => 'Sole',
            'PAYMENTREQUEST_0_PAYMENTACTION' => 'Sale',
            'PAYMENTREQUEST_0_CURRENCYCODE'  => get_option( 'bookly_pmt_currency' ),
            'NOSHIPPING' => 1,
            'RETURNURL'  => add_query_arg( array( 'bookly_action' => 'paypal-ec-return', 'bookly_fid' => $form_id ), $current_url ),
            'CANCELURL'  => add_query_arg( array( 'bookly_action' => 'paypal-ec-cancel', 'bookly_fid' => $form_id ), $current_url )
        );
        $data['L_PAYMENTREQUEST_0_NAME0'] = $this->product->name;
        $data['L_PAYMENTREQUEST_0_AMT0']  = $this->product->price;
        $data['L_PAYMENTREQUEST_0_QTY0']  = $this->product->qty;

        $total = $this->product->qty * $this->product->price;
        $data['PAYMENTREQUEST_0_ITEMAMT'] = $total;
        $data['PAYMENTREQUEST_0_AMT']     = $total + $this->tax;
        if ( get_option( 'bookly_paypal_send_tax' ) ) {
            $data['PAYMENTREQUEST_0_TAXAMT'] = $this->tax;
        }

        // send the request to PayPal
        $response = $this->sendNvpRequest( 'SetExpressCheckout', $data );
        if ( $response === null ) {
            $url = wp_sanitize_redirect(
                add_query_arg( array(
                    'bookly_action' => 'paypal-ec-error',
                    'bookly_fid'    => $form_id,
                    'error_msg'     => urlencode( $this->error ),
                ), $current_url ) );
        } else {
            // Respond according to message we receive from PayPal
            $ack = strtoupper( $response['ACK'] );
            if ( $ack == 'SUCCESS' || $ack == 'SUCCESSWITHWARNING' ) {
                // Redirect url to PayPal.
                $url = sprintf(
                    'https://www%s.paypal.com/cgi-bin/webscr?cmd=_express-checkout&useraction=commit&token=%s',
                    get_option( 'bookly_paypal_sandbox' ) ? '.sandbox' : '',
                    urlencode( $response['TOKEN'] )
                );
            } else {
                $url = wp_sanitize_redirect(
                    add_query_arg( array(
                        'bookly_action' => 'paypal-ec-error',
                        'bookly_fid'    => $form_id,
                        'error_msg'     => urlencode( $response['L_LONGMESSAGE0'] ),
                    ), $current_url ) );
            }
        }
        header( 'Location: ' . $url );
        exit;
    }

    /**
     * Send the NVP Request to the PayPal
     *
     * @param       $method
     * @param array $data
     * @return array|null
     */
    public function sendNvpRequest( $method, array $data )
    {
        $paypal_response = array();
        $url  = 'https://api-3t' . ( get_option( 'bookly_paypal_sandbox' ) ? '.sandbox' : '' ) . '.paypal.com/nvp';

        $data['METHOD']    = $method;
        $data['VERSION']   = '124.0';
        $data['USER']      = get_option( 'bookly_paypal_api_username' );
        $data['PWD']       = get_option( 'bookly_paypal_api_password' );
        $data['SIGNATURE'] = get_option( 'bookly_paypal_api_signature' );

        $args = array(
            'sslverify' => false,
            'body'      => $data,
            'timeout'   => 60,
        );

        $response = wp_remote_post( $url, $args );
        if ( $response instanceof \WP_Error ) {
            $this->error = 'Invalid HTTP Response for POST request to ' . $url;
            return null;
        } else {
            // Extract the response details.
            parse_str( $response['body'], $paypal_response );

            if ( ! array_key_exists( 'ACK', $paypal_response ) ) {
                $this->error = 'Invalid HTTP Response for POST request to ' . $url;
                return null;
            }
        }

        return $paypal_response;
    }

    /**
     * Outputs HTML form for PayPal Express Checkout.
     *
     * @param string $form_id
     */
    public static function renderECForm( $form_id )
    {
        $replacement = array(
            '%form_id%'     => $form_id,
            '%gateway%'     => Payment::TYPE_PAYPAL,
            '%back%'        => Common::getTranslatedOption( 'bookly_l10n_button_back' ),
            '%next%'        => Common::getTranslatedOption( 'bookly_l10n_step_payment_button_next' ),
            '%align_class%' => get_option( 'bookly_app_align_buttons_left' ) ? 'bookly-left' : 'bookly-right',
        );

        $form = '<form method="post" class="bookly-%gateway%-form">
                <input type="hidden" name="bookly_action" value="paypal-ec-init"/>
                <input type="hidden" name="bookly_fid" value="%form_id%"/>
                <button class="bookly-back-step bookly-js-back-step bookly-btn ladda-button" data-style="zoom-in" style="margin-right: 10px;" data-spinner-size="40"><span class="ladda-label">%back%</span></button>
                <div class="%align_class%">
                    <button class="bookly-next-step bookly-js-next-step bookly-btn ladda-button" data-style="zoom-in" data-spinner-size="40"><span class="ladda-label">%next%</span></button>
                </div>
             </form>';

        echo strtr( $form, $replacement );
    }

    /**
     * Add the Product for payment
     *
     * @param \stdClass $product
     */
    public function setProduct( \stdClass $product )
    {
        $this->product = $product;
    }

    /**
     * @param float $tax
     */
    public function setTotalTax( $tax )
    {
        $this->tax = $tax;
    }

    /**
     * Verify IPN request
     * @return bool
     */
    public static function verifyIPN()
    {
        $paypalUrl = get_option( 'bookly_paypal_sandbox' ) ?
            self::URL_POSTBACK_IPN_SANDBOX :
            self::URL_POSTBACK_IPN_LIVE;

        $raw_post_data  = file_get_contents( 'php://input' );
        $raw_post_array = explode( '&', $raw_post_data );
        $postData       = array();
        foreach ( $raw_post_array as $keyval ) {
            $keyval = explode( '=', $keyval );
            if ( count( $keyval ) == 2 ) {
                $postData[ $keyval[0] ] = urldecode( $keyval[1] );
            }
        }

        $req = 'cmd=_notify-validate';
        foreach ( $postData as $key => $value ) {
            if (
                ( function_exists( 'get_magic_quotes_gpc' ) === true )
                && ( get_magic_quotes_gpc() === 1 )
            ) {
                $value = urlencode( stripslashes( $value ) );
            } else {
                $value = urlencode( $value );
            }
            $req .= "&$key=$value";
        }

        $response = wp_safe_remote_post(
            $paypalUrl,
            array(
                'sslcertificates' => __DIR__ . '/PayPal/cert/cacert.pem',
                'body'            => $req,
            )
        );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        return strcmp( $response['body'], 'VERIFIED' ) === 0;
    }

    /**
     * Gets error
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }
}