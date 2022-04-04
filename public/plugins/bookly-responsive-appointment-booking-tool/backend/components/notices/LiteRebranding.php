<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class LiteRebranding
 * @package Bookly\Backend\Components\Notices
 */
class LiteRebranding extends Lib\Base\Component
{
    /**
     * Render subscribe notice.
     */
    public static function render()
    {
        if ( Lib\Utils\Common::isCurrentUserAdmin() &&
            get_user_meta( get_current_user_id(), 'bookly_show_lite_rebranding_notice', true ) ) {

            self::enqueueStyles( array(
                'alias' => array( 'bookly-backend-globals', ),
            ) );

            self::enqueueScripts( array(
                'module' => array( 'js/lite-rebranding.js' => array( 'bookly-backend-globals' ), ),
            ) );

            wp_localize_script( 'bookly-lite-rebranding.js', 'BooklyLiteL10n', array(
                'csrfToken' => Lib\Utils\Common::getCsrfToken(),
            ) );

            self::renderTemplate( 'lite_rebranding' );
        }
    }
}