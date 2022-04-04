<?php
namespace BooklyPro\Lib\Notifications\NewWpUser;

use Bookly\Lib\Entities\Customer;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Notifications\Base;
use BooklyPro\Lib\Notifications\Assets\NewWpUser;

/**
 * Class NewUser
 * @package BooklyPro\Lib\Notifications\NewWpUser
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send email/sms with username and password for newly created WP user.
     *
     * @param Customer $customer
     * @param string $username
     * @param string $password
     */
    public static function sendAuthToClient( Customer $customer, $username, $password )
    {
        $codes = new NewWpUser\Client\Codes( $customer, $username, $password );
        $notifications = static::getNotifications( 'customer_new_wp_user' );

        // Notify client.
        foreach ( $notifications['client'] as $notification ) {
            static::sendToClient( $customer, $notification, $codes );
        }
    }

    /**
     * Send email/sms with username and password for newly created WP user.
     *
     * @param Staff $staff
     * @param string $username
     * @param string $password
     */
    public static function sendAuthToStaff( Staff $staff, $username, $password )
    {
        $codes = new NewWpUser\Staff\Codes( $staff, $username, $password );
        $notifications = static::getNotifications( 'staff_new_wp_user' );

        // Notify staff.
        foreach ( $notifications['staff'] as $notification ) {
            static::sendToStaff( $staff, $notification, $codes );
        }
    }
}