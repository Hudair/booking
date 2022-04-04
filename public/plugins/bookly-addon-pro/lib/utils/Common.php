<?php
namespace BooklyPro\Lib\Utils;

use Bookly\Lib as BooklyLib;

/**
 * Class Common
 * @package BooklyPro\Lib\Utils
 */
abstract class Common
{
    /**
     * WPML translation
     *
     * @param array $appointments
     * @return array
     */
    public static function translateAppointments( array $appointments )
    {
        $postfix_any = sprintf( ' (%s)', BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_employee' ) );
        foreach ( $appointments as &$appointment ) {
            $category                = new BooklyLib\Entities\Category( array( 'id' => $appointment['category_id'], 'name' => $appointment['category'] ) );
            $service                 = new BooklyLib\Entities\Service( array( 'id' => $appointment['service_id'], 'title' => $appointment['service'] ) );
            $staff                   = new BooklyLib\Entities\Staff( array( 'id' => $appointment['staff_id'], 'full_name' => $appointment['staff'] ) );
            $appointment['category'] = $category->getTranslatedName();
            $appointment['service']  = $service->getTranslatedTitle();
            $appointment['staff']    = $staff->getTranslatedName() . ( $appointment['staff_any'] ? $postfix_any : '' );
            // Prepare extras.
            $appointment['extras'] = (array) BooklyLib\Proxy\ServiceExtras::getCAInfo( json_decode( $appointment['ca_id'], true ), true );
        }

        return $appointments;
    }

    /**
     * @return array
     */
    public static function getAddressFields()
    {
        return array(
            'country' => get_option( 'bookly_l10n_label_country' ),
            'state' => get_option( 'bookly_l10n_label_state' ),
            'postcode' => get_option( 'bookly_l10n_label_postcode' ),
            'city' => get_option( 'bookly_l10n_label_city' ),
            'street' => get_option( 'bookly_l10n_label_street' ),
            'street_number' => get_option( 'bookly_l10n_label_street_number' ),
            'additional_address' => get_option( 'bookly_l10n_label_additional_address' ),
        );
    }

    /**
     * @return array
     */
    public static function getDisplayedAddressFields()
    {
        $fields = array();
        $address_show_fields = (array) get_option( 'bookly_cst_address_show_fields', array() );
        $address_fields = self::getAddressFields();

        foreach ( $address_show_fields as $field => $attributes ) {
            if ( array_key_exists( $field, $address_fields ) && array_key_exists( 'show', $attributes ) && $attributes['show'] ) {
                $fields[ $field ] = true;
            }
        }

        return $fields;
    }

    /**
     * @param array $data
     * @return string
     */
    public static function getFullAddressByCustomerData( array $data )
    {
        $fields  = array();
        $address_empty = true;
        foreach ( self::getDisplayedAddressFields() as $field_name => $attributes ) {
            if ( array_key_exists( $field_name, $data ) ) {
                $fields[ $field_name ] = $data[ $field_name ];
                if ( $data[ $field_name ] != '' ) {
                    $address_empty = false;
                }
            } else {
                $fields[ $field_name ] = null;
            }
        }

        return $address_empty
            ? ''
            : BooklyLib\Utils\Codes::replace( BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_cst_address_template' ), $fields, false );
    }

    /**
     * Create day options.
     *
     * @return array
     */
    public static function dayOptions()
    {
        return array_combine( range( 1, 31 ), range( 1, 31 ) );
    }

    /**
     * Create month options.
     *
     * @return array
     */
    public static function monthOptions()
    {
        global $wp_locale;

        return array_combine( range( 1, 12 ), $wp_locale->month );
    }

    /**
     * Create year options.
     *
     * @param int $delta_from
     * @param int $delta_to
     *
     * @return array
     */
    public static function yearOptions( $delta_from = 0, $delta_to = -100 )
    {
        $year  = (int) BooklyLib\Slots\DatePoint::now()->format( 'Y' );
        $range = range( $year + $delta_from, $year + $delta_to );

        return array_combine( $range, $range );
    }

    /**
     * Create WordPress user
     *
     * @param array $params expected ['first_name', 'last_name', 'full_name', 'email' ]
     * @param string $password
     * @param string $alt_base
     * @return \WP_User
     * @throws BooklyLib\Base\ValidationException
     */
    public static function createWPUser( array $params, &$password, $alt_base = 'client' )
    {
        if ( $params['email'] == '' ) {
            throw new BooklyLib\Base\ValidationException( __( 'Email required', 'bookly' ), 'email' );
        }

        $base = BooklyLib\Config::showFirstLastName()
            ? sanitize_user( sprintf( '%s %s', $params['first_name'], $params['last_name'] ), true )
            : sanitize_user( $params['full_name'], true );
        $base     = $base != '' ? $base : $alt_base;
        $username = $base;
        $i        = 1;
        while ( username_exists( $username ) ) {
            $username = $base . $i;
            ++ $i;
        }
        // Generate password.
        $password = wp_generate_password( 6, true );
        // Create WordPress user.
        $wp_user_id = wp_create_user( $username, $password, $params['email'] );
        if ( is_wp_error( $wp_user_id ) ) {
            throw new BooklyLib\Base\ValidationException( implode( $wp_user_id->get_error_messages(), PHP_EOL ), 'wp_user' );
        }

        return get_user_by( 'id', $wp_user_id );
    }
}