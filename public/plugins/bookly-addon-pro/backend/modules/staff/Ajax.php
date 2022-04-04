<?php
namespace BooklyPro\Backend\Modules\Staff;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Modules\Appointments\Page;
use Bookly\Backend\Modules\Staff\Proxy;
use BooklyPro\Backend\Modules\Staff\Forms;
use BooklyPro\Lib;

/**
 * Class Ajax
 * @package BooklyPro\Backend\Modules\Staff
 */
class Ajax extends BooklyLib\Base\Ajax
{
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
     * Add category.
     */
    public static function addStaffCategory()
    {
        $response = array();
        if ( ! empty ( $_POST ) && self::csrfTokenValid() ) {
            $form = new Forms\Category();
            $form->bind( self::postParameters() );
            if ( $category = $form->save() ) {
                $response = array( 'id' => $category->getId(), 'name' => $category->getName() );
            }
        }
        wp_send_json_success( $response );
    }

    /**
     * Delete category.
     */
    public static function deleteStaffCategory()
    {
        $category_id = self::parameter( 'id' );
        $category    = Lib\Entities\StaffCategory::find( $category_id );

        if ( $category ) {
            BooklyLib\Entities\Staff::query()
                ->update()
                ->set( 'category_id', null )
                ->where( 'category_id', $category_id )
                ->execute();

            $category->delete();
        }

        wp_send_json_success();
    }

    /**
     * Rename staff category
     */
    public static function renameStaffCategory()
    {
        $category_id = self::parameter( 'id' );
        $name        = self::parameter( 'name' );
        $category    = Lib\Entities\StaffCategory::find( $category_id );

        if ( $category ) {
            $category->setName( $name )->save();
        }

        wp_send_json_success();
    }

    /**
     * 'Safely' staff archiving (with confirmation)
     *
     * @request param 'archiving' with values [force, archive, verify]
     * @request param 'staff_id'
     */
    public static function archivingStaff()
    {
        $staff_id = self::parameter( 'staff_id' );
        if ( BooklyLib\Utils\Common::isCurrentUserAdmin()
            && $staff = BooklyLib\Entities\Staff::find( $staff_id )
        ) {
            /**
             * archiving values:
             *  force   - archiving staff
             *  archive - archiving staff if there aren't any appointments in the future
             *  verify || verify-and-confirm - check if staff are any appointments in the future
             */
            $archiving = self::parameter( 'archiving', 'archive' );
            if ( $archiving == 'force' ) {
                $staff->setVisibility( 'archive' )->save();
            } elseif ( $staff->getVisibility() != 'archive' ) {
                $appointment = BooklyLib\Entities\Appointment::query( 'a' )
                    ->select( 'MAX(a.start_date) AS start_date' )
                    ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                    ->where( 'a.staff_id', $staff_id )
                    ->whereGt( 'a.start_date', current_time( 'mysql' ) )
                    ->whereIn( 'ca.status', BooklyLib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                        BooklyLib\Entities\CustomerAppointment::STATUS_PENDING,
                        BooklyLib\Entities\CustomerAppointment::STATUS_APPROVED,
                    ) ) )
                    ->fetchRow();
                $filter_url = '';
                if ( $appointment['start_date'] ) {
                    $last_month = date_create( $appointment['start_date'] )->modify( 'last day of' )->format( 'Y-m-d' );
                    $filter_url = sprintf( '%s#staff=%d&appointment-date=%s-%s',
                        BooklyLib\Utils\Common::escAdminUrl( Page::pageSlug() ),
                        $staff_id,
                        date_create( current_time( 'mysql' ) )->format( 'Y-m-d' ),
                        $last_month );
                }
                $filter_url = Proxy\Shared::getAffectedAppointmentsFilter( $filter_url, array( $staff_id ) );
                if ( $filter_url ) {
                    // Show confirmation question.
                    wp_send_json_error( compact( 'filter_url' ) );
                } elseif ( $archiving == 'archive' ) {
                    $staff->setVisibility( 'archive' )->save();
                }
            }
        }

        wp_send_json_success();
    }
}