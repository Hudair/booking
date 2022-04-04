<?php
namespace BooklyPro\Backend\Modules\Staff\ProxyProviders;

use Bookly\Backend\Modules\Staff\Proxy;
use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Staff;
use BooklyPro\Lib\Config;
use BooklyPro\Lib\Google;

/**
 * Class Shared
 * @package BooklyPro\Backend\Modules\Staff\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function renderStaffPage( $params )
    {
        // Check if this request is the request after google auth, set the token-data to the staff.
        if ( isset ( $params['code'] ) ) {
            $google = new Google\Client();
            $token  = $google->exchangeCodeForAccessToken( $params[ 'code'] );

            if ( $token ) {
                $staff_id = (int) base64_decode( strtr( $params['state'], '-_,', '+/=' ) );
                $staff = new Staff();
                $staff->load( $staff_id );
                $staff
                    ->setGoogleData( json_encode( array(
                        'token'    => $token,
                        'calendar' => array( 'id' => null, 'sync_token' => null ),
                        'channel'  => array( 'id' => null, 'resource_id' => null, 'expiration' => null ),
                    ) ) )
                    ->save()
                ;

                exit ( sprintf( '<script>location.href="%s";</script>', admin_url( 'admin.php?page=' . self::pageSlug() . '&tab=advanced&staff_id=' . $staff_id ) ) );
            } else {
                BooklyLib\Session::set( 'staff_google_auth_error', json_encode( $google->getErrors() ) );
            }
        }

        return $params;
    }

    /**
     * @inheritDoc
     */
    public static function searchStaff( array $fields, array $columns, BooklyLib\Query $query )
    {
        $query->leftJoin( 'StaffCategory', 'sc', 'sc.id = s.category_id', '\BooklyPro\Lib\Entities' );

        foreach ( $columns as $column ) {
            if ( $column['data'] == 'category_name' ) {
                $fields[] = 'sc.name';
            }
        }

        return $fields;
    }

    /**
     * @inheritDoc
     */
    public static function prepareGetStaffQuery( $query )
    {
        $query
            ->addSelect( 'sc.name as category_name' )
            ->leftJoin( 'StaffCategory', 'sc', 'sc.id = s.category_id', '\BooklyPro\Lib\Entities' );
    }
}