<?php
namespace BooklyPro\Backend\Modules\Services\ProxyProviders;

use Bookly\Backend\Modules\Services\Proxy;
use BooklyPro\Lib;

/**
 * Class Shared
 * @package BooklyPro\Backend\Modules\Services\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function duplicateService( $source_id, $target_id )
    {
        foreach ( Lib\Entities\StaffPreferenceOrder::query()->where( 'service_id', $source_id )->fetchArray() as $record ) {
            $new_record = new Lib\Entities\StaffPreferenceOrder( $record );
            $new_record->setId( null )->setServiceId( $target_id )->save();
        }
    }
}