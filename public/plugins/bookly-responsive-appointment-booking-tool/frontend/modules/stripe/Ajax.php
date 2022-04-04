<?php
namespace Bookly\Frontend\Modules\Stripe;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Frontend\Modules\Stripe
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    public static function cloudStripeNotify()
    {
        if ( Lib\Cloud\API::getInstance()->account->productActive( 'stripe' ) ) {
            try {
                self::notify();
            } catch ( \Exception $e ) {
                status_header( 400 );
            }
        }
        exit;
    }

    /**
     * Retrieve event by notifying from Bookly Cloud
     *
     * @throws \Exception
     */
    private static function notify()
    {
        $event = Lib\Cloud\API::getInstance()->stripe->retrieveEvent( $_POST['event_id'] );
        switch ( $event['type'] ) {
            case 'checkout.session.completed':
                self::processCheckoutSessionCompleted( $event );
                break;
            case 'charge.refunded':
                self::processChargeRefunded( $event );
                break;
        }
    }

    /**
     * Process Stripe event checkout.session.completed
     *
     * @param array $data
     */
    private static function processCheckoutSessionCompleted( $data )
    {
        $stripe_amount = $data['amount'];
        $payment = new Lib\Entities\Payment();
        $payment->loadBy( array( 'id' => $data['metadata']['payment_id'], 'type' => Lib\Entities\Payment::TYPE_CLOUD_STRIPE ) );
        if ( $payment->getStatus() === Lib\Entities\Payment::STATUS_PENDING ) {
            if ( strtoupper( $data['currency'] ) == get_option( 'bookly_pmt_currency' ) ) {
                $amount = $payment->getPaid();
                if ( ! in_array( get_option( 'bookly_pmt_currency' ), array( 'BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'VND', 'VUV', 'XAF', 'XOF', 'XPF', ) ) ) {
                    // Zero-decimal currency
                    $amount = (int) ($amount * 100);
                }
                if ( $stripe_amount == $amount ) {
                    $payment->setStatus( Lib\Entities\Payment::STATUS_COMPLETED )->save();
                    if ( $order = Lib\DataHolders\Booking\Order::createFromPayment( $payment ) ) {
                        Lib\Notifications\Cart\Sender::send( $order );

                        foreach ( $order->getFlatItems() as $item ) {
                            if ( $item->getAppointment()->getGoogleEventId() !== null ) {
                                Lib\Proxy\Pro::syncGoogleCalendarEvent( $item->getAppointment() );
                            }
                            if ( $item->getAppointment()->getOutlookEventId() !== null ) {
                                Lib\Proxy\OutlookCalendar::syncEvent( $item->getAppointment() );
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Process Stripe charge.refunded
     *
     * @param array $data
     */
    private static function processChargeRefunded( $data )
    {
        $payment = new Lib\Entities\Payment();
        $loaded = $payment->loadBy( array(
            'type' => Lib\Entities\Payment::TYPE_CLOUD_STRIPE,
            'id'   => $data['metadata']['payment_id'],
        ) );
        if ( $loaded ) {
            /** @var Lib\Entities\CustomerAppointment $ca */
            foreach ( Lib\Entities\CustomerAppointment::query()->where( 'payment_id', $payment->getId() )->find() as $ca ) {
                Lib\Utils\Log::deleteEntity( $ca, __METHOD__ );
                $ca->deleteCascade();
            }
            $payment->delete();
        }
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        return $action === 'cloudStripeNotify' || parent::csrfTokenValid( $action );
    }
}