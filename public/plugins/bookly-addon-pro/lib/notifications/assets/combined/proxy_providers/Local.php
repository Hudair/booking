<?php
namespace BooklyPro\Lib\Notifications\Assets\Combined\ProxyProviders;

use Bookly\Lib\Notifications\Assets\Item\Proxy;
use BooklyPro\Lib\Notifications\Assets\Combined\Codes;
use BooklyPro\Lib\Notifications\Assets\Combined\ICS;

/**
 * Class Local
 *
 * @package BooklyPro\Lib\Notifications\Assets\Combined\ProxyProviders
 */
abstract class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function createICS( Codes $codes )
    {
        return new ICS( $codes );
    }
}