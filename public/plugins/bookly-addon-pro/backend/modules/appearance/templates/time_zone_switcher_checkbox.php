<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
?>
<div class="col-md-3 my-2">
    <?php Inputs::renderCheckBox( __( 'Show time zone switcher', 'bookly' ), null, get_option( 'bookly_app_show_time_zone_switcher' ), array( 'id' => 'bookly-show-time-zone-switcher' ) ) ?>
</div>