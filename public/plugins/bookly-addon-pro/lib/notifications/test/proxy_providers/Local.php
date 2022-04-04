<?php
namespace BooklyPro\Lib\Notifications\Test\ProxyProviders;

use Bookly\Lib;
use Bookly\Lib\Notifications\Test\Proxy;
use BooklyPro\Lib\Notifications\Test\Sender;

/**
 * Class Local
 * @package BooklyPro\Lib\Notifications\Cart\ProxyProviders
 */
abstract class Local extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function send( $to_email, Lib\Entities\Notification $notification, $codes, $attachments, $reply_to, $send_as, $from )
    {
        Sender::send( $to_email, $notification, $codes, $attachments, $reply_to, $send_as, $from );
    }
}