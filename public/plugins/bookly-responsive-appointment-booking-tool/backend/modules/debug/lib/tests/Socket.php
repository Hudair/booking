<?php
namespace Bookly\Backend\Modules\Debug\Lib\Tests;

use Bookly\Lib\API;
use Bookly\Lib\Cloud\API as CloudAPI;

/**
 * Class Socket
 * @package Bookly\Backend\Modules\Debug\Lib\Tests
 */
class Socket extends Base
{
    protected $name = 'Check API servers availability';

    /** @inheritDoc */
    public function execute()
    {
        $port    = 443;
        $timeout = 1;
        $hosts = array( parse_url( API::API_URL, PHP_URL_HOST ), parse_url( CloudAPI::API_URL, PHP_URL_HOST ) );
        $available = true;
        foreach ( $hosts as $host ) {
            $fp = fsockopen( $host, $port, $errno, $errstr, $timeout );
            if ( ! $fp ) {
                $available = false;
                $this->addError( $host . ' ' . $errstr );
            }
        }

        if ( $available ) {
            return true;
        } else {
            foreach ( array( 'www.google.com', 'www.amazon.com' ) as $host ) {
                $time = microtime( true );
                fsockopen( $host, $port, $errno, $errstr, $timeout );
                $this->addError( $host . "\t time=" . round( ( ( microtime( true ) - $time ) * 1000 ), 0 ) . ' ms' );
            }
        }
        return false;
    }

}