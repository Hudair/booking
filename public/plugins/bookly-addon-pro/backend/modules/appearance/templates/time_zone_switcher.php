<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
?>
<div class="bookly-box bookly-js-time-zone-switcher">
    <select class="bookly-time-zone-switcher">
        <?php echo wp_timezone_choice( $tz_string, BooklyLib\Config::getLocale() ) ?>
    </select>
</div>