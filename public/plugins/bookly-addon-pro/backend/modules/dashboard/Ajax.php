<?php
namespace BooklyPro\Backend\Modules\Dashboard;

use Bookly\Lib;

/**
 * Class Ajax
 * @package BooklyPro\Backend\Modules\Dashboard
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * Get analytics.
     */
    public static function getAnalytics()
    {
        $date = self::parameter( 'date' );
        $filter = self::parameter( 'filter', array() );
        $staff_ids = isset( $filter['staff'] ) ? $filter['staff'] : array();
        if ( $staff_ids == 'all' ) {
            $staff_ids = Lib\Entities\Staff::query()->fetchCol( 'id' );
        }

        $service_ids = isset( $filter['services'] )
            ? array_map( function ( $id ) {return $id ?: null;}, $filter['services'] )
            : array();

        $postfix_archived = sprintf( ' (%s)', __( 'Archived', 'bookly' ) );

        Lib\Utils\Tables::updateSettings( 'analytics', array(), array(), $filter );

        $data = array();
        foreach ( $staff_ids as $staff_id ) {
            foreach ( $service_ids as $service_id ) {
                $staff = Lib\Entities\Staff::find( $staff_id );
                $data[ $staff_id ][ $service_id ] = array(
                    'staff'   => $staff->getFullName() . ( $staff->getVisibility() == 'archive' ? $postfix_archived : '' ),
                    'service' => $service_id ? Lib\Entities\Service::find( $service_id )->getTitle() : __( 'Custom', 'bookly' ),
                    'appointments' => array(
                        'total'     => 0,
                        'pending'   => 0,
                        'approved'  => 0,
                        'rejected'  => 0,
                        'cancelled' => 0,
                    ),
                    'customers' => array(
                        'total' => array(),
                        'new'   => array(),
                    ),
                    'revenue' => array(
                        'total' => array(),
                    ),
                );
            }
        }

        list ( $start, $end ) = explode( ' - ', $date, 2 );
        $end = date( 'Y-m-d', strtotime( '+1 day', strtotime( $end ) ) );

        $query = Lib\Entities\CustomerAppointment::query( 'ca' )
            ->select( 'ca.appointment_id, ca.customer_id, ca.status, a.staff_id, a.service_id, a.start_date, p.id AS payment_id, p.paid' )
            ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
            ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' )
            ->leftJoin( 'Payment', 'p', 'p.id = ca.payment_id' )
            ->whereIn( 'a.staff_id', $staff_ids )
            ->whereBetween( 'ca.created_at', $start, $end )
        ;
        if ( array_search( null, $service_ids, true ) !== false ) {
            $where_raw = 'a.service_id IS NULL';
            $service_ids_filtered = array_filter( $service_ids );
            if ( ! empty ( $service_ids_filtered ) ) {
                $where_raw .= sprintf( ' OR a.service_id IN (%s)', implode( ',', $service_ids_filtered ) );
            }
            $query->whereRaw( $where_raw, array() );
        } else {
            $query->whereIn( 'a.service_id', $service_ids );
        }

        $custom_statuses = (array) Lib\Proxy\CustomStatuses::getAll();
        $payments = array();

        foreach ( $query->fetchArray() as $row ) {
            $record = &$data[ $row['staff_id'] ][ $row['service_id'] ];
            switch ( $row['status'] ) {
                case Lib\Entities\CustomerAppointment::STATUS_PENDING:
                    ++ $record['appointments']['pending'];
                    break;
                case Lib\Entities\CustomerAppointment::STATUS_APPROVED:
                    ++ $record['appointments']['approved'];
                    break;
                case Lib\Entities\CustomerAppointment::STATUS_REJECTED:
                    ++ $record['appointments']['rejected'];
                    break;
                case Lib\Entities\CustomerAppointment::STATUS_CANCELLED:
                    ++ $record['appointments']['cancelled'];
                    break;
                default:
                    if ( isset ( $custom_statuses[ $row['status'] ] ) ) {
                        if ( $custom_statuses[ $row['status'] ]->getBusy() ) {
                            // Consider as APPROVED.
                            ++ $record['appointments']['approved'];
                        } else {
                            // Consider as CANCELLED.
                            ++ $record['appointments']['cancelled'];
                        }
                    }
            }
            ++ $record['appointments']['total'];
            $record['customers']['total'][ $row['customer_id'] ] = true;
            if ( ! isset ( $record['customers']['new'][ $row['customer_id'] ] ) ) {
                $ca = Lib\Entities\CustomerAppointment::query( 'ca' )
                    ->select( '1' )
                    ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
                    ->where( 'ca.customer_id', $row['customer_id'] )
                    ->limit( 1 );
                if ( $row['start_date'] ) {
                    $ca->whereLt( 'a.start_date', $row['start_date'] );
                }
                $exists = $ca->execute( Lib\Query::HYDRATE_NONE );
                if ( ! $exists ) {
                    $record['customers']['new'][ $row['customer_id'] ] = true;
                }
            }
            // Revenue.
            if ( $row['payment_id'] ) {
                $record['revenue']['total'][ $row['payment_id'] ] = $row['paid'];
                $payments[ $row['payment_id'] ] = $row['paid'];
            }

            unset ( $record );
        }

        $result = array();
        $total  = array(
            'appointments' => array(
                'total'     => 0,
                'pending'   => 0,
                'approved'  => 0,
                'rejected'  => 0,
                'cancelled' => 0,
            ),
            'customers' => array(
                'total' => 0,
                'new'   => 0,
            ),
            'revenue' => array(
                'total' => array_sum( $payments ),
            ),
        );
        foreach ( $data as $staff_data ) {
            foreach ( $staff_data as $record ) {
                $record['customers']['total'] = count( $record['customers']['total'] );
                $record['customers']['new']   = count( $record['customers']['new'] );

                $record['revenue']['total'] = array_sum( $record['revenue']['total'] );
                $record['revenue']['total_formatted'] = Lib\Utils\Price::format( $record['revenue']['total'] );

                $result[] = $record;

                $total['appointments']['total']     += $record['appointments']['total'];
                $total['appointments']['pending']   += $record['appointments']['pending'];
                $total['appointments']['approved']  += $record['appointments']['approved'];
                $total['appointments']['rejected']  += $record['appointments']['rejected'];
                $total['appointments']['cancelled'] += $record['appointments']['cancelled'];
                $total['customers']['total']        += $record['customers']['total'];
                $total['customers']['new']          += $record['customers']['new'];
            }
        }
        $total['revenue']['total_formatted'] = Lib\Utils\Price::format( $total['revenue']['total'] );

        wp_send_json( array( 'data' => $result, 'total' => $total ) );
    }
}