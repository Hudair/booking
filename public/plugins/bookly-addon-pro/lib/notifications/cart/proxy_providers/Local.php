<?php
namespace BooklyPro\Lib\Notifications\Cart\ProxyProviders;

use Bookly\Lib\DataHolders\Booking\Order;
use Bookly\Lib\Notifications\Cart\Proxy;
use BooklyPro\Lib\Notifications\Cart\Sender;

/**
 * Class Local
 * @package BooklyPro\Lib\Notifications\Cart\ProxyProviders
 */
abstract class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function sendCombinedToClient( $queue, Order $order )
    {
        Sender::sendCombined( $order, $queue );

        return $queue;
    }
}