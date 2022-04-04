<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Dialogs\Service\Edit\Proxy;
use Bookly\Lib\Entities\Service;
/** @var array $service */
/** @var array $min_time_prior_cancel */
/** @var array $min_time_prior_booking */
?>
<div class="bookly-js-service-advanced-container">
    <?php if ( $service['type'] == Service::TYPE_SIMPLE ) : ?>
        <?php Proxy\GroupBooking::renderSubForm( $service ) ?>
        <?php self::renderTemplate( '_online_meeting_provider', compact( 'service' ) ) ?>
    <?php endif ?>
    <?php self::renderTemplate( '_limit_per_customer', compact( 'service' ) ) ?>
    <?php self::renderTemplate( 'final_step_url', compact( 'service' ) ) ?>
    <?php self::renderTemplate( '_time_requirements', compact( 'min_time_prior_cancel', 'min_time_prior_booking', 'service' ) ) ?>
    <?php Proxy\Taxes::renderSubForm( $service ) ?>
    <?php Proxy\Discounts::renderSubForm( $service ) ?>
    <?php Proxy\RecurringAppointments::renderSubForm( $service ) ?>
</div>