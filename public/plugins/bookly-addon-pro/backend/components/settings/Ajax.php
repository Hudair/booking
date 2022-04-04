<?php
namespace BooklyPro\Backend\Components\Settings;

use Bookly\Lib as BooklyLib;
use BooklyPro\lib;

/**
 * Class Ajax
 * @package BooklyPro\Backend\Modules\Settings
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * Detach purchase code.
     */
    public static function detachPurchaseCode()
    {
        $option_name = self::parameter( 'name' );
        $blog_id     = self::parameter( 'blog_id' );

        /** @var BooklyLib\Base\Plugin $plugin_class */
        foreach ( apply_filters( 'bookly_plugins', array() ) as $plugin_class ) {
            if ( $plugin_class::getPurchaseCodeOption() == $option_name
                && Lib\API::detachPurchaseCode( $plugin_class, $blog_id )
            ) {
                $plugin_class::updatePurchaseCode( '', $blog_id );
                wp_send_json_success( array( 'alert' => array( 'success' => array( __( 'Settings saved.', 'bookly' ) ) ) ) );
            }
        }

        wp_send_json_error( array( 'alert' => array( 'error' => array( __( 'Error dissociating purchase code.', 'bookly' ) ) ) ) );
    }
}