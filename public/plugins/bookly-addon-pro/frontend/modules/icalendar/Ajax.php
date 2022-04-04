<?php
namespace BooklyPro\Frontend\Modules\Icalendar;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Modules\Appointments\Page;
use Bookly\Backend\Modules\Staff\Proxy;
use Bookly\Lib\Utils\Common;
use BooklyPro\Backend\Modules\Staff\Forms;
use BooklyPro\Lib;

/**
 * Class Ajax
 *
 * @package BooklyPro\Frontend\Modules\Icalendar
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

    public static function staffIcalendar()
    {
        /** @var BooklyLib\Entities\Staff $staff */
        $staff = BooklyLib\Entities\Staff::query()->where( 'icalendar_token', self::parameter( 'token' ) )->findOne();

        $ics = new BooklyLib\Utils\Ics\Feed();

        if ( $staff && $staff->getICalendar() ) {
            $appointments = BooklyLib\Entities\Appointment::query()
                ->where( 'staff_id', $staff->getId() )
                ->whereGte( 'start_date', date_create()->modify( - $staff->getICalendarDaysBefore() . 'days' )->format( 'Y-m-d' ) )
                ->whereLte( 'end_date', date_create()->modify( $staff->getICalendarDaysAfter() . 'days' )->format( 'Y-m-d' ) )
                ->find();

            foreach ( $appointments as $appointment ) {
                if ( $appointment->getServiceId() === null ) {
                    $service_name = $appointment->getCustomServiceName();
                } else {
                    $service = BooklyLib\Entities\Service::find( $appointment->getServiceId() );
                    $service_name = $service->getTranslatedTitle();
                }
                $descriptions = array();
                foreach ( $appointment->getCustomerAppointments( true ) as $customer_appointment ) {
                    $descriptions[] = sprintf(
                        '%s: %s\n%s: %s\n%s: %s\n%s: %s\n',
                        __( 'Name', 'bookly' ),
                        $customer_appointment->customer->getFullName(),
                        __( 'Email', 'bookly' ),
                        $customer_appointment->customer->getEmail(),
                        __( 'Phone', 'bookly' ),
                        $customer_appointment->customer->getPhone(),
                        __( 'Status', 'bookly' ),
                        BooklyLib\Entities\CustomerAppointment::statusToString( $customer_appointment->getStatus() )
                    );
                }
                $ics->addEvent( $appointment->getStartDate(), $appointment->getEndDate(), $service_name, implode( '\n', $descriptions ), $appointment->getLocationId() );
            }
        }

        echo $ics->render();

        exit();
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        $excluded_actions = array(
            'staffIcalendar',
        );

        return in_array( $action, $excluded_actions ) || parent::csrfTokenValid( $action );
    }
}