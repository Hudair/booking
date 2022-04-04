<?php
namespace BooklyPro\Backend\Components\TinyMce\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\TinyMce\Proxy;

/**
 * Class Shared
 * @package BooklyPro\Backend\Components\TinyMce\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function renderMediaButtons( $version )
    {
        if ( $version < 3.5 ) {
            // show button for v 3.4 and below
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-appointment-popup&amp;height=300" id="add-ap-appointment" title="' . esc_attr__( 'Add Bookly appointments list', 'bookly' ) . '">' . __( 'Add Bookly appointments list', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-cancellation-confirmation-popup&amp;height=300" id="add-cancellation-confirmation" title="' . esc_attr__( 'Add appointment cancellation confirmation', 'bookly' ) . '">' . __( 'Add appointment cancellation confirmation', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-calendar&height=300" id="add-bookly-calendar" title="' . esc_attr__( 'Add Bookly calendar', 'bookly' ) . '">' . __( 'Add Bookly calendar', 'bookly' ) . '</a>';
        } else {
            // display button matching new UI
            $img = '<span class="bookly-media-icon"></span> ';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-appointment-popup&amp;height=300" id="add-ap-appointment" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add Bookly appointments list', 'bookly' ) . '">' . $img . __( 'Add Bookly appointments list', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-cancellation-confirmation-popup&amp;height=300" id="add-cancellation-confirmation" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add appointment cancellation confirmation', 'bookly' ) . '">' . $img . __( 'Add appointment cancellation confirmation', 'bookly' ) . '</a>';
            echo '<a href="#TB_inline?width=400&inlineId=bookly-tinymce-calendar&height=300" id="add-bookly-calendar" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add Bookly calendar', 'bookly' ) . '">' . $img . __( 'Add Bookly calendar', 'bookly' ) . '</a>';
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderPopup()
    {
        $casest = BooklyLib\Config::getCaSeSt();
        $custom_fields = (array) BooklyLib\Proxy\CustomFields::getWhichHaveData();
        self::renderTemplate( 'appointment_list', compact( 'custom_fields' ) );
        self::renderTemplate( 'cancellation_confirmation' );
        self::renderTemplate( 'calendar', compact( 'casest' ) );
    }
}