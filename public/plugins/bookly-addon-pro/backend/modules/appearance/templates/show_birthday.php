<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs;
?>
<div class="col-md-3 my-2">
    <?php Inputs::renderCheckBox( __( 'Show birthday field', 'bookly' ), null, get_option( 'bookly_app_show_birthday' ), array( 'id' => 'bookly-show-birthday' ) ) ?>
</div>