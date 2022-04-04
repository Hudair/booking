<?php
namespace BooklyPro\Backend\Components\Dialogs\Appointment\AttachPayment;

use Bookly\Lib as BooklyLib;

/**
 * Class Dialog
 * @package BooklyPro\Backend\Components\Dialogs\Appointment\AttachPayment
 */
class Dialog extends BooklyLib\Base\Component
{
    public static function render()
    {
        self::enqueueScripts( array(
            'module' => array( 'js/attach_payment.js' => array( 'bookly-backend-globals' ), ),
        ) );

        wp_localize_script( 'bookly-attach_payment.js', 'BooklyL10nAttachPaymentDialog', array(
            'csrf_token' => BooklyLib\Utils\Common::getCsrfToken(),
            'taxes_included' => get_option( 'bookly_taxes_in_price' ) == 'included',
            'l10n' => array(
                'attach_payment' => __( 'Attach payment', 'bookly' ),
                'create_payment' => __( 'Create payment', 'bookly' ),
                'search_payment' => __( 'Search payment', 'bookly' ),
                'total_price' => __( 'Total price', 'bookly' ),
                'payment_id' => __( 'Payment ID', 'bookly' ),
                'tax' => __( 'Tax', 'bookly' ), // @todo Вынести в аддон Taxes
                'apply' => __( 'Apply', 'bookly' ),
                'cancel' => __( 'Cancel', 'bookly' )
            )
        ));

        static::renderTemplate( 'attach_payment' );
    }
}