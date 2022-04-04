<?php
namespace BooklyPro\Backend\Modules\Notifications\ProxyProviders;

use Bookly\Backend\Modules\Notifications\Proxy;
use Bookly\Lib as BooklyLib;

/**
 * Class Shared
 * @package BooklyPro\Backend\Modules\Notifications\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function prepareNotificationCodes( array $codes, $type )
    {
        $codes['appointment']['online_meeting_url'] = array( 'description' => __( 'Online meeting URL', 'bookly' ) );
        $codes['appointment']['online_meeting_password'] = array( 'description' => __( 'Online meeting password', 'bookly' ) );
        $codes['appointment']['online_meeting_start_url'] = array( 'description' => __( 'Online meeting start URL', 'bookly' ) );
        $codes['appointment']['online_meeting_join_url'] = array( 'description' => __( 'Online meeting join URL', 'bookly' ) );
        $codes['customer']['client_birthday'] = array( 'description' => __( 'Client birthday', 'bookly' ), 'if' => true );
        $codes['staff']['staff_timezone'] = array( 'description' => __( 'Time zone of staff', 'bookly' ), 'if' => true );

        return $codes;
    }

    /**
     * @inheritDoc
     */
    public static function buildNotificationCodesList( array $codes, $notification_type, array $codes_data )
    {
        switch ( $notification_type ) {
            case 'customer_new_wp_user':
                $codes = array_merge(
                    $codes_data['company'],
                    $codes_data['customer'],
                    $codes_data['user_credentials']
                );
                break;
            case 'staff_new_wp_user':
                $codes = array_merge(
                    $codes_data['company'],
                    $codes_data['staff'],
                    $codes_data['user_credentials']
                );
                break;
        }

        return $codes;
    }

    /**
     * @inheritDoc
     */
    public static function enqueueAssets()
    {
        self::enqueueScripts( array(
            'module' => array( 'js/email-logs.js' => array( 'bookly-backend-globals' ) ),
        ) );

        $datatables = BooklyLib\Utils\Tables::getSettings( 'email_logs' );

        wp_localize_script( 'bookly-email-logs.js', 'BooklyEmailLogsL10n', array(
            'datatables' => $datatables,
            'datePicker' => BooklyLib\Utils\DateTime::datePickerOptions(),
            'dateRange' => BooklyLib\Utils\DateTime::dateRangeOptions( array( 'lastMonth' => __( 'Last month', 'bookly' ), ) ),
            'details' => __( 'Details', 'bookly' ),
            'zeroRecords' => __( 'No records for selected period.', 'bookly' ),
            'processing' => __( 'Processing...', 'bookly' ),
        ) );
    }
}