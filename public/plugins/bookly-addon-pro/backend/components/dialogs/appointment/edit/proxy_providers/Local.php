<?php
namespace BooklyPro\Backend\Components\Dialogs\Appointment\Edit\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Appointment\Edit\Proxy;
use Bookly\Lib as BooklyLib;

/**
 * Class Local
 * @package BooklyPro\Backend\Components\Dialogs\Appointment\Edit\ProxyProviders
 */
class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function addCustomService( array $services )
    {
        $services[] = array(
            'id'        => null,
            'name'      => __( 'Custom', 'bookly' ),
            'units_min' => 1,
            'units_max' => 1,
            'duration'  => BooklyLib\Config::getTimeSlotLength(),
            'locations' => array(
                0 => array(
                    'capacity_min' => 1,
                    'capacity_max' => BooklyLib\Config::groupBookingActive() ? 9999 : 1,
                ),
            ),
        );

        return $services;
    }
}