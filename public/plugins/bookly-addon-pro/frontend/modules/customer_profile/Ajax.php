<?php
namespace BooklyPro\Frontend\Modules\CustomerProfile;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

/**
 * Class Ajax
 * @package BooklyPro\Frontend\Modules\CustomerProfile
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /** @var BooklyLib\Entities\Customer */
    protected static $customer;

    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'customer' );
    }

    /**
     * Get past appointments.
     */
    public static function getPastAppointments()
    {
        $past = self::$customer->getPastAppointments( self::parameter( 'page' ), 30 );
        $appointments  = Lib\Utils\Common::translateAppointments( $past['appointments'] );
        $custom_fields = self::parameter( 'custom_fields' ) ? explode( ',', self::parameter( 'custom_fields' ) ) : array();
        $columns       = (array) self::parameter( 'columns' );
        $with_cancel   = in_array( 'cancel', $columns );
        $html = self::renderTemplate( '_rows', compact( 'appointments', 'columns', 'custom_fields', 'with_cancel' ), false );
        wp_send_json_success( array( 'html' => $html, 'more' => $past['more'] ) );
    }

    /**
     * @inheritDoc
     */
    protected static function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            self::$customer = BooklyLib\Entities\Customer::query()->where( 'wp_user_id', get_current_user_id() )->findOne();

            return self::$customer->isLoaded();
        }

        return false;
    }
}