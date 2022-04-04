<?php
namespace BooklyPro\Backend\Modules\Notifications;

use BooklyPro\Lib;
use Bookly\Lib as BooklyLib;

/**
 * Class Ajax
 *
 * @package BooklyPro\Backend\Modules\Notifications
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'supervisor' );
    }

    /**
     * Get email logs.
     */
    public static function getEmailLogs()
    {
        $range = self::parameter( 'range' );

        $query = Lib\Entities\EmailLog::query( 'e' )
            ->select( 'e.*' );
        $total = $query->count();

        // Filters.
        list ( $start, $end ) = explode( ' - ', $range, 2 );
        $end = date( 'Y-m-d', strtotime( '+1 day', strtotime( $end ) ) );

        $query->whereBetween( 'e.created_at', $start, $end );

        $query->limit( self::parameter( 'length' ) )->offset( self::parameter( 'start' ) );

        // Order.
        $order = self::parameter( 'order', array() );
        $columns = self::parameter( 'columns' );

        foreach ( $order as $sort_by ) {
            $query->sortBy( '`' . str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) . '`' )
                ->order( $sort_by['dir'] == 'desc' ? BooklyLib\Query::ORDER_DESCENDING : BooklyLib\Query::ORDER_ASCENDING );
        }

        $logs = $query->fetchArray();

        $data = array();
        foreach ( $logs as $record ) {
            $data[] = array(
                'id' => $record['id'],
                'to' => $record['to'],
                'subject' => $record['subject'],
                'body' => $record['body'],
                'headers' => json_decode( $record['headers'] ),
                'attach' => json_decode( $record['attach'] ),
                'created_at' => BooklyLib\Utils\DateTime::formatDateTime( $record['created_at'] ),
            );
        }

        wp_send_json( array(
            'draw' => ( int ) self::parameter( 'draw' ),
            'recordsTotal' => count( $logs ),
            'recordsFiltered' => $total,
            'data' => $data,
        ) );
    }

    /**
     * Get email logs.
     */
    public static function deleteEmailLogs()
    {
        Lib\Entities\EmailLog::query()->delete()->whereIn( 'id', array_map( 'intval', self::parameter( 'data', array() ) ) )->execute();

        wp_send_json_success();
    }
}