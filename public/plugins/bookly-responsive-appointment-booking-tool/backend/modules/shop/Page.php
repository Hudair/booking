<?php
namespace Bookly\Backend\Modules\Shop;

use Bookly\Lib;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Shop
 */
class Page extends Lib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'module' => array( 'css/shop.css' => array( 'bookly-backend-globals' ) ),
        ) );

        self::enqueueScripts( array(
            'backend' => array( 'js/select2.min.js' => array( 'bookly-backend-globals' ), ),
            'module' => array( 'js/shop.js' => array( 'bookly-select2.min.js' ) ),
        ) );

        wp_localize_script( 'bookly-shop.js', 'BooklyL10n', array(
            'csrf_token' => Lib\Utils\Common::getCsrfToken(),
        ) );

        $has_new_items = Lib\Entities\Shop::query()
            ->whereGt( 'published', date_create( 'now' )->modify( '-2 weeks' )->format( 'Y-m-d H:i:s' ) )
            ->where( 'seen', 0, 'OR' )
            ->count();

        self::renderTemplate( 'index', compact( 'has_new_items' ) );
    }

    /**
     * @return int
     */
    public static function getNotSeenCount()
    {
        return Lib\Entities\Shop::query()
            ->where( 'seen', 0 )
            ->count();
    }

    /**
     * Show 'Addons' submenu with counter inside Bookly main menu
     */
    public static function addBooklyMenuItem()
    {
        $title = __( 'Addons', 'bookly' );
        $count = self::getNotSeenCount();
        if ( $count ) {
            add_submenu_page( 'bookly-menu', $title, sprintf( '%s <span class="update-plugins count-%d"><span class="update-count">%d</span></span>', $title, $count, $count ), Lib\Utils\Common::getRequiredCapability(),
                self::pageSlug(), function () { Page::render(); } );
        } else {
            add_submenu_page( 'bookly-menu', $title, $title, Lib\Utils\Common::getRequiredCapability(),
                self::pageSlug(), function () { Page::render(); } );
        }
    }
}