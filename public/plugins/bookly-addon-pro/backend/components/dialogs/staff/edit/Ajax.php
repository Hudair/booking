<?php
namespace BooklyPro\Backend\Components\Dialogs\Staff\Edit;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Dialogs\Staff\Edit\Proxy;

/**
 * Class Ajax
 * @package BooklyPro\Backend\Components\Dialogs\Staff\Edit
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /** @var BooklyLib\Entities\Staff */
    protected static $staff;

    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        $permissions = get_option( 'bookly_gen_allow_staff_edit_profile' )
            ? array( '_default' => 'staff' )
            : array();
        if ( BooklyLib\Config::staffCabinetActive() ) {
            $permissions = array( '_default' => 'staff' );
        }

        return $permissions;
    }

    /**
     * Update staff advanced settings.
     */
    public static function updateStaffAdvanced()
    {
        $params = self::postParameters();
        self::$staff->setFields( $params );
        $data = array( 'alerts' => array( 'error' => array() ) );

        $data = Proxy\Shared::preUpdateStaffAdvanced( $data, self::$staff, $params );
        self::$staff->save();
        $data = Proxy\Shared::updateStaffAdvanced( $data, self::$staff, $params );

        wp_send_json_success( $data );
    }

    /**
     * Get staff advanced.
     */
    public static function getStaffAdvanced()
    {
        $data = Proxy\Shared::editStaffAdvanced(
            array( 'alert' => array( 'error' => array() ), 'tpl' => array() ),
            self::$staff
        );
        $html = self::renderTemplate( 'staff_advanced', self::$staff, $data['tpl'] );
        wp_send_json_success( compact( 'html' ) );
    }

    /**
     * Extend parent method to control access on staff member level.
     *
     * @param string $action
     * @return bool
     */
    protected static function hasAccess( $action )
    {
        if ( parent::hasAccess( $action ) ) {
            if ( ! BooklyLib\Utils\Common::isCurrentUserAdmin() ) {
                self::$staff = BooklyLib\Entities\Staff::query()->where( 'wp_user_id', get_current_user_id() )->findOne();
                if( ! self::$staff ) {
                    return false;
                } else switch ( $action ) {
                    case 'getStaffAdvanced':
                    case 'updateStaffAdvanced':
                        return self::$staff->getId() == self::parameter( 'staff_id' );
                    default:
                        return false;
                }
            } elseif ( $action === 'updateStaffAdvanced' ) {
                self::$staff = new BooklyLib\Entities\Staff();
                self::$staff->load( self::parameter( 'staff_id' ) );
            }

            return true;
        }

        return false;
    }
}