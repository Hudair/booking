<?php
namespace BooklyPro\Lib\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\CartInfo;
use Bookly\Lib\Entities\Appointment;
use Bookly\Lib\Entities\Notification;
use Bookly\Lib\Entities\Payment;
use Bookly\Lib\Entities\Service;
use Bookly\Lib\Slots\DatePoint;
use Bookly\Lib\Utils\Common;
use BooklyPro\Backend\Components\License;
use BooklyPro\Frontend\Modules\Paypal;
use BooklyPro\Lib\Config;
use BooklyPro\Lib\Zoom;

/**
 * Class Shared
 * @package BooklyPro\Lib\ProxyProviders
 */
class Shared extends BooklyLib\Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function applyGateway( CartInfo $cart_info, $gateway )
    {
        if ( $gateway === Payment::TYPE_PAYPAL && BooklyLib\Config::paypalEnabled() ) {
            $cart_info->setGateway( $gateway );
        }

        return $cart_info;
    }

    /**
     * @inheritDoc
     */
    public static function doDailyRoutine()
    {
        // Grace routine.
        $remaining_days = Config::graceRemainingDays();
        if ( $remaining_days !== false ) {
            $today               = (int) ( current_time( 'timestamp' ) / DAY_IN_SECONDS );
            $grace_notifications = get_option( 'bookly_grace_notifications' );
            if ( $today != $grace_notifications['sent'] ) {
                $admin_emails = Common::getAdminEmails();
                if ( ! empty ( $admin_emails ) ) {
                    $grace_notifications['sent'] = $today;
                    if ( $remaining_days === 0 && ( $grace_notifications['bookly'] != 1 ) ) {
                        $subject = __( 'Please verify your Bookly Pro license', 'bookly' );
                        $message = __( 'Bookly Pro will need to verify your license to restore access to your bookings. Please enter the purchase code in the administrative panel.', 'bookly' );
                        foreach ( $admin_emails as $email ) {
                            if ( wp_mail( $email, $subject, $message ) ) {
                                $grace_notifications['bookly'] = 1;
                                update_option( 'bookly_grace_notifications', $grace_notifications );
                            }
                        }
                    } else if ( in_array( $remaining_days, array( 13, 7, 1 ) ) ) {
                        $days_text = sprintf( _n( '%d day', '%d days', $remaining_days, 'bookly' ), $remaining_days );
                        $replace   = array( '{days}' => $days_text );
                        $subject   = __( 'Please verify your Bookly Pro license', 'bookly' );
                        $message   = strtr( __( 'Please verify Bookly Pro license in the administrative panel. If you do not verify the license within {days}, access to your bookings will be disabled.', 'bookly' ), $replace );
                        foreach ( $admin_emails as $email ) {
                            if ( wp_mail( $email, $subject, $message ) ) {
                                update_option( 'bookly_grace_notifications', $grace_notifications );
                            }
                        }
                    }
                }
            }
        }

        if ( get_option( 'bookly_pr_show_time' ) < time() ) {
            update_option( 'bookly_pr_show_time', time() + 7776000 );
            if ( get_option( 'bookly_pro_envato_purchase_code' ) == '' ) {
                foreach ( get_users( array( 'role' => 'administrator' ) ) as $admin ) {
                    update_user_meta( $admin->ID, 'bookly_show_purchase_reminder', '1' );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function handleRequestAction( $action )
    {
        switch ( $action ) {
            // PayPal Express Checkout.
            case 'paypal-ec-init':
                Paypal\Controller::ecInit();
                break;
            case 'paypal-ec-return':
                Paypal\Controller::ecReturn();
                break;
            case 'paypal-ec-cancel':
                Paypal\Controller::ecCancel();
                break;
            case 'paypal-ec-error':
                Paypal\Controller::ecError();
                break;
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderAdminNotices( $bookly_page )
    {
        License\Components::renderLicenseRequired( $bookly_page );
        License\Components::renderLicenseNotice( $bookly_page );
        License\Components::renderPurchaseReminder( $bookly_page );
    }

    /**
     * @inheritDoc
     */
    public static function showPaymentSpecificPrices( $show )
    {
        if ( ! $show && BooklyLib\Config::paypalEnabled() ) {
            return (float) get_option( 'bookly_paypal_increase' ) != 0 || (float) get_option( 'bookly_paypal_addition' ) != 0;
        }

        return $show;
    }

    /**
     * @inheritDoc
     */
    public static function prepareCaSeStQuery( BooklyLib\Query $query )
    {
        if ( ! BooklyLib\Config::customerGroupsActive() ) {
            $query->where( 's.visibility', BooklyLib\Entities\Service::VISIBILITY_PUBLIC );
        }

        return $query;
    }

    /**
     * @inheritDoc
     */
    public static function prepareStaffServiceQuery( BooklyLib\Query $query )
    {
        $query
            ->addSelect( 'spo.position' )
            ->leftJoin( 'StaffPreferenceOrder', 'spo', 'spo.service_id = ss.service_id AND spo.staff_id = ss.staff_id', '\BooklyPro\Lib\Entities' );

        return $query;
    }

    /**
     * @inheritDoc
     */
    public static function prepareStatement( $value, $statement, $table )
    {
        $tables = array( 'Service', 'Staff' );
        $key    = $table . '-' . $statement;
        if ( in_array( $table, $tables ) ) {
            if ( ! self::hasInCache( $key ) ) {
                preg_match( '/(?:(\w+)\()?\W*(?:(\w+)\.(\w+)|(\w+))/', $statement, $match );

                $count = count( $match );
                if ( $count == 4 ) {
                    $field = $match[3];
                } elseif ( $count == 5 ) {
                    $field = $match[4];
                }

                switch ( $field ) {
                    case 'category_id':
                    case 'padding_left':
                    case 'padding_right':
                    case 'staff_preference':
                    case 'staff_preference_settings':
                        self::putInCache( $key, $statement );
                        break;
                }
            }
        } else {
            self::putInCache( $key, $value );
        }

        return self::getFromCache( $key );
    }

    /**
     * @inheritDoc
     */
    public static function prepareNotificationTypes( array $types, $gateway )
    {
        if ( $gateway == 'email' ) {
            $types[] = Notification::TYPE_APPOINTMENT_REMINDER;
            $types[] = Notification::TYPE_LAST_CUSTOMER_APPOINTMENT;
            $types[] = Notification::TYPE_STAFF_DAY_AGENDA;
        }
        $types[] = Notification::TYPE_NEW_BOOKING_COMBINED;
        $types[] = Notification::TYPE_CUSTOMER_BIRTHDAY;
        $types[] = Notification::TYPE_CUSTOMER_NEW_WP_USER;
        $types[] = Notification::TYPE_STAFF_NEW_WP_USER;

        return $types;
    }

    /**
     * @inheritDoc
     */
    public static function prepareTableColumns( $columns, $table )
    {
        switch ( $table ) {
            case BooklyLib\Utils\Tables::APPOINTMENTS:
                $columns['customer_address' ] = esc_attr__( 'Customer address', 'bookly' );
                $columns['customer_birthday' ] = esc_attr__( 'Customer birthday', 'bookly' );
                $columns['online_meeting'] = esc_attr__( 'Online meeting', 'bookly' );
                break;

            case BooklyLib\Utils\Tables::CUSTOMERS:
                $columns['address']  = esc_attr( BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_address' ) );
                $columns['facebook'] = 'Facebook';
                break;

            case BooklyLib\Utils\Tables::SERVICES:
                $columns['online_meetings'] = esc_attr__( 'Online meetings', 'bookly' );
                break;

            case BooklyLib\Utils\Tables::STAFF_MEMBERS:
                $columns['category_name'] = esc_attr__( 'Category', 'bookly' );
                break;
        }

        return $columns;
    }

    /**
     * @inheritDoc
     */
    public static function buildOnlineMeetingUrl( $default, Appointment $appointment)
    {
        if ( $appointment->getOnlineMeetingProvider() == 'zoom' ) {
            return 'https://zoom.us/j/' . $appointment->getOnlineMeetingId();
        } elseif ( $appointment->getOnlineMeetingProvider() == 'google_meet' || $appointment->getOnlineMeetingProvider() == 'jitsi' ) {
            return $appointment->getOnlineMeetingId();
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public static function buildOnlineMeetingPassword( $default, Appointment $appointment )
    {
        if ( $appointment->getOnlineMeetingProvider() == 'zoom' ) {
            $options = json_decode( $appointment->getOnlineMeetingData() ?: '{}', true );

            return isset( $options['password'] ) ? $options['password'] : $default;
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public static function buildOnlineMeetingStartUrl( $default, Appointment $appointment )
    {
        if ( $appointment->getOnlineMeetingProvider() == 'zoom' ) {
            $options = json_decode( $appointment->getOnlineMeetingData() ?: '{}', true );

            return isset( $options['start_url'] ) ? $options['start_url'] : self::buildOnlineMeetingUrl( $default, $appointment );
        } elseif ( $appointment->getOnlineMeetingProvider() == 'google_meet' || $appointment->getOnlineMeetingProvider() == 'jitsi' ) {
            return $appointment->getOnlineMeetingId();
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public static function buildOnlineMeetingJoinUrl( $default, Appointment $appointment )
    {
        if ( $appointment->getOnlineMeetingProvider() == 'zoom' ) {
            $options = json_decode( $appointment->getOnlineMeetingData() ?: '{}', true );

            return isset( $options['join_url'] ) ? $options['join_url'] : self::buildOnlineMeetingUrl( $default, $appointment );
        } elseif ( $appointment->getOnlineMeetingProvider() == 'google_meet' || $appointment->getOnlineMeetingProvider() == 'jitsi' ) {
            return $appointment->getOnlineMeetingId();
        }

        return $default;
    }

    /**
     * @inheritDoc
     */
    public static function syncOnlineMeeting( array $errors, Appointment $appointment, $service )
    {
        // Zoom.
        if ( ( $appointment->getOnlineMeetingProvider() == 'zoom' || $appointment->getOnlineMeetingProvider() == null && $service && $service->getOnlineMeetings() == 'zoom' ) && $appointment->getStartDate() !== null ) {
            $start = DatePoint::fromStr( $appointment->getStartDate() );
            $end = DatePoint::fromStr( $appointment->getEndDate() );
            $duration = $end->diff( $start ) + $appointment->getExtrasDuration();

            $zoom = new Zoom\Meetings( BooklyLib\Entities\Staff::find( $appointment->getStaffId() ) );
            $data = array(
                'topic' => $service->getTitle(),
                'start_time' => $start->toTz( 'UTC' )->format( 'Y-m-d\TH:i:s\Z' ),
                'duration' => (int) ( $duration / 60 ),  // duration in minutes
            );
            if ( $appointment->getOnlineMeetingId() != '' ) {
                $res = $zoom->update( $appointment->getOnlineMeetingId(), $data );
            } else {
                $res = $zoom->create( $data );
                if ( $res ) {
                    $appointment
                        ->setOnlineMeetingProvider( 'zoom' )
                        ->setOnlineMeetingId( $res['id'] )
                        ->setOnlineMeetingData( json_encode( $res ) )
                        ->save();
                }
            }

            if ( ! $res ) {
                $errors = array_merge( $errors, array_map( function ( $e ) { return 'Zoom: ' . $e; }, $zoom->errors() ) );
            }
        } elseif ( $appointment->getOnlineMeetingProvider() == null && $service && $service->getOnlineMeetings() == 'jitsi' ) {
            $token = md5( uniqid( time(), true ) );
            $token = sprintf(
                'https://meet.jit.si/%s-%s-%s',
                substr( $token, 0, 3 ),
                substr( $token, 3, 4 ),
                substr( $token, 7, 3 )
            );
            $appointment
                ->setOnlineMeetingProvider( 'jitsi' )
                ->setOnlineMeetingId( $token )
                ->save();
        }

        return $errors;
    }

    /**
     * @inheritDoc
     */
    public static function prepareAppointmentCodes( $codes, $appointment )
    {
        $codes['online_meeting_url'] = BooklyLib\Proxy\Shared::buildOnlineMeetingUrl( '', $appointment );
        $codes['online_meeting_password'] = BooklyLib\Proxy\Shared::buildOnlineMeetingPassword( '', $appointment );
        $codes['online_meeting_start_url'] = BooklyLib\Proxy\Shared::buildOnlineMeetingStartUrl( '', $appointment );
        $codes['online_meeting_join_url'] = BooklyLib\Proxy\Shared::buildOnlineMeetingJoinUrl( '', $appointment );
        $codes['on_waiting_list'] = BooklyLib\Config::waitingListActive()
            ? BooklyLib\Entities\CustomerAppointment::query( 'ca' )
                ->where( 'ca.appointment_id', $appointment->getId() )
                ->where( 'status', BooklyLib\Entities\CustomerAppointment::STATUS_WAITLISTED )
                ->count()
            : 0;

        return $codes;
    }

    /**
     * @inheritDoc
     */
    public static function prepareCustomerAppointmentCodes( $codes, $customer_appointment, $format )
    {
        $customer = BooklyLib\Entities\Customer::find( $customer_appointment->getCustomerId() );
        $codes['status'] = BooklyLib\Entities\CustomerAppointment::statusToString( $customer_appointment->getStatus() );
        $codes['client_address'] = $customer->getAddress();
        $codes['client_birthday'] = $customer->getBirthday() ? BooklyLib\Utils\DateTime::formatDate( $customer->getBirthday() ) : '';

        return $codes;
    }

    /**
     * @inheritDoc
     */
    public static function prepareL10nGlobal( array $obj )
    {
        $plugins = apply_filters( 'bookly_plugins', array() );
        unset ( $plugins['bookly-responsive-appointment-booking-tool'] );
        foreach ( array_keys( $plugins ) as $addon ) {
            $obj['addons'][] = substr( $addon, 13 );
        }

        return $obj;
    }

    /**
     * @inheritDoc
     */
    public static function prepareNotificationTitles( array $titles )
    {
        $titles['new_booking_combined'] = __( 'New booking combined notification', 'bookly' );
        $titles['customer_new_wp_user'] = __( 'New customer\'s WordPress user login details', 'bookly' );
        $titles['staff_new_wp_user'] = __( 'New staff\'s WordPress user login details', 'bookly' );

        return $titles;
    }
}