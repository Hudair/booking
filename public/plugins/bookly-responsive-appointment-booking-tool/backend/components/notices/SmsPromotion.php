<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class SmsPromotion
 * @package Bookly\Backend\Components\Notices
 */
class SmsPromotion extends Lib\Base\Component
{
    /**
     * Render collect stats notice.
     */
    public static function render()
    {
        if ( Lib\Utils\Common::isCurrentUserAdmin() && isset ( $_REQUEST['page'] ) && ( strncmp( $_REQUEST['page'], 'bookly-cloud', 12 ) === 0 ) ) {
            $promotion = Lib\Cloud\API::getInstance()->general->getPromotionForNotice( $type );
            if ( $promotion ) {
                self::enqueueStyles( array(
                    'alias' => array( 'bookly-backend-globals', ),
                ) );
                self::enqueueScripts( array(
                    'module' => array( 'js/sms-promotion.js' => array( 'bookly-backend-globals' ), ),
                ) );

                wp_localize_script( 'bookly-sms-promotion.js', 'BooklySmsPromotionL10n', array(
                    'csrfToken' => Lib\Utils\Common::getCsrfToken(),
                ) );

                self::renderTemplate( 'sms_promotion', compact( 'type', 'promotion' ) );
            }
        }
    }
}