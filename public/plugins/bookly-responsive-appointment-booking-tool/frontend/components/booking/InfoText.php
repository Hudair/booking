<?php
namespace Bookly\Frontend\Components\Booking;

use Bookly\Lib;
use Bookly\Frontend\Modules\Booking\Proxy;
use Bookly\Frontend\Modules\Booking\Lib\Steps;

/**
 * Class InfoText
 * @package Bookly\Frontend\Components\Booking
 */
class InfoText
{
    /**
     * Replaces codes with data in given text
     *
     * @param int $step
     * @param string $text
     * @param Lib\UserBookingData $userData
     * @return string
     */
    public static function prepare( $step, $text, Lib\UserBookingData $userData )
    {
        $codes = array();

        switch ( $step ) {
            case Steps::SERVICE:
                break;
            case Steps::EXTRAS:
            case Steps::TIME:
            case Steps::REPEAT:
                $data = array(
                    'appointments' => array(),
                    'appointment_date' => array(),
                    'appointment_time' => array(),
                    'category_name' => array(),
                    'number_of_persons' => array(),
                    'online_meeting_url' => array(),       // @todo Remove it from here and adjust proxy methods so that codes can be processed for each step independently
                    'online_meeting_password' => array(),
                    'online_meeting_join_url' => array(),
                    'service_duration' => array(),
                    'service_info' => array(),
                    'service_name' => array(),
                    'service_image' => array(),
                    'service_price' => array(),
                    'staff_info' => array(),
                    'staff_name' => array(),
                    'staff_photo' => array(),
                    'total_deposit_price' => 0,
                    'total_price' => 0,
                );

                foreach ( $userData->chain->getItems() as $num => $chain_item ) {
                    $appointment_data = array(
                        'category_name' => '',
                        'appointment_date' => '',
                        'service_duration' => '',
                        'service_name' => '',
                        'service_image' => '',
                        'service_info' => '',
                        'staff_name' => '',
                        'service_price' => '',
                        'appointment_time' => '',
                    );
                    /** @var Lib\Entities\Service $service */
                    $service = Lib\Entities\Service::find( $chain_item->getServiceId() );
                    $appointment_data['category_name'] = $service->getTranslatedCategoryName();
                    $appointment_data['service_name'] = $service->getTranslatedTitle();
                    $appointment_data['service_image'] = ( $url = $service->getImageUrl() ) ? '<img src="' . $url . '"/>' : '';
                    $appointment_data['service_info'] = $service->getTranslatedInfo();

                    $data['number_of_persons'][] = $chain_item->getNumberOfPersons();
                    $data['category_name'][] = $appointment_data['category_name'];
                    $data['service_name'][] = $appointment_data['service_name'];
                    $data['service_image'][] = $appointment_data['service_image'];
                    $data['service_info'][] = $service->getTranslatedInfo();

                    $duration = 0;
                    if ( $service->withSubServices() ) {
                        foreach ( $service->getSubServices() as $sub_service ) {
                            if ( $service->isCompound() ) {
                                $duration += $sub_service->getDuration();
                            } else if ( $service->isCollaborative() ) {
                                $duration = max( $duration, $sub_service->getDuration() );
                            }
                        }
                    } else {
                        $duration = $chain_item->getUnits() * $service->getDuration();
                    }
                    $appointment_data['service_duration'] = Lib\Utils\DateTime::secondsToInterval( $duration );
                    $data['service_duration'][] = $appointment_data['service_duration'];

                    /** @var Lib\Entities\Staff $staff */
                    $staff = null;
                    if ( $step == Steps::REPEAT ) {
                        $slot = $userData->getSlots();
                        list ( $slot_service, $slot_staff, $slot_time ) = $slot[ $num ];
                        $staff = Lib\Entities\Staff::find( $slot_staff );

                        if ( $slot_time !== null ) {
                            $service_dp = Lib\Slots\DatePoint::fromStr( $slot_time )->toClientTz();
                            $appointment_data['appointment_date'] = $service_dp->formatI18nDate();
                            $appointment_data['appointment_time'] = $duration >= DAY_IN_SECONDS ? $service->getStartTimeInfo() : $service_dp->formatI18nTime();
                        } else {
                            $appointment_data['appointment_date'] = __( 'N/A', 'bookly' );
                            $appointment_data['appointment_time'] = __( 'N/A', 'bookly' );
                        }
                        $data['appointment_date'][] = $appointment_data['appointment_date'];
                        $data['appointment_time'][] = $appointment_data['appointment_time'];
                    } else {
                        $staff_ids = $chain_item->getStaffIds();
                        if ( count( $staff_ids ) == 1 ) {
                            $staff = Lib\Entities\Staff::find( $staff_ids[0] );
                        }
                    }
                    $staff_photo = '';
                    if ( $staff ) {
                        $appointment_data['staff_name'] = esc_html( $staff->getTranslatedName() );
                        $data['staff_info'][] = esc_html( $staff->getTranslatedInfo() );

                        if ( $staff->getAttachmentId() && $img = wp_get_attachment_image_src( $staff->getAttachmentId(), 'full' ) ) {
                            $staff_photo = '<img src="' . $img[0] . '"/>';
                        }
                        if ( $service->withSubServices() ) {
                            $service_price = $service->getPrice();
                            $price = $deposit_price = $service_price;
                        } else {
                            $staff_service = new Lib\Entities\StaffService();
                            $staff_service->loadBy( array(
                                'staff_id' => $staff->getId(),
                                'service_id' => $service->getId(),
                                'location_id' => $chain_item->getLocationId() ?: null,
                            ) );
                            if ( ! $staff_service->getId() ) {
                                $staff_service->loadBy( array(
                                    'staff_id' => $staff->getId(),
                                    'service_id' => $service->getId(),
                                    'location_id' => null,
                                ) );
                            }
                            $service_price = $staff_service->getPrice() * $chain_item->getUnits();
                            $price = Lib\Proxy\ServiceExtras::prepareServicePrice(
                                $service_price * $chain_item->getNumberOfPersons(),
                                $service_price,
                                $chain_item->getNumberOfPersons(),
                                $chain_item->getExtras()
                            );
                            $price = Lib\Proxy\Discounts::prepareServicePrice( $price, $service->getId(), $chain_item->getNumberOfPersons() );
                            $deposit_price = Lib\Proxy\DepositPayments::prepareAmount( $price, $staff_service->getDeposit(), $chain_item->getNumberOfPersons() );
                        }
                    } else {
                        $service_price = $service->getPrice() * $chain_item->getUnits();
                        $appointment_data['staff_name'] = Lib\Utils\Common::getTranslatedOption( 'bookly_l10n_option_employee' );
                        $price = Lib\Proxy\ServiceExtras::prepareServicePrice(
                            $service->getPrice() * $chain_item->getNumberOfPersons(),
                            $service->getPrice(),
                            $chain_item->getNumberOfPersons(),
                            $chain_item->getExtras()
                        );
                        $price = Lib\Proxy\Discounts::prepareServicePrice( $price, $service->getId(), $chain_item->getNumberOfPersons() );
                        $deposit_price = $price;
                    }
                    $appointment_data['service_price'] = Lib\Utils\Price::format( $service_price );
                    $data['appointments'][] = $appointment_data;
                    $data['service_price'][] = $appointment_data['service_price'];
                    $data['staff_name'][] = $appointment_data['staff_name'];
                    $data['staff_photo'][] = $staff_photo;
                    $data['total_price'] += $price;
                    $data['total_deposit_price'] += $deposit_price;

                    $data = Proxy\Shared::prepareChainItemInfoText( $data, $chain_item );
                }

                $codes = array(
                    'amount_due' => Lib\Utils\Price::format( $data['total_price'] - $data['total_deposit_price'] ),
                    'amount_to_pay' => Lib\Utils\Price::format( $data['total_deposit_price'] ),
                    'appointments' => $data['appointments'],
                    'appointment_date' => self::implode( $data['appointment_date'] ),
                    'appointment_time' => self::implode( $data['appointment_time'] ),
                    'category_name' => self::implode( $data['category_name'] ),
                    'deposit_value' => Lib\Utils\Price::format( $data['total_deposit_price'] ),
                    'number_of_persons' => self::implode( $data['number_of_persons'] ),
                    'service_date' => self::implode( $data['appointment_date'] ),  // deprecated
                    'service_duration' => self::implode( $data['service_duration'] ),
                    'service_info' => self::implode( $data['service_info'] ),
                    'service_name' => self::implode( $data['service_name'] ),
                    'service_image' => self::implode( $data['service_image'] ),
                    'service_price' => self::implode( $data['service_price'] ),
                    'service_time' => self::implode( $data['appointment_time'] ),  // deprecated
                    'staff_info' => self::implode( $data['staff_info'] ),
                    'staff_name' => self::implode( $data['staff_name'] ),
                    'staff_photo' => self::implode( $data['staff_photo'] ),
                    'total_price' => Lib\Utils\Price::format( $data['total_price'] ),
                );
                $codes = Proxy\Shared::prepareInfoTextCodes( $codes, $data );

                break;
            default:
                $data = array(
                    'appointments' => array(),
                    'appointment_date' => array(),
                    'appointment_time' => array(),
                    'booking_number' => array(),
                    'category_name' => array(),
                    'extras' => array(),
                    'number_of_persons' => array(),
                    'online_meeting_url' => array(),
                    'online_meeting_password' => array(),
                    'online_meeting_join_url' => array(),
                    'service' => array(),
                    'service_duration' => array(),
                    'service_info' => array(),
                    'service_name' => array(),
                    'service_image' => array(),
                    'service_price' => array(),
                    'staff_info' => array(),
                    'staff_name' => array(),
                    'staff_photo' => array(),
                );

                foreach ( $userData->cart->getItems() as $cart_item ) {
                    $appointment_data = array(
                        'category_name' => '',
                        'appointment_date' => '',
                        'service_duration' => '',
                        'service_name' => '',
                        'service_image' => '',
                        'service_info' => '',
                        'staff_name' => '',
                        'service_price' => '',
                        'appointment_time' => '',
                    );
                    $service = $cart_item->getService();
                    $slots = $cart_item->getSlots();
                    $service_dp = Lib\Slots\DatePoint::fromStr( $slots[0][2] )->toClientTz();

                    $appointment_data['appointment_date'] = $slots[0][2] !== null ? $service_dp->formatI18nDate() : __( 'N/A', 'bookly' );
                    $appointment_data['category_name'] = $service->getTranslatedCategoryName();
                    $appointment_data['service_price'] = Lib\Utils\Price::format( $cart_item->getServicePriceWithoutExtras() );
                    $appointment_data['service_name'] = $service->getTranslatedTitle();
                    $appointment_data['service_image'] = ( $url = $service->getImageUrl() ) ? '<img src="' . $url . '"/>' : '';
                    $appointment_data['service_info'] = $service->getTranslatedInfo();

                    $data['appointment_date'][] = $appointment_data['appointment_date'];
                    $data['category_name'][] = $appointment_data['category_name'];
                    $data['number_of_persons'][] = $cart_item->getNumberOfPersons();
                    $data['service_info'][] = $service->getTranslatedInfo();
                    $data['service_name'][] = $appointment_data['service_name'];
                    $data['service_image'][] = $appointment_data['service_image'];
                    $data['service_price'][] = $appointment_data['service_price'];
                    if ( $cart_item->getService()->withSubServices() ) {
                        $duration = 0;
                        foreach ( $cart_item->getService()->getSubServices() as $sub_service ) {
                            if ( $cart_item->getService()->isCompound() ) {
                                $duration += $sub_service->getDuration();
                            } else if ( $cart_item->getService()->isCollaborative() ) {
                                $duration = max( $duration, $sub_service->getDuration() );
                            }
                        }
                        $appointment_data['appointment_time'] = $slots[0][2] !== null
                            ? ( $duration >= DAY_IN_SECONDS ? $service->getStartTimeInfo() : $service_dp->formatI18nTime() )
                            : __( 'N/A', 'bookly' );
                        $appointment_data['service_duration'] = Lib\Utils\DateTime::secondsToInterval( $duration );
                    } else {
                        $appointment_data['appointment_time'] = $slots[0][2] !== null
                            ? ( $cart_item->getUnits() * $cart_item->getService()->getDuration() >= DAY_IN_SECONDS ? $service->getStartTimeInfo() : $service_dp->formatI18nTime() )
                            : __( 'N/A', 'bookly' );
                        $appointment_data['service_duration'] = Lib\Utils\DateTime::secondsToInterval( $cart_item->getUnits() * $cart_item->getService()->getDuration() );
                    }
                    $data['appointment_time'][] = $appointment_data['appointment_time'];
                    $data['service_duration'][] = $appointment_data['service_duration'];
                    // For Task when time step can be skipped, staff can be false
                    $staff = $cart_item->getStaff();
                    $appointment_data['staff_name'] = $staff ? esc_html( $staff->getTranslatedName() ) : '';
                    $data['staff_info'][] = $staff ? esc_html( $staff->getTranslatedInfo() ) : '';
                    $data['staff_name'][] = $appointment_data['staff_name'];
                    if ( $staff && $staff->getAttachmentId() && $img = wp_get_attachment_image_src( $staff->getAttachmentId(), 'full' ) ) {
                        $data['staff_photo'][] = '<img src="' . $img[0] . '"/>';
                    } else {
                        $data['staff_photo'][] = '';
                    }

                    // If appointment exists, prepare some additional data.
                    if ( $cart_item->getAppointmentId() ) {
                        $data['booking_number'][] = $cart_item->getAppointmentId();
                    }

                    $data['appointments'][] = $appointment_data;

                    $data = Proxy\Shared::prepareCartItemInfoText( $data, $cart_item );
                }

                $with_coupon = $step == Steps::PAYMENT || $step == Steps::DONE; // >= step payment
                $gateway = $step == Steps::DONE ? $userData->getPaymentType() : null;
                $cart_info = $userData->cart->getInfo( $gateway, $with_coupon );
                $data['_cart_info'] = $cart_info;

                $codes = array(
                    'amount_due' => Lib\Utils\Price::format( $cart_info->getDue() ),
                    'amount_to_pay' => Lib\Utils\Price::format( $cart_info->getPayNow() ),
                    'appointments' => $data['appointments'],
                    'appointments_count' => count( $userData->cart->getItems() ),
                    'appointment_date' => self::implode( $data['appointment_date'] ),
                    'appointment_time' => self::implode( $data['appointment_time'] ),
                    'booking_number' => self::implode( $data['booking_number'] ),
                    'category_name' => self::implode( $data['category_name'] ),
                    'deposit_value' => Lib\Utils\Price::format( $cart_info->getDepositPay() ),
                    'number_of_persons' => self::implode( $data['number_of_persons'] ),
                    'service_date' => self::implode( $data['appointment_date'] ),  // deprecated
                    'service_duration' => self::implode( $data['service_duration'] ),
                    'service_info' => self::implode( $data['service_info'] ),
                    'service_name' => self::implode( $data['service_name'] ),
                    'service_image' => self::implode( $data['service_image'] ),
                    'service_price' => self::implode( $data['service_price'] ),
                    'service_time' => self::implode( $data['appointment_time'] ),  // deprecated
                    'staff_info' => self::implode( $data['staff_info'] ),
                    'staff_name' => self::implode( $data['staff_name'] ),
                    'staff_photo' => self::implode( $data['staff_photo'] ),
                    'total_price' => Lib\Utils\Price::format( $cart_info->getTotal() ),
                );
                if ( $step == Steps::DETAILS ) {
                    $codes['login_form'] = ! get_current_user_id() && ! $userData->getFacebookId()
                        ? sprintf( '<a class="bookly-js-login-show" href="#">%s</a>', __( 'Log In' ) )
                        : '';
                }
                $codes = Proxy\Shared::prepareInfoTextCodes( $codes, $data );

                break;
        }

        return nl2br( Lib\Utils\Codes::replace( $text, $codes, array( 'online_meeting_url', 'online_meeting_join_url' ) ) );
    }

    /**
     * Implode input data with comma
     *
     * @param array $data
     * @return string
     */
    protected static function implode( $data )
    {
        return implode( ', ', array_filter( $data, function ( $value ) { return ! is_null( $value ) && $value !== ''; } ) );
    }
}