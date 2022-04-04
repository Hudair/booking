<?php
namespace BooklyPro\Frontend\Modules\WooCommerce;

use Bookly\Lib as BooklyLib;
use Bookly\Frontend\Modules\Booking\Lib\Errors;

/**
 * Class Ajax
 * @package BooklyPro\Frontend\Modules\WooCommerce
 */
class Ajax extends Controller
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'anonymous' );
    }

    /**
     * Add product to cart
     *
     * return string JSON
     */
    public static function addToWoocommerceCart()
    {
        if ( ! get_option( 'bookly_wc_enabled' ) ) {
            exit;
        }
        $userData = new BooklyLib\UserBookingData( self::parameter( 'form_id' ) );

        if ( $userData->load() ) {
            $session = WC()->session;
            /** @var \WC_Session_Handler $session */
            if ( $session instanceof \WC_Session_Handler && $session->get_session_cookie() === false ) {
                $session->set_customer_session_cookie( true );
            }
            if ( $userData->cart->getFailedKey() === null ) {
                $cart_item  = self::_getIntersectedItem( $userData->cart->getItems() );
                if ( $cart_item === null ) {
                    if ( get_option( 'bookly_cst_first_last_name' ) ) {
                        $first_name = $userData->getFirstName();
                        $last_name  = $userData->getLastName();
                    } else {
                        $full_name  = $userData->getFullName();
                        $first_name = strtok( $full_name, ' ' );
                        $last_name  = strtok( '' );
                    }
                    $iso = $userData->getAddressIso();
                    $bookly = $userData->getData();
                    $bookly['version']     = self::VERSION;
                    $bookly['items']       = $userData->cart->getItemsData();
                    $bookly['wc_checkout'] = array(
                        'billing_first_name' => $first_name,
                        'billing_last_name'  => $last_name,
                        'billing_email'      => $userData->getEmail(),
                        'billing_phone'      => $userData->getPhone(),
                        // Billing country on WC checkout is select (select2)
                        'billing_country'    => isset( $iso['country'] ) ? $iso['country'] : null,
                        'billing_state'      => isset( $iso['state'] )   ? $iso['state'] : null,
                        'billing_city'       => $userData->getCity() ?: null,
                        'billing_address_1'  => $userData->getStreet() ? BooklyLib\Proxy\Pro::getFullAddressByCustomerData( array( 'street' => $userData->getStreet(), 'street_number' => $userData->getStreetNumber() ) ) : null,
                        'billing_address_2'  => $userData->getAdditionalAddress() ?: null,
                        'billing_postcode'   => $userData->getPostcode() ?: null,
                    );
                    if ( BooklyLib\Config::tasksActive() ) {
                        foreach ( $userData->cart->getItems() as $cart_item ) {
                            $slots = $cart_item->getSlots();
                            if ( $slots[0][2] === null ) {
                                // If identical appointments with no date/time are added to WC cart, WC will increase quantity for such items.
                                // To allow client add more than one task in cart, generate random line.
                                $bookly['wc_checkout']['bookly_random'] = md5( uniqid( time(), true ) );
                                break;
                            }
                        }
                    }
                    $product_id = get_option( 'bookly_wc_product' );
                    if ( count( $bookly['items'] ) == 1 ) {
                        $product_id = BooklyLib\Entities\Service::query()
                            ->where( 'id', $bookly['items'][0]['service_id'] )->fetchVar( 'wc_product_id' ) ?: $product_id;
                    }

                    // Qnt 1 product in $userData exists value with number_of_persons
                    WC()->cart->add_to_cart( $product_id, 1, '', array(), compact( 'bookly' ) );

                    $response = array( 'success' => true );
                } else {
                    $response = array( 'success' => false, 'error' => Errors::CART_ITEM_NOT_AVAILABLE );
                }
            } else {
                $response = array( 'success' => false, 'error' => Errors::CART_ITEM_NOT_AVAILABLE );
            }
        } else {
            $response = array( 'success' => false, 'error' => Errors::SESSION_ERROR );
        }
        wp_send_json( $response );
    }

    /**
     * Find conflicted CartItem with items in WC Cart.
     *
     * Resolved:
     *  number of persons > staff.capacity
     *  Services for some Staff intersected
     *
     * @param BooklyLib\CartItem[] $new_items
     * @return BooklyLib\CartItem
     */
    private static function _getIntersectedItem( array $new_items )
    {
        /** @var BooklyLib\CartItem[] $wc_items */
        $wc_items  = array();
        $cart_item = new BooklyLib\CartItem();
        foreach ( WC()->cart->get_cart() as $wc_key => $wc_item ) {
            if ( array_key_exists( 'bookly', $wc_item ) ) {
                if ( self::_migration( $wc_key, $wc_item ) === false ) {
                    // Removed item from cart.
                    continue;
                }
                foreach ( $wc_item['bookly']['items'] as $item_data ) {
                    $entity = clone $cart_item;
                    $entity->setData( $item_data );
                    if ( $wc_item['quantity'] > 1 ) {
                        $nop = $item_data['number_of_persons'] *= $wc_item['quantity'];
                        $entity->setNumberOfPersons( $nop );
                    }
                    $wc_items[] = $entity;
                }
            }
        }
        $staff_service = array();
        foreach ( $new_items as $candidate_cart_item ) {
            $candidate_slots = $candidate_cart_item->getSlots();
            if ( $candidate_slots[0][2] === null ) {
                // Appointment with no date/time can't overlap with other appointments
                continue;
            }
            $candidate_staff_id = $candidate_cart_item->getStaff()->getId();
            $candidate_service_id = $candidate_cart_item->getService()->getId();
            foreach ( $wc_items as $wc_cart_item ) {
                $wc_cart_slots = $wc_cart_item->getSlots();
                // $wc_cart_service can be null when service exists in WC cart but removed from Bookly
                $wc_cart_service = $wc_cart_item->getService();
                if ( $wc_cart_service && $wc_cart_item->getStaff() && $candidate_cart_item->getService() ) {
                    if ( $candidate_staff_id == $wc_cart_item->getStaff()->getId() ) {
                        // Equal Staff
                        $candidate_start = date_create( $candidate_slots[0][2] );
                        $candidate_end   = date_create( $candidate_slots[0][2] )->modify( ( $candidate_cart_item->getService()->getDuration() + $candidate_cart_item->getExtrasDuration() ) . ' sec' );
                        $wc_cart_start   = date_create( $wc_cart_slots[0][2] );
                        $wc_cart_end     = date_create( $wc_cart_slots[0][2] )->modify( ( $wc_cart_service->getDuration() + $wc_cart_item->getExtrasDuration() ) . ' sec' );
                        if ( ( $wc_cart_end > $candidate_start ) && ( $candidate_end > $wc_cart_start ) ) {
                            // Services intersected.
                            if ( $candidate_start == $wc_cart_start ) {
                                // Equal Staff/Service/Start
                                if ( ! isset( $staff_service[ $candidate_staff_id ][ $candidate_service_id ] ) ) {
                                    $staff_service[ $candidate_staff_id ][ $candidate_service_id ] = self::_getMaxCapacity( $candidate_staff_id, $candidate_service_id );
                                }
                                $allow_capacity = $staff_service[ $candidate_staff_id ][ $candidate_service_id ];
                                $nop = $candidate_cart_item->getNumberOfPersons() + $wc_cart_item->getNumberOfPersons();
                                if ( $nop > $allow_capacity ) {
                                    // Equal Staff/Service/Start and number_of_persons > capacity
                                    return $candidate_cart_item;
                                }
                            } else {
                                // Intersect Services for some Staff
                                return $candidate_cart_item;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    /**
     * Get max capacity.
     *
     * @param int $staff_id
     * @param int $service_id
     * @return int
     */
    private static function _getMaxCapacity( $staff_id, $service_id )
    {
        return BooklyLib\Config::groupBookingActive()
            ? BooklyLib\Entities\StaffService::query()
                ->where( 'staff_id', $staff_id )
                ->where( 'service_id', $service_id )
                ->fetchVar( 'capacity_max' )
            : 1;
    }
}