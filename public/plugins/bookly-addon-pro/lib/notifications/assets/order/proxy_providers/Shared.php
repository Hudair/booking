<?php
namespace BooklyPro\Lib\Notifications\Assets\Order\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Notifications\Assets\Order\Codes;
use Bookly\Lib\Notifications\Assets\Order\Proxy;

/**
 * Class Shared
 * @package BooklyPro\Lib\Notifications\Assets\Order\ProxyProviders
 */
abstract class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function prepareCodes( Codes $codes )
    {
        $customer = $codes->getOrder()->getCustomer();
        if ( $customer && $customer->getBirthday() ) {
            $codes->client_birthday = date_i18n( 'F j', strtotime( $customer->getBirthday() ) );
        } else {
            $codes->client_birthday = '';
        }
    }

    /**
     * @inheritDoc
     */
    public static function prepareReplaceCodes( array $replace_codes, Codes $codes, $format )
    {
        $replace_codes['client_birthday'] = $codes->client_birthday;

        return $replace_codes;
    }
}