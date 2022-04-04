<?php
namespace BooklyPro\Backend\Components\Gutenberg\AppointmentsList;

use Bookly\Lib as BooklyLib;

/**
 * Class Block
 * @package Bookly\Backend\Components\Gutenberg\AppointmentsList
 */
class Block extends BooklyLib\Base\Block
{
    /**
     * @inheritDoc
     */
    public static function registerBlockType()
    {
        self::enqueueScripts( array(
            'module' => array(
                'js/appointments-list-block.js' => array( 'jquery', 'wp-blocks', 'wp-components', 'wp-element', 'wp-editor' ),
            ),
        ) );

        $custom_fields = BooklyLib\Proxy\CustomFields::getWhichHaveData();
        wp_localize_script( 'bookly-appointments-list-block.js', 'BooklyAppointmentListL10n', array(
            'block' => array(
                'title' => 'Bookly - ' . __( 'Appointments list', 'bookly' ),
                'description' => __( 'A custom block for displaying appointments list', 'bookly' ),
            ),
            'titles' => __( 'Titles', 'bookly' ),
            'show' => __( 'show', 'bookly' ),
            'columns' => __( 'Columns', 'bookly' ),
            'tableColumns' => array(
                'category' => __( 'Category', 'bookly' ),
                'service' => __( 'Service', 'bookly' ),
                'staff' => __( 'Employee', 'bookly' ),
                'date' => __( 'Date', 'bookly' ),
                'time' => __( 'Time', 'bookly' ),
                'price' => __( 'Price', 'bookly' ),
                'status' => __( 'Status', 'bookly' ),
                'cancel' => __( 'Cancel', 'bookly' ),
                'online_meeting' => __( 'Online meeting', 'bookly' ),
            ),
            'customFieldsTitle' => __( 'Custom fields', 'bookly' ),
            'customFields' => $custom_fields === null ? array() : array_values( $custom_fields ),
        ) );

        register_block_type( 'bookly/appointments-list-block', array(
            'editor_script' => 'bookly-appointments-list-block.js',
        ) );
    }
}