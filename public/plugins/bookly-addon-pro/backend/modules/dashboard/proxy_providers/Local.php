<?php
namespace BooklyPro\Backend\Modules\Dashboard\ProxyProviders;

use Bookly\Backend\Modules\Dashboard\Proxy;
use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

/**
 * Class Local
 * @package BooklyPro\Backend\Modules\Dashboard\ProxyProviders
 */
class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderAnalytics()
    {
        self::enqueueScripts( array(
            'module' => array( 'js/dashboard-pro.js' => array( 'bookly-backend-globals' ), ),
        ) );
        $datatables = BooklyLib\Utils\Tables::getSettings( 'analytics' );

        wp_localize_script( 'bookly-dashboard-pro.js', 'BooklyAnalyticsL10n', array(
            'zeroRecords' => __( 'No appointments for selected period.', 'bookly' ),
            'processing' => __( 'Processing...', 'bookly' ),
            'filter' => $datatables['analytics']['settings']['filter'],
        ) );

        $dropdown_data = array(
            'service' => BooklyLib\Utils\Common::getServiceDataForDropDown( 's.type = "simple"' ),
            'staff'   => Lib\ProxyProviders\Local::getStaffDataForDropDown()
        );

        self::renderTemplate( 'analytics', compact( 'dropdown_data' ) );
    }
}