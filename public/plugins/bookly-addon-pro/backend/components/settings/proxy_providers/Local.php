<?php
namespace BooklyPro\Backend\Components\Settings\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Settings;

/**
 * Class Local
 * @package BooklyPro\Backend\Modules\Appointments\ProxyProviders
 */
class Local extends Settings\Proxy\Pro
{
    /**
     * @inheritDoc
     *
     * @param null $blog_id
     */
    public static function renderPurchaseCode( $blog_id = null )
    {
        if ( is_admin() ) {
            static::enqueueScripts( array(
                'module' => array( 'js/purchase_code.js' => array( 'bookly-backend-globals' ), ),
            ) );

            wp_localize_script( 'bookly-purchase_code.js', 'PurchaseCodeL10n', array(
                'confirmDetach' => sprintf( __( "Are you sure you want to dissociate this purchase code from %s?\n\nThis will also remove the entered purchase code from this site.", 'bookly' ), get_site_url() ),
            ) );
            $blog = $blog_id === null ? '' : ' data-blog_id="' . $blog_id . '"';
            /** @var BooklyLib\Base\Plugin $plugin_class */
            foreach ( apply_filters( 'bookly_plugins', array() ) as $plugin_class ) {
                if ( $plugin_class::getSlug() != BooklyLib\Plugin::getSlug() && ! $plugin_class::embedded() ) {
                    $purchase_code = $plugin_class::getPurchaseCode( $blog_id );
                    self::renderTemplate( 'purchase_code', compact( 'plugin_class', 'purchase_code', 'blog' ) );
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderDisplayFrontendCalendar()
    {
        Settings\Selects::renderSingle( 'bookly_cal_frontend_enabled', __( 'Display front-end calendar', 'bookly' ), __( 'If this option is enabled then customers will be able to view the availability of the selected staff in a front-end calendar', 'bookly' ) );
    }
}