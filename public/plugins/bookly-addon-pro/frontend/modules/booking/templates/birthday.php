<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyPro\Frontend\Components;
use Bookly\Lib as BooklyLib;

/** @var BooklyLib\UserBookingData $userData */
?>
<div class="bookly-box bookly-table">
    <?php Components\Fields\Birthday::render( $userData ) ?>
</div>
