<?php
namespace BooklyPro\Backend\Components\Notices\ProxyProviders;

use Bookly\Backend\Components\Notices\Proxy;

/**
 * Class Local
 * @package BooklyPro\Backend\Components\Notices\ProxyProviders
 */
class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderWelcome()
    {
        return self::renderTemplate( 'welcome' );
    }
}