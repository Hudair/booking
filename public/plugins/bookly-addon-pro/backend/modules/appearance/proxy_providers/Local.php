<?php
namespace BooklyPro\Backend\Modules\Appearance\ProxyProviders;

use Bookly\Backend\Modules\Appearance\Proxy;
use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

/**
 * Class Local
 * @package BooklyPro\Backend\Modules\Appointments\ProxyProviders
 */
class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderBookingStatesSelector()
    {
        self::renderTemplate( 'booking_states_selector' );
    }

    /**
     * @inheritDoc
     */
    public static function renderBookingStatesText()
    {
        self::renderTemplate( 'booking_states_text' );
    }

    /**
     * @inheritDoc
     */
    public static function renderPaymentImpossible()
    {
        self::renderTemplate( 'payment_impossible' );
    }

    /**
     * @inheritDoc
     */
    public static function renderShowAddress()
    {
        self::renderTemplate( 'show_address' );
    }

    /**
     * @inheritDoc
     */
    public static function renderShowBirthday()
    {
        self::renderTemplate( 'show_birthday' );
    }

    /**
     * @inheritDoc
     */
    public static function renderTimeZoneSwitcher()
    {
        $current_offset = get_option( 'gmt_offset' );
        $tz_string      = get_option( 'timezone_string' );
        if ( $tz_string == '' ) { // Create a UTC+- zone if no timezone string exists
            if ( $current_offset == 0 ) {
                $tz_string = 'UTC+0';
            } else if ( $current_offset < 0 ) {
                $tz_string = 'UTC' . $current_offset;
            } else {
                $tz_string = 'UTC+' . $current_offset;
            }
        }

        self::renderTemplate( 'time_zone_switcher', compact( 'tz_string' ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderTimeZoneSwitcherCheckbox()
    {
        self::renderTemplate( 'time_zone_switcher_checkbox' );
    }

    /**
     * @inheritDoc
     */
    public static function renderFacebookButton()
    {
        self::renderTemplate( 'fb_button' );
    }

    /**
     * @inheritDoc
     */
    public static function renderShowFacebookButton()
    {
        self::renderTemplate( 'show_fb_button_checkbox' );
    }

    /**
     * @inheritDoc
     */
    public static function renderTips()
    {
        self::renderTemplate( 'tips' );
    }

    /**
     * @inheritDoc
     */
    public static function renderShowTips()
    {
        self::renderTemplate( 'show_tips' );
    }

    /**
     * @inheritDoc
     */
    public static function renderAddress()
    {
        $address_is_required = BooklyLib\Config::addressRequired();
        $address = array();
        foreach ( Lib\Utils\Common::getDisplayedAddressFields() as $field_name => $field ) {
            $labels = array( 'bookly_l10n_label_' . $field_name );
            if ( $address_is_required ) {
                $labels[] = 'bookly_l10n_required_' . $field_name;
            }
            $id             = 'bookly-js-address-' . $field_name;
            $address[ $id ] = $labels;
        }
        self::renderTemplate( 'address', compact( 'address' ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderBirthday()
    {
        // Render HTML.
        $fields = array();
        foreach ( BooklyLib\Utils\DateTime::getDatePartsOrder() as $type ) {
            $fields[] = self::_renderEditableField( $type );
        }

        self::renderTemplate( 'birthday', compact( 'fields' ) );
    }

    /**
     * Render single editable field of given type.
     *
     * @param string $type
     *
     * @return string
     */
    protected static function _renderEditableField( $type )
    {
        $editable = array( 'bookly_l10n_label_birthday_' . $type, 'bookly_l10n_option_' . $type, 'bookly_l10n_required_' . $type );
        $empty    = get_option( 'bookly_l10n_option_' . $type );
        $options  = array();

        switch ( $type ) {
            case 'day':
                $editable[] = 'bookly_l10n_invalid_day';
                $options    = Lib\Utils\Common::dayOptions();
                break;
            case 'month':
                $options = Lib\Utils\Common::monthOptions();
                break;
            case 'year':
                $options = Lib\Utils\Common::yearOptions();
                break;
        }

        return self::renderTemplate( 'birthday_fields', compact( 'type', 'editable', 'empty', 'options' ), false );
    }
}