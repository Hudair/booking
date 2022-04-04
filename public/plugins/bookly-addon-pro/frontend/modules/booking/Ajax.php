<?php
namespace BooklyPro\Frontend\Modules\Booking;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\Payment;
use Bookly\Lib\UserBookingData;
use Bookly\Frontend\Modules\Booking\Lib\Errors;
use BooklyPro\Lib\Payment\PayPal;

/**
 * Class Ajax
 * @package BooklyPro\Frontend\Modules\Booking
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    /**
     * Apply tips
     */
    public static function applyTips()
    {
        $response = null;
        $userData = new UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() && get_option( 'bookly_app_show_tips' ) ) {

            $tips = self::parameter( 'tips' );
            if ( $tips >= 0 ) {
                $userData->setTips( $tips );
                $response = array( 'success' => true );
            } else {
                $response = array(
                    'success' => false,
                    'error' => BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_tips_error' ),
                );
            }
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }

        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * Save cart items as pending appointments.
     */
    public static function savePendingAppointment()
    {
        if ( ( Config::payuLatamActive() && get_option( 'bookly_payu_latam_enabled' ) ) || get_option( 'bookly_paypal_enabled' ) == PayPal::TYPE_PAYMENTS_STANDARD ) {
            $userData = new UserBookingData( self::parameter( 'form_id' ) );
            if ( $userData->load() ) {
                $failed_cart_key = $userData->cart->getFailedKey();
                if ( $failed_cart_key === null ) {
                    $coupon = $userData->getCoupon();
                    if ( $coupon ) {
                        $coupon->claim();
                        $coupon->save();
                    }
                    $payment   = new Payment();
                    $cart_info = $userData->cart->getInfo( self::parameter( 'payment_type' ) );
                    switch ( self::parameter( 'payment_type' ) ) {
                        case  Payment::TYPE_PAYPAL:
                            $cart_info->setGatewayTaxCalculationRule( 'tax_increases_the_cost' );
                            break;
                        case  Payment::TYPE_PAYULATAM:
                            $cart_info->setGatewayTaxCalculationRule( 'tax_in_the_price' );
                            break;
                    }

                    $payment
                        ->setType( self::parameter( 'payment_type' ) )
                        ->setStatus( Payment::STATUS_PENDING )
                        ->setCartInfo( $cart_info )
                        ->save();
                    $payment_id = $payment->getId();
                    $order = $userData->save( $payment );
                    $payment->setDetailsFromOrder( $order, $cart_info )->save();
                    $response = array(
                        'success'    => true,
                        'payment_id' => $payment_id,
                    );
                } else {
                    $response = array(
                        'success'         => false,
                        'failed_cart_key' => $failed_cart_key,
                        'error'           => Errors::CART_ITEM_NOT_AVAILABLE,
                    );
                }
            } else {
                $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
            }
            $userData->sessionSave();
        } else {
            $response = array( 'success' => false, 'error' => Errors::INVALID_GATEWAY );
        }

        wp_send_json( $response );
    }

    /**
     * Log in with Facebook.
     */
    public static function facebookLogin()
    {
        if ( get_current_user_id() ) {
            // Do nothing for logged in users.
            wp_send_json( array( 'success' => false, 'error' => Errors::ALREADY_LOGGED_IN ) );
        }

        $form_id     = self::parameter( 'form_id' );
        $facebook_id = self::parameter( 'id' );

        $response = null;
        $userData = new BooklyLib\UserBookingData( $form_id );

        if ( $userData->load() ) {
            $customer = new BooklyLib\Entities\Customer();
            if ( $customer->loadBy( array( 'facebook_id' => $facebook_id ) ) ) {
                $user_info = array(
                    'email'              => $customer->getEmail(),
                    'full_name'          => $customer->getFullName(),
                    'first_name'         => $customer->getFirstName(),
                    'last_name'          => $customer->getLastName(),
                    'phone'              => $customer->getPhone(),
                    'country'            => $customer->getCountry(),
                    'state'              => $customer->getState(),
                    'postcode'           => $customer->getPostcode(),
                    'city'               => $customer->getCity(),
                    'street'             => $customer->getStreet(),
                    'street_number'      => $customer->getStreetNumber(),
                    'additional_address' => $customer->getAdditionalAddress(),
                    'birthday'           => $customer->getBirthday(),
                    'info_fields'        => json_decode( $customer->getInfoFields() ),
                );
            } else {
                $user_info  = array(
                    'email'      => self::parameter( 'email' ),
                    'full_name'  => self::parameter( 'name' ),
                    'first_name' => self::parameter( 'first_name' ),
                    'last_name'  => self::parameter( 'last_name' ),
                );
            }
            $userData->fillData( $user_info + array( 'facebook_id' => $facebook_id ) );
            $response = array(
                'success' => true,
                'data'    => $user_info,
            );
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        $userData->sessionSave();

        // Output JSON response.
        wp_send_json( $response );
    }

    /**
     * Cancel appointments using token.
     */
    public static function cancelAppointments()
    {
        $token = self::parameter( 'token' );
        $succeed = false;
        if ( $token !== null ) {
            $customer_appointments = BooklyLib\Entities\CustomerAppointment::query( 'ca' )
                ->leftJoin( 'Order', 'o', 'o.id = ca.order_id' )
                ->where( 'o.token', $token )
                ->find();

            /** @var BooklyLib\Entities\CustomerAppointment $customer_appointment */
            foreach ( $customer_appointments as $customer_appointment ) {
                if ( $customer_appointment->cancelAllowed() ) {
                    $customer_appointment->cancel();
                    $succeed = true;
                }
            }
        }

        BooklyLib\Utils\Common::cancelAppointmentRedirect( $succeed );
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        $excluded_actions = array(
            'cancelAppointments'
        );

        return in_array( $action, $excluded_actions ) || parent::csrfTokenValid( $action );
    }
}