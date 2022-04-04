<?php
namespace BooklyPro\Frontend\Modules\Booking\ProxyProviders;

use BooklyPro\Lib;
use Bookly\Lib as BooklyLib;
use Bookly\Frontend\Components\Booking\InfoText;
use Bookly\Frontend\Modules\Booking\Lib\Steps;
use Bookly\Frontend\Modules\Booking\Proxy;

/**
 * Class Local
 * @package BooklyPro\Frontend\Modules\Booking\ProxyProviders
 */
class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderTimeZoneSwitcher()
    {
        if ( get_option( 'bookly_app_show_time_zone_switcher' ) ) {
            $time_zone = BooklyLib\Slots\DatePoint::$client_timezone;
            if ( $time_zone[0] == '+' || $time_zone[0] == '-' ) {
                $parts     = explode( ':', $time_zone );
                $time_zone = sprintf(
                    'UTC%s%d%s',
                    $time_zone[0],
                    abs( $parts[0] ),
                    (int) $parts[1] ? '.' . rtrim( $parts[1] * 100 / 60, '0' ) : ''
                );
            }
            $time_zone_options = wp_timezone_choice( $time_zone, BooklyLib\Config::getLocale() );
            if ( strpos( $time_zone_options, 'selected' ) === false ) {
                $time_zone_options .= sprintf(
                    '<option selected="selected" value="%s">%s</option>',
                    esc_attr( $time_zone ),
                    esc_html( $time_zone )
                );
            }

            self::renderTemplate( 'time_zone_switcher', compact( 'time_zone_options' ) );
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderFacebookButton()
    {
        if ( Lib\Config::showFacebookLoginButton() ) {
            self::renderTemplate( 'fb_button' );
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderDetailsAddress( BooklyLib\UserBookingData $userData )
    {
        if ( get_option( 'bookly_app_show_address' ) ) {
            self::renderTemplate( 'address', compact( 'userData' ) );
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderDetailsBirthday( BooklyLib\UserBookingData $userData )
    {
        if ( get_option( 'bookly_app_show_birthday' ) ) {
            self::renderTemplate( 'birthday', compact( 'userData' ) );
        }
    }

    /**
     * @inheritDoc
     */
    public static function filterGateways( $gateways, BooklyLib\UserBookingData $userData )
    {
        $default = array_keys( $gateways );
        $unique = array();
        $counter = array();
        foreach ( $userData->cart->getItems() as $cart_item ) {
            $staff_id = $cart_item->getStaff()->getId();
            if ( ! in_array( $staff_id, $unique ) ) {
                $unique[] = $staff_id;
                $list = $cart_item->getStaff()->getGateways()
                    ? json_decode( $cart_item->getStaff()->getGateways(), true )
                    : $default;
                foreach ( $list as $gateway_name ) {
                    if ( in_array( $gateway_name, $default ) ) {
                        if ( ! isset( $counter[ $gateway_name ] ) ) {
                            $counter[ $gateway_name ] = 0;
                        }
                        $counter[ $gateway_name ] ++;
                    }
                }
            }
        }
        $staff_count = count( $unique );
        foreach ( $gateways as $gateway_name => $data ) {
            if ( isset( $counter[ $gateway_name ] ) && $counter[ $gateway_name ] == $staff_count ) {
                continue;
            }
            unset( $gateways[ $gateway_name ] );
        }

        return $gateways;
    }

    /**
     * @inheritDoc
     */
    public static function getHtmlPaymentImpossible( $progress_tracker, BooklyLib\UserBookingData $userData )
    {
        $info = InfoText::prepare( Steps::PAYMENT, BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_payment_step_without_intersected_gateways' ), $userData );

        return self::renderTemplate( 'payment_impossible', compact( 'progress_tracker', 'info' ), false );
    }

    /**
     * @inheritDoc
     */
    public static function renderPaymentStep( BooklyLib\UserBookingData $userData )
    {
        if ( get_option( 'bookly_app_show_tips' ) ) {
            self::renderTemplate( 'tips', compact( 'userData' ) );
        }
    }
}