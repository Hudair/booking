<?php
namespace BooklyPro\Backend\Components\Settings;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

/**
 * Class Address
 * @package BooklyPro\Backend\Components\Settings
 */
class Address extends BooklyLib\Base\Component
{
    /**
     * Render inputs for address fields in settings.
     */
    public static function render()
    {
        $address_show_fields = (array) get_option( 'bookly_cst_address_show_fields', array() );
        $address_fields = Lib\Utils\Common::getAddressFields();

        foreach ( $address_show_fields as $field_name => $attributes ) {
            if ( array_key_exists( $field_name, $address_fields ) ) {
                $showed = array_key_exists( 'show', $attributes ) && $attributes['show'];
                $label = $address_fields[$field_name];
                self::renderTemplate( 'address', compact( 'field_name', 'label', 'showed' ) );
            }
        }
        foreach ( $address_fields as $field_name => $label ) {
            if ( ! array_key_exists( $field_name, $address_show_fields ) ) {
                $showed = false;
                self::renderTemplate( 'address', compact( 'field_name', 'label', 'showed' ) );
            }
        }

    }
}