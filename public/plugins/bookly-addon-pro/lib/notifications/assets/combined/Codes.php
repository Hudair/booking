<?php
namespace BooklyPro\Lib\Notifications\Assets\Combined;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Config;
use Bookly\Lib\DataHolders\Booking\Order;
use Bookly\Lib\Notifications\Assets\Item;
use Bookly\Lib\Proxy;
use Bookly\Lib\Utils;

/**
 * Class Codes
 * @package BooklyPro\Lib\Notifications\Assets\Combined
 */
class Codes extends Item\Codes
{
    public $cart_info;
    public $order_token;

    /**
     * @inheritDoc
     */
    public function __construct( Order $order )
    {
        parent::__construct( $order );

        $this->cart_info = array();

        $total = 0.0;

        $first = current( $order->getItems() );
        $this->prepareForItem( $first, 'client' );

        foreach ( $order->getItems() as $item ) {
            $sub_items = array();
            if ( $item->isSeries() ) {
                $sub_items = $item->getItems();
                if ( get_option( 'bookly_recurring_appointments_payment' ) === 'first' ) {
                    array_splice( $sub_items, 1 );
                }
            } else {
                $sub_items[] = $item;
            }
            foreach ( $sub_items as $sub_item ) {
                if ( $this->order_token === null ) {
                    if ( $order_id = $sub_item->getCA()->getOrderId() ) {
                        $order_entity = BooklyLib\Entities\Order::find( $order_id );
                        $this->order_token = $order_entity->getToken();
                    }
                }
                // Skip wait listed  appointments.
                if ( $sub_item->getCA()->getLastStatus() !== 'waitlisted' ) {
                    // Sub-item price.
                    $price = $sub_item->getTotalPrice();

                    $deposit_price = Config::depositPaymentsActive()
                        ? BooklyLib\Proxy\DepositPayments::prepareAmount( $price, $sub_item->getDeposit(), $sub_item->getCA()->getNumberOfPersons() )
                        : 0;
                    $location = Config::locationsActive() && $sub_item->getAppointment()->getLocationId()
                        ? BooklyLib\Proxy\Locations::findById( $sub_item->getAppointment()->getLocationId() )
                        : null;

                    // Prepare data for {cart_info} || {cart_info_c}.
                    $this->cart_info[] = array(
                        'appointment_price' => $price,
                        'appointment_start' => $this->applyItemTz( $sub_item->getAppointment()->getStartDate(), $sub_item ),
                        'appointment_end'   => $this->applyItemTz( $sub_item->getAppointment()->getEndDate(), $sub_item ),
                        'cancel_url'        => admin_url( 'admin-ajax.php?action=bookly_cancel_appointment&token=' . $sub_item->getCA()->getToken() ),
                        'service_name'      => $sub_item->getService()->getTranslatedTitle(),
                        'staff_name'        => $sub_item->getStaff()->getTranslatedName(),
                        'extras'            => (array) BooklyLib\Proxy\ServiceExtras::getInfo( $sub_item->getExtras(), true ),
                        'location'          => $location ? $location->getTranslatedName() : '',
                        'tax'               => Config::taxesActive() ? $sub_item->getTax() : null,
                        'deposit'           => Config::depositPaymentsActive()
                            ? BooklyLib\Proxy\DepositPayments::formatDeposit( $deposit_price, $sub_item->getDeposit() )
                            : null,
                        'appointment_start_info' => $sub_item->getService()->getDuration() < DAY_IN_SECONDS
                            ? null
                            : $sub_item->getService()->getStartTimeInfo(),
                        'item'              => $sub_item
                    );

                    // Total price.
                    $total += $price;
                }
            }
        }

        if ( ! $order->hasPayment() ) {
            $this->total_price = $total;
        }
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );

        $cart_info_c = $cart_info = '';
        $appointments = array();

        // Cart info.
        $cart_info_data = $this->cart_info;
        if ( ! empty ( $cart_info_data ) ) {
            $cart_columns = get_option( 'bookly_cart_show_columns', array() );
            if ( empty( $cart_columns ) ) {
                $cart_columns = array(
                    'service'  => array( 'show' => '1', ),
                    'date'     => array( 'show' => '1', ),
                    'time'     => array( 'show' => '1', ),
                    'employee' => array( 'show' => '1', ),
                    'price'    => array( 'show' => '1', ),
                    'deposit'  => array( 'show' => (int) Config::depositPaymentsActive() ),
                    'tax'      => array( 'show' => (int) Config::taxesActive(), ),
                );
            }
            if ( ! Proxy\Taxes::showTaxColumn() ) {
                unset( $cart_columns['tax'] );
            }
            if ( ! Config::depositPaymentsActive() ) {
                unset( $cart_columns['deposit'] );
            }
            $ths = array();
            foreach ( $cart_columns as $column => $attr ) {
                if ( $attr['show'] ) {
                    switch ( $column ) {
                        case 'service':
                            $ths[] = Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' );
                            break;
                        case 'date':
                            $ths[] = __( 'Date', 'bookly' );
                            break;
                        case 'time':
                            $ths[] = __( 'Time', 'bookly' );
                            break;
                        case 'tax':
                            $ths[] = __( 'Tax', 'bookly' );
                            break;
                        case 'employee':
                            $ths[] = Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' );
                            break;
                        case 'price':
                            $ths[] = __( 'Price', 'bookly' );
                            break;
                        case 'deposit':
                            $ths[] = __( 'Deposit', 'bookly' );
                            break;
                    }
                }
            }
            $trs = array();
            foreach ( $cart_info_data as $data ) {
                $item_codes = new Item\Codes( $this->order );
                $item_codes->prepareForItem( $data['item'], 'client' );
                $appointments[] = $item_codes->getReplaceCodes( $format );

                $tds = array();
                foreach ( $cart_columns as $column => $attr ) {
                    if ( $attr['show'] ) {
                        switch ( $column ) {
                            case 'service':
                                $service_name = $data['service_name'];
                                if ( ! empty ( $data['extras'] ) ) {
                                    $extras = '';
                                    if ( $format == 'html' ) {
                                        foreach ( $data['extras'] as $extra ) {
                                            $extras .= '<li>' . $extra['title'] . '</li>';
                                        }
                                        $extras = '<ul>' . $extras . '</ul>';
                                    } else {
                                        foreach ( $data['extras'] as $extra ) {
                                            $extras .= ', ' . str_replace( '&nbsp;&times;&nbsp;', ' x ', $extra['title'] );
                                        }
                                    }
                                    $service_name .= $extras;
                                }
                                $tds[] = $service_name;
                                break;
                            case 'date':
                                $tds[] = $data['appointment_start'] === null ? __( 'N/A', 'bookly' ) : Utils\DateTime::formatDate( $data['appointment_start'] );
                                break;
                            case 'time':
                                if ( $data['appointment_start_info'] !== null ) {
                                    $tds[] = $data['appointment_start_info'];
                                } else {
                                    $tds[] = $data['appointment_start'] === null ? __( 'N/A', 'bookly' ) : Utils\DateTime::formatTime( $data['appointment_start'] );
                                }
                                break;
                            case 'tax':
                                $tds[] = Utils\Price::format( $data['tax'] );
                                break;
                            case 'employee':
                                $tds[] = $data['staff_name'];
                                break;
                            case 'price':
                                $tds[] = Utils\Price::format( $data['appointment_price'] );
                                break;
                            case 'deposit':
                                $tds[] = $data['deposit'];
                                break;
                        }
                    }
                }
                $tds[] = $data['cancel_url'];
                $trs[] = $tds;
            }
            if ( $format == 'html' ) {
                $cart_info   = '<table cellspacing="1" border="1" cellpadding="5"><thead><tr><th>' . implode( '</th><th>', $ths ) . '</th></tr></thead><tbody>';
                $cart_info_c = '<table cellspacing="1" border="1" cellpadding="5"><thead><tr><th>' . implode( '</th><th>', $ths ) . '</th><th>' . __( 'Cancel', 'bookly' ) . '</th></tr></thead><tbody>';
                foreach ( $trs as $tr ) {
                    $cancel_url   = array_pop( $tr );
                    $cart_info   .= '<tr><td>' . implode( '</td><td>', $tr ) . '</td></tr>';
                    $cart_info_c .= '<tr><td>' . implode( '</td><td>', $tr ) . '</td><td><a href="' . $cancel_url . '">' . __( 'Cancel', 'bookly' ) . '</a></td></tr>';
                }
                $cart_info   .= '</tbody></table>';
                $cart_info_c .= '</tbody></table>';
            } else {
                foreach ( $trs as $tr ) {
                    $cancel_url = array_pop( $tr );
                    foreach ( $ths as $position => $column ) {
                        $cart_info   .= $column . ' ' . $tr[ $position ] . "\r\n";
                        $cart_info_c .= $column . ' ' . $tr[ $position ] . "\r\n";
                    }
                    $cart_info .= "\r\n";
                    $cart_info_c .= __( 'Cancel', 'bookly' )  . ' ' . $cancel_url . "\r\n\r\n";
                }
            }
        }

        // Replace codes.
        $replace_codes += array(
            'cart_info' => $cart_info,
            'cart_info_c' => $cart_info_c,
            'cancel_all_combined_appointments_url' => $this->order_token === null ? '' : admin_url( 'admin-ajax.php?action=bookly_pro_cancel_appointments&token=' . $this->order_token ),
            'appointment_notes' => $this->appointment_notes,
            'appointments' => $appointments,
        );

        $replace_codes['cancel_all_combined_appointments'] = $format == 'html'
            ? sprintf( '<a href="%1$s">%1$s</a>', $replace_codes['cancel_all_combined_appointments_url'] )
            : $replace_codes['cancel_all_combined_appointments_url'];

        return $replace_codes;
    }
}