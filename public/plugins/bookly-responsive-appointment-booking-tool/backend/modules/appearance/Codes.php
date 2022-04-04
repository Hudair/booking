<?php
namespace Bookly\Backend\Modules\Appearance;

/**
 * Class Codes
 * @package Bookly\Backend\Modules\Appearance
 */
class Codes
{
    /**
     * Get JSON for appearance codes
     *
     * @param int $step
     * @param bool $extra_codes
     * @return string
     */
    public static function getJson( $step = null, $extra_codes = false )
    {
        $codes = Proxy\Shared::prepareCodes( array(
            'appointments' => array(
                'description' => array(
                    __( 'Loop over appointments list', 'bookly' ),
                    __( 'Loop over appointments list with delimiter', 'bookly' ),
                ),
                'loop' => array(
                    'item' => 'appointment',
                    'codes' => array(
                        'service_name' => array( 'description' => __( 'Service name of appointment', 'bookly' ) ),
                        'service_info' => array( 'description' => __( 'Info of service', 'bookly' ), 'if' => true  ),
                        'category_name' => array( 'description' => __( 'Service category name of appointment', 'bookly' ), 'if' => true ),
                        'service_duration' => array( 'description' => __( 'Service duration of appointment', 'bookly' ) ),
                        'service_price' => array( 'description' => __( 'Service price of appointment', 'bookly' ), 'if' => true ),
                        'staff_name' => array( 'description' => __( 'Staff member full name in appointment', 'bookly' ) ),
                        'appointment_date' => array( 'description' => __( 'Date of appointment', 'bookly' ), 'if' => true ),
                        'appointment_time' => array( 'description' => __( 'Time of appointment', 'bookly' ), 'if' => true ),
                    ),
                ),
                'flags' => array( 'step' => '>1' ),
            ),
            'appointments_count' => array( 'description' => __( 'Total quantity of appointments in cart', 'bookly' ), 'flags' => array( 'step' => 7, 'extra_codes' => true ) ),
            'appointment_date' => array( 'description' => __( 'Date of appointment', 'bookly' ), 'if' => true, 'flags' => array( 'step' => '>3' ) ),
            'appointment_time' => array( 'description' => __( 'Time of appointment', 'bookly' ), 'if' => true, 'flags' => array( 'step' => '>3' ) ),
            'booking_number' => array( 'description' => __( 'Booking number', 'bookly' ), 'flags' => array( 'step' => 8, 'extra_codes' => true ) ),
            'category_name' => array( 'description' => __( 'Name of category', 'bookly' ) ),
            'login_form' => array( 'description' => __( 'Login form', 'bookly' ), 'flags' => array( 'step' => 6, 'extra_codes' => true ) ),
            'service_duration' => array( 'description' => __( 'Duration of service', 'bookly' ) ),
            'service_info' => array( 'description' => __( 'Info of service', 'bookly' ), 'if' => true ),
            'service_name' => array( 'description' => __( 'Name of service', 'bookly' ) ),
            'service_image' => array( 'description' => __( 'Image of service', 'bookly' ), 'if' => true ),
            'service_price' => array( 'description' => __( 'Price of service', 'bookly' ), 'if' => true ),
            'staff_info' => array( 'description' => __( 'Info of staff member', 'bookly' ), 'if' => true ),
            'staff_name' => array( 'description' => __( 'Full name of staff member', 'bookly' ) ),
            'staff_photo' => array( 'description' => __( 'Photo of staff member', 'bookly' ), 'if' => true, 'flags' => array( 'step' => '>1' ) ),
            'total_price' => array( 'description' => __( 'Total price of booking', 'bookly' ), 'if' => true ),
        ) );

        $codes = self::filter( $codes, compact( 'step', 'extra_codes' ) );

        return json_encode( $codes );
    }

    /**
     * Get JSON for appearance services codes
     *
     * @return string
     */
    public static function getServiceCodes()
    {
        return json_encode( array(
            'service_duration' => array( 'description' => __( 'Duration of service', 'bookly' ) ),
            'service_info' => array( 'description' => __( 'Info of service', 'bookly' ), 'if' => true ),
            'service_name' => array( 'description' => __( 'Name of service', 'bookly' ) ),
            'service_image' => array( 'description' => __( 'Image of service', 'bookly' ), 'if' => true ),
            'service_image_url' => array( 'description' => __( 'URL of service image (to use inside img tag)', 'bookly' ), 'if' => true ),
            'service_price' => array( 'description' => __( 'Price of service', 'bookly' ), 'if' => true ),
        ) );
    }

    /**
     * Get JSON for appearance staff codes
     *
     * @return string
     */
    public static function getStaffCodes()
    {
        return json_encode( array(
            'staff_info' => array( 'description' => __( 'Info of staff member', 'bookly' ), 'if' => true ),
            'staff_name' => array( 'description' => __( 'Full name of staff member', 'bookly' ) ),
            'staff_photo' => array( 'description' => __( 'Photo of staff member', 'bookly' ), 'if' => true ),
            'staff_photo_url' => array( 'description' => __( 'URL of staff photo (to use inside img tag)', 'bookly' ), 'if' => true ),
        ) );
    }

    /**
     * Filter codes
     *
     * @param array $codes
     * @param array $flags
     * @return array
     */
    protected static function filter( array $codes, $flags = array() )
    {
        // Sort codes alphabetically.
        ksort( $codes );

        $result = array();
        foreach ( $codes as $code => $data ) {
            $valid = true;
            if ( isset ( $data['flags'] ) ) {
                foreach ( $data['flags'] as $flag => $value ) {
                    $valid = false;
                    if ( isset ( $flags[ $flag ] ) ) {
                        if ( is_string( $value ) && preg_match( '/([!>=<]+)(\d+)/', $value, $match ) ) {
                            switch ( $match[1] ) {
                                case '<':
                                    $valid = $flags[ $flag ] < $match[2];
                                    break;
                                case '<=':
                                    $valid = $flags[ $flag ] <= $match[2];
                                    break;
                                case '=':
                                    $valid = $flags[ $flag ] == $match[2];
                                    break;
                                case '!=':
                                    $valid = $flags[ $flag ] != $match[2];
                                    break;
                                case '>=':
                                    $valid = $flags[ $flag ] >= $match[2];
                                    break;
                                case '>':
                                    $valid = $flags[ $flag ] > $match[2];
                                    break;
                            }
                        } else {
                            $valid = $flags[ $flag ] == $value;
                        }
                    }
                    if ( ! $valid ) {
                        break;
                    }
                }
            }
            if ( $valid ) {
                $result[ $code ] = $data;
            }
        }

        return $result;
    }
}