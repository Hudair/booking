<?php
namespace BooklyPro\Frontend\Components\Fields;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

/**
 * Class Birthday
 * @package BooklyPro\Frontend\Components\Fields
 */
class Birthday extends BooklyLib\Base\Component
{
    /**
     * Render triple select for birthday field on the frontend.
     *
     * @param BooklyLib\UserBookingData $user_data
     */
    public static function render( BooklyLib\UserBookingData $user_data )
    {
        $values = array( 'day' => '', 'month' => '', 'year' => '' );

        // Selected values.
        $birthday = $user_data->getBirthday();
        if ( is_array( $birthday ) ) {
            $values['day']   = $birthday['day']   ;
            $values['month'] = $birthday['month'] ;
            $values['year']  = $birthday['year']  ;
        }

        // Render HTML.
        foreach ( BooklyLib\Utils\DateTime::getDatePartsOrder() as $type ) {
            self::_renderField( $type, $values[ $type ] );
        }
    }

    /**
     * Render triple select for birthday field on the frontend.
     *
     * @param string $birthday
     */
    public static function renderBootstrap( $birthday )
    {
        $values = array( 'day' => '', 'month' => '', 'year' => '' );

        // Selected values.
        if ( $birthday != '' ) {
            $timestamp = strtotime( $birthday );
            $values['day']   = date( 'j', $timestamp );
            $values['month'] = date( 'n', $timestamp );
            $values['year']  = date( 'Y', $timestamp );
        }

        // Render HTML.
        foreach ( BooklyLib\Utils\DateTime::getDatePartsOrder() as $type ) {
            self::_renderFieldBootstrap( $type, $values[ $type ] );
        }
    }

    /**
     * Render single field of given type.
     *
     * @param string $type
     * @param string $selected_value
     */
    protected static function _renderField( $type, $selected_value )
    {
        $title   = BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_birthday_' . $type );
        $empty   = BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_' . $type );
        $options = array();

        switch ( $type ) {
            case 'day':
                $options = Lib\Utils\Common::dayOptions();
                break;
            case 'month':
                $options = Lib\Utils\Common::monthOptions();
                break;
            case 'year':
                $options = Lib\Utils\Common::yearOptions();
                break;
        }

        self::renderTemplate(
            'birthday',
            compact( 'type', 'selected_value', 'title', 'empty', 'options' )
        );
    }

    /**
     * Render single field of given type.
     *
     * @param string $type
     * @param string $selected_value
     */
    protected static function _renderFieldBootstrap( $type, $selected_value )
    {
        $title   = BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_birthday_' . $type );
        $empty   = BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_' . $type );
        $options = array();

        switch ( $type ) {
            case 'day':
                $options = Lib\Utils\Common::dayOptions();
                break;
            case 'month':
                $options = Lib\Utils\Common::monthOptions();
                break;
            case 'year':
                $options = Lib\Utils\Common::yearOptions();
                break;
        }

        self::renderTemplate(
            'birthday_bootstrap',
            compact( 'type', 'selected_value', 'title', 'empty', 'options' )
        );
    }
}