<?php
namespace BooklyPro\Frontend\Modules\Calendar;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Config;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Utils\Common;
use Bookly\Lib\Utils\DateTime;
use Bookly\Backend\Modules\Calendar\Ajax as CalendarAjax;

/**
 * Class Ajax
 * @package BooklyPro\Frontend\Modules\Calendar
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    /**
     * Get appointments for frontend calendar
     */
    public static function getCalendarAppointments()
    {
        $result = array();
        $one_day = new \DateInterval( 'P1D' );
        $start_date = new \DateTime( self::parameter( 'start' ) );
        $end_date = new \DateTime( self::parameter( 'end' ) );
        $location_id = self::parameter( 'location_id' );
        $staff_id = self::parameter( 'staff_id' );
        $service_id = self::parameter( 'service_id' );

        // Determine display time zone
        $display_tz = Common::getCurrentUserTimeZone();

        // Due to possibly different time zones of staff members expand start and end dates
        // to provide 100% coverage of the requested date range
        $start_date->sub( $one_day );
        $end_date->add( $one_day );

        // Load staff members
        $query = Staff::query()->whereNot( 'visibility', 'archive' );
        if ( $staff_id ) {
            $query->where( 'id', $staff_id );
        }
        /** @var Staff[] $staff_members */
        $staff_members = $query->find();

        if ( ! empty ( $staff_members ) ) {
            // Load special days.
            $special_days = array();
            $staff_ids = array_map( function ( $staff ) { return $staff->getId(); }, $staff_members );
            $schedule  = BooklyLib\Proxy\SpecialDays::getSchedule( $staff_ids, $start_date, $end_date ) ?: array();
            foreach ( $schedule as $day ) {
                if ( $location_id === null || in_array( $day['location_id'], $location_id ) ) {
                    $special_days[ $day['staff_id'] ][ $day['date'] ][] = $day;
                }
            }

            foreach ( $staff_members as $staff ) {
                $query = CalendarAjax::getAppointmentsQueryForCalendar( $staff->getId(), $start_date, $end_date, $location_id ? array( $location_id ) : null );
                if ( $service_id ) {
                    $query->where( 'a.service_id', $service_id );
                }
                $appointments = self::buildAppointmentsForCalendar( $query, $display_tz );
                $result = array_merge( $result, $appointments );

                // Schedule
                $schedule = array();
                $items = $staff->getScheduleItems();
                $day   = clone $start_date;
                // Find previous day end time.
                $last_end = clone $day;
                $last_end->sub( $one_day );
                $end_time = $items[ (int) $last_end->format( 'w' ) + 1 ]->getEndTime();
                if ( $end_time !== null ) {
                    $end_time = explode( ':', $end_time );
                    $last_end->setTime( $end_time[0], $end_time[1] );
                } else {
                    $last_end->setTime( 24, 0 );
                }
                // Do the loop.
                while ( $day < $end_date ) {
                    $start = $last_end->format( 'Y-m-d H:i:s' );
                    // Check if $day is Special Day for current staff.
                    if ( isset ( $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ] ) ) {
                        $sp_days = $special_days[ $staff->getId() ][ $day->format( 'Y-m-d' ) ];
                        $end     = $sp_days[0]['date'] . ' ' . $sp_days[0]['start_time'];
                        if ( $start < $end ) {
                            $schedule[] = compact( 'start', 'end' );
                        }
                        // Breaks.
                        foreach ( $sp_days as $sp_day ) {
                            if ( $sp_day['break_start'] ) {
                                $break_start = date(
                                    'Y-m-d H:i:s',
                                    strtotime( $sp_day['date'] ) + DateTime::timeToSeconds( $sp_day['break_start'] )
                                );
                                $break_end = date(
                                    'Y-m-d H:i:s',
                                    strtotime( $sp_day['date'] ) + DateTime::timeToSeconds( $sp_day['break_end'] )
                                );
                                $schedule[] = array(
                                    'start' => $break_start,
                                    'end'   => $break_end,
                                );
                            }
                        }
                        $end_time = explode( ':', $sp_days[0]['end_time'] );
                        $last_end = clone $day;
                        $last_end->setTime( $end_time[0], $end_time[1] );
                    } else {
                        $item = $items[ (int) $day->format( 'w' ) + 1 ];
                        if ( $item->getStartTime() && ! $staff->isOnHoliday( $day ) ) {
                            $end = $day->format( 'Y-m-d ' . $item->getStartTime() );
                            if ( $start < $end ) {
                                $schedule[] = compact( 'start', 'end' );
                            }
                            $last_end = clone $day;
                            $end_time = explode( ':', $item->getEndTime() );
                            $last_end->setTime( $end_time[0], $end_time[1] );

                            // Breaks.
                            foreach ( $item->getBreaksList() as $break ) {
                                $break_start = date(
                                    'Y-m-d H:i:s',
                                    $day->getTimestamp() + DateTime::timeToSeconds( $break['start_time'] )
                                );
                                $break_end = date(
                                    'Y-m-d H:i:s',
                                    $day->getTimestamp() + DateTime::timeToSeconds( $break['end_time'] )
                                );
                                $schedule[] = array(
                                    'start' => $break_start,
                                    'end'   => $break_end,
                                );
                            }
                        }
                    }

                    $day->add( $one_day );
                }

                if ( $last_end->format( 'Ymd' ) != $day->format( 'Ymd' ) ) {
                    $schedule[] = array(
                        'start' => $last_end->format( 'Y-m-d H:i:s' ),
                        'end'   => $day->format( 'Y-m-d 24:00:00' ),
                    );
                }

                // Add schedule to result,
                // with appropriate time zone shift if needed
                $staff_tz = $staff->getTimeZone();
                $convert_tz = $staff_tz && $staff_tz !== $display_tz;
                foreach ( $schedule as $item ) {
                    if ( $convert_tz ) {
                        $item['start'] = DateTime::convertTimeZone( $item['start'], $staff_tz, $display_tz );
                        $item['end']   = DateTime::convertTimeZone( $item['end'], $staff_tz, $display_tz );
                    }
                    $result[] = array(
                        'start' => $item['start'],
                        'end' => $item['end'],
                        'display' => 'background',
                    );
                }
            }
        }

        wp_send_json( $result );
    }

    /**
     * Build appointments for Event Calendar.
     *
     * @param BooklyLib\Query $query
     * @param string $display_tz
     * @return array
     */
    private static function buildAppointmentsForCalendar( BooklyLib\Query $query, $display_tz )
    {
        $coloring_mode = get_option( 'bookly_cal_coloring_mode' );
        $query
            ->select( 'a.id, a.start_date, DATE_ADD(a.end_date, INTERVAL IF(ca.extras_consider_duration, a.extras_duration, 0) SECOND) AS end_date, COALESCE(s.color,"silver") AS service_color, s.title AS service_name, st.color AS staff_color' )
            ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
            ->leftJoin( 'Customer', 'c', 'c.id = ca.customer_id' )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
            ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id' );

        $appointments = array();
        $wp_tz = Config::getWPTimeZone();
        $convert_tz = $display_tz !== $wp_tz;

        foreach ( $query->fetchArray() as $appointment ) {
            if ( ! isset ( $appointments[ $appointment['id'] ] ) ) {
                if ( $convert_tz ) {
                    $appointment['start_date'] = DateTime::convertTimeZone( $appointment['start_date'], $wp_tz, $display_tz );
                    $appointment['end_date']   = DateTime::convertTimeZone( $appointment['end_date'], $wp_tz, $display_tz );
                }
                $appointments[ $appointment['id'] ] = $appointment;
            }
            $appointments[ $appointment['id'] ]['customers'][] = array(
                'status' => $appointment['status'],
            );
        }
        $colors = array();
        if ( $coloring_mode == 'status' ) {
            $colors = BooklyLib\Proxy\Shared::prepareColorsStatuses( array(
                CustomerAppointment::STATUS_PENDING => get_option( 'bookly_appointment_status_pending_color' ),
                CustomerAppointment::STATUS_APPROVED => get_option( 'bookly_appointment_status_approved_color' ),
                CustomerAppointment::STATUS_CANCELLED => get_option( 'bookly_appointment_status_cancelled_color' ),
                CustomerAppointment::STATUS_REJECTED => get_option( 'bookly_appointment_status_rejected_color' ),
            ) );
            $colors['mixed'] = get_option( 'bookly_appointment_status_mixed_color' );
        }
        foreach ( $appointments as $key => $appointment ) {
            $event_status = null;
            foreach ( $appointment['customers'] as $customer ) {
                if ( $coloring_mode == 'status' ) {
                    if ( $event_status === null ) {
                        $event_status = $customer['status'];
                    } elseif ( $event_status != $customer['status'] ) {
                        $event_status = 'mixed';
                    }
                }
            }

            switch ( $coloring_mode ) {
                case 'status';
                    $color = $colors[ $event_status ];
                    break;
                case 'staff':
                    $color = $appointment['staff_color'];
                    break;
                case 'service':
                default:
                    $color = $appointment['service_color'];
            }

            $appointments[ $key ] = array(
                'id' => $appointment['id'],
                'start' => $appointment['start_date'],
                'end' => $appointment['end_date'],
                'title' => $appointment['service_name'],
                'color' => $color,
            );
        }

        return array_values( $appointments );
    }
}