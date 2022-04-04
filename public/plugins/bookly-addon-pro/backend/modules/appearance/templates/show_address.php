<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Lib as BooklyLib;
?>
<div class="col-md-3 my-2">
    <div data-toggle="bookly-popover" data-trigger="hover" data-placement="auto">
        <?php Inputs::renderCheckBox( __( 'Show address fields', 'bookly' ), null, get_option( 'bookly_app_show_address' ), array( 'id' => 'bookly-show-address' ) ) ?>
    </div>
</div>