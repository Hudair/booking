<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Entities\Notification;
$codes = new \Bookly\Backend\Modules\Notifications\Lib\Codes( 'email' );
?>
<div class="form-group bookly-js-codes-container">
    <a class="collapsed mb-2 d-inline-block" data-toggle="collapse" href="#bookly-notification-codes" role="button" aria-expanded="false" aria-controls="collapseExample">
        <?php esc_attr_e( 'Codes', 'bookly' ) ?>
    </a>
    <div class="collapse" id="bookly-notification-codes">
        <?php foreach ( Notification::getTypes() as $notification_type ) :
            $codes->render( $notification_type );
        endforeach ?>
    </div>
</div>