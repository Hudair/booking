<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Frontend\Modules\CustomerProfile\Proxy\CustomFields as CustomFieldsProxy;
use Bookly\Lib\Entities;
use Bookly\Lib\Proxy;
use Bookly\Lib\Utils\DateTime;
use Bookly\Lib\Utils\Price;
use BooklyPro\Lib;
?>
<?php foreach ( $appointments as $app ) : ?>
    <?php
    $extras_total_price = 0;
    foreach ( $app['extras'] as $extra ) {
        $extras_total_price += $extra['price'];
    }
    ?>
    <tr>
        <?php foreach ( $columns as $column ) :
            switch ( $column ) :
                case 'service' : ?>
                    <td>
                    <?php echo esc_html( $app['service'] ) ?>
                    <?php if ( ! empty ( $app['extras'] ) ): ?>
                        <ul class="bookly-extras">
                            <?php foreach ( $app['extras'] as $extra ) : ?>
                                <li><?php echo esc_html( $extra['title'] ) ?></li>
                            <?php endforeach ?>
                        </ul>
                    <?php endif ?>
                    </td><?php
                    break;
                case 'date' : ?>
                    <td><?php echo $app['start_date'] === null ? __( 'N/A', 'bookly' ) : DateTime::formatDate( $app['start_date'] ) ?></td><?php
                    break;
                case 'time' : ?>
                    <td><?php echo $app['start_date'] === null ? __( 'N/A', 'bookly' ) : DateTime::formatTime( $app['start_date'] ) ?></td><?php
                    break;
                case 'price' : ?>
                    <td class="bookly-text-right"><?php echo Price::format( ( $app['price'] + $extras_total_price ) * $app['number_of_persons'] ) ?></td><?php
                    break;
                case 'status' : ?>
                    <td><?php echo esc_html( Entities\CustomerAppointment::statusToString( $app['appointment_status'] ) ) ?></td><?php
                    break;
                case 'online_meeting' : ?>
                    <?php $online_meeting_url = Proxy\Shared::buildOnlineMeetingJoinUrl( '', Entities\Appointment::find( $app['appointment_id'] ) ) ?>
                    <td><?php if ( $online_meeting_url ) : ?><a href="<?php echo $online_meeting_url ?>" target="_blank"><?php esc_html_e( 'Join', 'bookly' ) ?></a><?php endif ?></td><?php
                    break;
                case 'cancel' :
                    CustomFieldsProxy::renderCustomerProfileRow( $custom_fields, $app ) ?>
                    <td>
                    <?php if ( $app['start_date'] > current_time( 'mysql' ) || $app['start_date'] === null ) : ?>
                    <?php if ( ( current_time( 'timestamp' ) + Lib\Config::getMinimumTimePriorCancel( $app['service_id'] ) < strtotime( $app['start_date'] ) || $app['start_date'] === null ) && $app['appointment_status'] != Entities\CustomerAppointment::STATUS_DONE ) : ?>
                        <?php if ( ! in_array( $app['appointment_status'], Proxy\CustomStatuses::prepareFreeStatuses( array(
                            Entities\CustomerAppointment::STATUS_CANCELLED,
                            Entities\CustomerAppointment::STATUS_REJECTED,
                        ) ) ) ) : ?>
                            <a class="bookly-btn-default" style="background-color: <?php echo $color ?>" href="<?php echo esc_attr( $url_cancel . '&token=' . $app['token'] ) ?>">
                                <span><?php esc_html_e( 'Cancel', 'bookly' ) ?></span>
                            </a>
                        <?php endif ?>
                    <?php else : ?>
                        <?php esc_html_e( 'Not allowed', 'bookly' ) ?>
                    <?php endif ?>
                <?php else : ?>
                    <?php esc_html_e( 'Expired', 'bookly' ) ?>
                <?php endif ?>
                    </td><?php
                    break;
                default : ?>
                    <td><?php echo esc_html( $app[ $column ] ) ?></td>
                <?php endswitch ?>
        <?php endforeach ?>
        <?php if ( $with_cancel == false ) :
            CustomFieldsProxy::renderCustomerProfileRow( $custom_fields, $app );
        endif ?>
    </tr>
<?php endforeach ?>