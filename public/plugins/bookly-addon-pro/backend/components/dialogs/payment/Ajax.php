<?php
namespace BooklyPro\Backend\Components\Dialogs\Payment;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Payment;

/**
 * Class Ajax
 * @package BooklyPro\Backend\Modules\Payments
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => array( 'staff', 'supervisor' ) );
    }

    /**
     * Adjust payment.
     */
    public static function addPaymentAdjustment()
    {
        $payment_id = self::parameter( 'payment_id' );
        $reason     = self::parameter( 'reason' );
        $tax        = self::parameter( 'tax', 0 );
        $amount     = self::parameter( 'amount' );

        $payment = new Payment();
        $payment->load( $payment_id );

        if ( is_numeric( $amount ) ) {
            $details = json_decode( $payment->getDetails(), true );

            $details['adjustments'][] = compact( 'reason', 'amount', 'tax' );
            $payment
                ->setDetails( json_encode( $details ) )
                ->setTotal( $payment->getTotal() + $amount )
                ->setTax( $payment->getTax() + $tax )
                ->save();
        }

        wp_send_json_success();
    }
}