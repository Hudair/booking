<?php
namespace BooklyPro\Frontend\Modules\Booking\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Frontend\Modules\Booking\Proxy;
use Bookly\Lib\Config;
use BooklyPro\Lib;

/**
 * Class Shared
 * @package BooklyPro\Frontend\Modules\Booking\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function enqueueBookingScripts( array $depends )
    {
        if ( Lib\Config::showFacebookLoginButton() && ! get_current_user_id() ) {
            wp_enqueue_script( 'bookly-sdk.js', sprintf( 'https://connect.facebook.net/%s/sdk.js', BooklyLib\Config::getLocale() ) );

            $depends[] = 'bookly-sdk.js';
        }

        return $depends;
    }

    /**
     * @inheritDoc
     */
    public static function booklyFormOptions( array $bookly_options )
    {
        $bookly_options['facebook'] = array(
            'enabled' => (int) ( Lib\Config::showFacebookLoginButton() && ! get_current_user_id() ),
            'appId'   => Lib\Config::getFacebookAppId(),
        );

        return $bookly_options;
    }

    /**
     * @inheritDoc
     */
    public static function prepareCartItemInfoText( $data, BooklyLib\CartItem $cart_item )
    {
        if ( $cart_item->getAppointmentId() ) {
            $data['online_meeting_url'][] = BooklyLib\Proxy\Shared::buildOnlineMeetingUrl( '', $cart_item->getAppointment() );
            $data['online_meeting_password'][] = BooklyLib\Proxy\Shared::buildOnlineMeetingPassword( '', $cart_item->getAppointment() );
            $data['online_meeting_join_url'][] = BooklyLib\Proxy\Shared::buildOnlineMeetingJoinUrl( '', $cart_item->getAppointment() );
        }

        return $data;
    }

    /**
     * @inheritDoc
     */
    public static function prepareInfoTextCodes( array $codes, array $data )
    {
        $codes['online_meeting_url'] = isset( $data['online_meeting_url'] ) ? implode( ', ', $data['online_meeting_url'] ) : '';
        $codes['online_meeting_password'] = isset( $data['online_meeting_password'] ) ? implode( ', ', $data['online_meeting_password'] ) : '';
        $codes['online_meeting_join_url'] = isset( $data['online_meeting_join_url'] ) ? implode( ', ', $data['online_meeting_join_url'] ) : '';

        return $codes;
    }

    /**
     * @inheritDoc
     */
    public static function preparePaymentOptions( $options, $form_id, $show_price, BooklyLib\CartInfo $cart_info, $userData )
    {
        if ( Config::paypalEnabled() ) {
            $gateway = BooklyLib\Entities\Payment::TYPE_PAYPAL;
            if ( Proxy\CustomerGroups::allowedGateway( $gateway, $userData ) !== false ) {
                $cart_info->setGateway( $gateway );
                $payment_status = $userData->extractPaymentStatus();

                $options[ $gateway ] = array(
                    'html' => self::renderTemplate(
                        'paypal_payment_option',
                        compact( 'form_id', 'show_price', 'cart_info', 'payment_status' ),
                        false
                    ),
                    'pay' => $cart_info->getPayNow(),
                );
            }
        }

        return $options;
    }

    /**
     * @inheritDoc
     */
    public static function renderPaymentForms( $form_id, $page_url )
    {
        if ( Config::paypalEnabled() ) {
            $type = get_option( 'bookly_paypal_enabled' );
            self::renderTemplate(
                'paypal_payment_form',
                compact( 'type', 'form_id', 'page_url' )
            );
        }
    }
}