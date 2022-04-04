<?php if ( ! defined( 'ABSPATH' ) )  exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;
?>
<div class="row">
    <div class="col-md-12">
        <?php Selects::renderSingle( 'bookly_save_email_logs', __( 'Email logs', 'bookly' ), __( 'If this setting is enabled then all sent email notifications will be recorded in a log table. You can find these logs in Email Notifications > Email logs table.', 'bookly' ) ) ?>
    </div>
</div>