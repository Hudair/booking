<?php
namespace BooklyPro\Lib\Notifications\Test;

use Bookly\Lib\Entities\Customer;
use Bookly\Lib\Entities\CustomerAppointment;
use Bookly\Lib\Entities\Appointment;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Entities\Service;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Notifications\Base;
use BooklyPro\Lib\Notifications\Assets;
use Bookly\Lib\Notifications\Assets\Item\Attachments;
use Bookly\Lib\DataHolders\Booking\Order;

/**
 * Class Sender
 * @package Bookly\Lib\Notifications\Instant
 */
abstract class Sender extends Base\Sender
{
    public static function send( $to_email, Notification $notification, $codes, $attachments, $reply_to, $send_as, $from )
    {
        $customer = new Customer();
        if ( $notification->getType() == Notification::TYPE_CUSTOMER_NEW_WP_USER
            || $notification->getType() == Notification::TYPE_NEW_BOOKING_COMBINED ) {
            $customer
                ->setCountry( 'Country' )
                ->setState( 'State' )
                ->setPostcode( '12345' )
                ->setCity( 'City' )
                ->setFullName( 'First_name Last_name' )
                ->setFirstName( 'First_name' )
                ->setLastName( 'Last_name' )
                ->setPhone( '+123456789' )
                ->setEmail( 'email@test.test' )
                ->setAdditionalAddress( '2a' )
                ->setBirthday( '2000-01-01' )
                ->setNotes( 'Note' )
                ->setStreet( 'Street' )
                ->setStreetNumber( '12' );
        }

        switch ( $notification->getType() ) {
            case Notification::TYPE_CUSTOMER_NEW_WP_USER:
                if ( $notification->getToCustomer() ) {
                    $username = 'wp_user_name';
                    $password = 'password';
                    $codes = new Assets\NewWpUser\Client\Codes( $customer, $username, $password );
                    static::_sendEmailTo(
                        self::RECIPIENT_CLIENT,
                        $to_email,
                        $notification,
                        $codes,
                        $attachments,
                        $reply_to,
                        $send_as,
                        $from
                    );
                }
                break;
            case Notification::TYPE_STAFF_NEW_WP_USER:
                if ( $notification->getToStaff() ) {
                    $username = 'staff_user_name';
                    $password = 'password';
                    $staff = new Staff();
                    $staff
                        ->setFullName( 'First_name Last_name' )
                        ->setEmail( 'staff.email@test.test' )
                        ->setInfo( 'Staff info text' )
                        ->setPhone( '+345678910' );
                    $codes = new Assets\NewWpUser\Staff\Codes( $staff, $username, $password );
                    static::_sendEmailTo(
                        self::RECIPIENT_STAFF,
                        $to_email,
                        $notification,
                        $codes,
                        $attachments,
                        $reply_to,
                        $send_as,
                        $from
                    );
                }
                break;
            case Notification::TYPE_NEW_BOOKING_COMBINED:
                if ( $notification->getToCustomer() || $notification->getToCustom() ) {
                    $order = new Order( $customer );
                    /** @var Service $service */
                    $service = Service::query()->findOne();
                    $service->setTitle( 'Service Name' );
                    /** @var Staff $staff */
                    $staff = Staff::query()->findOne();
                    $staff->setFullName( 'Staff Name' );
                    $appointment = new Appointment();
                    $start = date_create()->modify( '-7 days' )->setTime( 8, 0, 0 );
                    $appointment
                        ->setService( $service )
                        ->setInternalNote( 'Internal note' )
                        ->setCreatedAt( $start->format( 'Y-m-d H:i:s' ) )
                        ->setCreatedFrom( 'backend' )
                        ->setStaff( $staff )
                        ->setService( $service )
                        ->setStartDate( $start->format( 'Y-m-d H:i:s' ) )
                        ->setEndDate( $start->modify( $service->getDuration() . ' sec' )->format( 'Y-m-d H:i:s' ) );
                    $ca = new CustomerAppointment();
                    $ca->setNotes( 'Notes' )
                        ->setNumberOfPersons( '1' )
                        ->setStatus( CustomerAppointment::STATUS_APPROVED )
                        ->setToken( 'token' )
                        ->setAppointment( $appointment );

                    $simple1 = \Bookly\Lib\DataHolders\Booking\Simple::create( $ca );
                    $simple1
                        ->setService( $service )
                        ->setStaff( $staff )
                        ->setAppointment( $appointment )
                        ->setStatus( CustomerAppointment::STATUS_APPROVED );


                    $service2 = clone $service;
                    $service2->setTitle( 'Another Service' );
                    $staff2 = clone $staff;
                    $staff2->setFullName( 'Another Staff' );
                    $appointment2 = clone $appointment;
                    $appointment2
                        ->setStartDate( $start->modify( '+1 day' )->format( 'Y-m-d H:i:s' ) )
                        ->setEndDate( $start->modify( $service2->getDuration() . ' sec' )->format( 'Y-m-d H:i:s' ) );

                    $ca2 = new CustomerAppointment();
                    $ca2->setNotes( 'Another Notes' )
                       ->setNumberOfPersons( '1' )
                       ->setStatus( CustomerAppointment::STATUS_PENDING )
                       ->setToken( 'token2' )
                       ->setAppointment( $appointment2 );

                    $simple2 = \Bookly\Lib\DataHolders\Booking\Simple::create( $ca2 );
                    $simple2
                        ->setService( $service2 )
                        ->setStaff( $staff2 )
                        ->setAppointment( $appointment2 )
                        ->setStatus( CustomerAppointment::STATUS_PENDING );

                    $order->addItem( 0, $simple1 );
                    $order->addItem( 1, $simple2 );
                    $codes = new Assets\Combined\Codes( $order );
                    $attachments = new Attachments( $codes );

                    if ( $notification->getToCustomer() ) {
                        static::_sendEmailTo(
                            self::RECIPIENT_CLIENT,
                            $to_email,
                            $notification,
                            $codes,
                            $attachments,
                            $reply_to,
                            $send_as,
                            $from
                        );
                    }
                    if( $notification->getToCustom() ) {
                        foreach ( array_map( 'trim', array_filter( explode( "\n", $notification->getCustomRecipients() ), 'trim' ) ) as $email ) {
                            static::_sendEmailTo(
                                self::RECIPIENT_ADMINS,
                                $email,
                                $notification,
                                $codes,
                                $attachments,
                                $reply_to,
                                $send_as,
                                $from
                            );
                        }
                    }
                }
                break;
        }
    }
}