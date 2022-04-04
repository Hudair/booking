<?php
namespace BooklyPro\Frontend\Components\Fields;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

/**
 * Class Address
 * @package BooklyPro\Frontend\Components\Fields
 */
class Address extends BooklyLib\Base\Component
{
    /**
     * Render inputs for address fields on the frontend.
     *
     * @param BooklyLib\UserBookingData $user_data
     */
    public static function render( BooklyLib\UserBookingData $user_data )
    {
        $displayed_fields = Lib\Utils\Common::getDisplayedAddressFields();
        foreach ( $displayed_fields as $field_name => $field ) {
            $field_value = $user_data->getAddressField( $field_name );
            self::renderTemplate( 'address',
                compact( 'field_name', 'field_value' )
            );
        }
        $hidden = true;
        foreach ( Lib\Utils\Common::getAddressFields() as $field_name => $field ) {
            if ( ! array_key_exists( $field_name, $displayed_fields ) ) {
                $field_value = $user_data->getAddressField( $field_name );
                self::renderTemplate( 'address',
                    compact( 'field_name', 'field_value', 'hidden' )
                );
            }
        }
    }
}