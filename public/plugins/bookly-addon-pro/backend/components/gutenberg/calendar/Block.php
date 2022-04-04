<?php
namespace BooklyPro\Backend\Components\Gutenberg\Calendar;

use Bookly\Lib;

/**
 * Class Block
 * @package BooklyPro\Backend\Components\Gutenberg\Calendar
 */
class Block extends Lib\Base\Block
{
    /**
     * @inheritDoc
     */
    public static function registerBlockType()
    {
        self::enqueueScripts( array(
            'module' => array(
                'js/calendar-block.js' => array( 'jquery', 'wp-blocks', 'wp-components', 'wp-element', 'wp-editor' ),
            ),
        ) );

        wp_localize_script( 'bookly-calendar-block.js', 'BooklyL10nCalendar', array(
            'casest' => Lib\Config::getCaSeSt(),
            'block' => array(
                'title' => 'Bookly - ' . __( 'Calendar', 'bookly' ),
                'description' => __( 'A custom block for displaying frontend calendar', 'bookly' ),
            ),
            'any' => __( 'Any', 'bookly' ),
            'location' => __( 'Location', 'bookly' ),
            'service' => __( 'Service', 'bookly' ),
            'staff' => __( 'Staff', 'bookly' ),
            'help' => sprintf( __( 'Check status of this option in Settings > Calendar > <a href="%s" target="_blank"/>Display front-end calendar</a>', 'bookly' ), Lib\Utils\Common::escAdminUrl( \Bookly\Backend\Modules\Settings\Page::pageSlug(), array( 'tab' => 'calendar#bookly_cal_frontend_enabled' ) ) ),
            'locationCustom' => (int) Lib\Proxy\Locations::servicesPerLocationAllowed(),
            'addons' => array(
                'locations' => (int) Lib\Config::locationsActive(),
            ),
        ) );

        register_block_type( 'bookly/calendar-block', array(
            'editor_script' => 'bookly-calendar-block.js',
        ) );
    }
}