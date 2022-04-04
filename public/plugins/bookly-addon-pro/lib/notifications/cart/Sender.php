<?php
namespace BooklyPro\Lib\Notifications\Cart;

use Bookly\Lib\DataHolders\Booking\Item;
use Bookly\Lib\DataHolders\Booking\Order;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Notifications\Assets\Item\Attachments;
use Bookly\Lib\Notifications\Base;
use BooklyPro\Lib\Notifications\Assets\Combined\Codes;

/**
 * Class Sender
 * @package BooklyPro\Lib\Notifications\Cart
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send combined notifications to client.
     *
     * @param Order      $order
     * @param array|bool $queue
     */
    public static function sendCombined( Order $order, &$queue = false )
    {
        /** @var Item $item */
        $item = current( $order->getItems() );
        $ca   = $item->getCA();

        if ( ! in_array( $ca->getStatus(), array( CustomerAppointment::STATUS_CANCELLED, CustomerAppointment::STATUS_REJECTED ) ) ) {

            $just_created = $ca->isJustCreated() ||
                // Maybe this is IPN request and combined notification should be sent for pending payment appointments created
                ( $ca->getCreatedFrom() == 'frontend' && strtotime( $ca->getCreatedAt() ) - current_time( 'timestamp' ) >= - 120 /* sec */ );

            if ( $just_created ) {
                $codes = new Codes( $order );
                $notifications = static::getNotifications( 'new_booking_combined' );

                $attachments = new Attachments( $codes );
                // Notify client.
                foreach ( $notifications['client'] as $notification ) {
                    static::sendToClient( $order->getCustomer(), $notification, $codes, $attachments, $queue );
                }
                // Reply to customer.
                $reply_to = null;
                if ( get_option( 'bookly_email_reply_to_customers' ) ) {
                    $customer = $order->getCustomer();
                    $reply_to = array( 'email' => $customer->getEmail(), 'name' => $customer->getFullName() );
                }
                // Notify custom.
                foreach ( $notifications['staff'] as $notification ) {
                    static::sendToCustom( $notification, $codes, $attachments, $reply_to, $queue );
                }
                if ( $queue === false ) {
                    $attachments->clear();
                }
            }
        }
    }
}