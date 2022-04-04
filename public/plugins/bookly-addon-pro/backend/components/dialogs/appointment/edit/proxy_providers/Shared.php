<?php
namespace BooklyPro\Backend\Components\Dialogs\Appointment\Edit\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Appointment\Edit\Proxy\Shared as AppointmentEditProxy;

/**
 * Class Shared
 * @package BooklyPro\Backend\Components\Dialogs\Appointment\Edit\ProxyProviders
 */
class Shared extends AppointmentEditProxy
{
    /**
     * @inheritDoc
     */
    public static function prepareL10n( $l10n )
    {
        $plugins = apply_filters( 'bookly_plugins', array() );
        unset( $plugins['bookly-responsive-appointment-booking-tool'] );
        foreach ( array_keys( $plugins ) as $addon ) {
            $l10n['addons'][] = substr( $addon, 13 );
        }

        $l10n['l10n']['attach_payment'] = __( 'Attach payment', 'bookly' );
        $l10n['l10n']['custom_service_name']  = __( 'Custom service name', 'bookly' );
        $l10n['l10n']['custom_service_price'] = __( 'Custom service price', 'bookly' );
        $l10n['l10n']['online_meeting'] = __( 'Online meeting', 'bookly' );
        $l10n['l10n']['meeting_code'] = __( 'This link can be inserted into notifications with {online_meeting_url} code', 'bookly' );
        $l10n['l10n']['meeting_create'] = __( 'Save appointment to create a meeting', 'bookly' );
        $l10n['l10n']['copied'] = __( 'copied', 'bookly' );
        $l10n['l10n']['copy_to_clipboard'] = __( 'Copy to clipboard', 'bookly' );
        $l10n['l10n']['notices']['custom_service_name_required']  = __( 'Please enter a service name', 'bookly' );
        $l10n['l10n']['notices']['overflow_capacity'] = __( 'The number of customers should not be more than %d', 'bookly' );
        $l10n['l10n']['notices']['staff_reaches_working_time_limit'] = is_admin()
            ? __( 'Booking exceeds the working hours limit for staff member', 'bookly' )
            : __( 'Booking exceeds your working hours limit', 'bookly' );

        return $l10n;
    }
}