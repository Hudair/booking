<?php
namespace Bookly\Backend\Components\Dialogs\TableSettings;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Components\Dialogs\TableSettings
 */
class Ajax extends Lib\Base\Ajax
{
    /** @var array */
    protected static $tables = array(
        'appointments',
        'cloud_purchases',
        'coupons',
        'custom_statuses',
        'customer_groups',
        'customers',
        'discounts',
        'email_logs',
        'email_notifications',
        'locations',
        'packages',
        'payments',
        'services',
        'staff_members',
        'taxes',
        'sms_details',
        'sms_notifications',
        'sms_prices',
        'sms_sender',
    );

    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => array( 'staff', 'supervisor' ) );
    }

    /**
     * Update table settings.
     */
    public static function updateTableSettings()
    {
        $table = self::parameter( 'table' );

        $meta = get_user_meta( get_current_user_id(), 'bookly_' . $table . '_table_settings', true ) ?: array();
        if ( in_array( $table, self::$tables ) ) {
            $meta['columns'] = self::parameter( 'columns', array() );
            array_walk( $meta['columns'], function ( &$show ) { $show = (bool) $show; } );
            update_user_meta( get_current_user_id(), 'bookly_' . $table . '_table_settings', $meta );
        }

        wp_send_json_success();
    }

    /**
     * Update table sorting.
     */
    public static function updateTableOrder()
    {
        $table = self::parameter( 'table' );

        $meta = get_user_meta( get_current_user_id(), 'bookly_' . $table . '_table_settings', true ) ?: array();
        if ( in_array( $table, self::$tables ) ) {
            $meta['order'] = self::parameter( 'order', array() );
            update_user_meta( get_current_user_id(), 'bookly_' . $table . '_table_settings', $meta );
        }

        wp_send_json_success();
    }
}