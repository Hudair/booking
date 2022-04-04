<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Dialogs;
/** @var array $datatables */
?>
<input type="hidden" name="form-notifications">
<div class="form-group">
    <label for="admin_phone">
        <?php esc_html_e( 'Administrator phone', 'bookly' ) ?>
    </label>
    <div class="form-row">
        <div class="col-auto">
            <input class="form-control w-100 mb-3 mb-md-0" id="admin_phone" name="bookly_sms_administrator_phone" type="text" value="<?php form_option( 'bookly_sms_administrator_phone' ) ?>">
        </div>
        <div class="col-auto">
            <div class="btn-group">
                <button class="btn btn-success" id="send_test_sms"><?php esc_html_e( 'Send test SMS', 'bookly' ) ?></button>
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                </button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item" data-action="save-administrator-phone"><?php esc_html_e( 'Save administrator phone', 'bookly' ) ?></a>
                </div>
            </div>
        </div>
    </div>
    <small class="form-text text-muted"><?php esc_html_e( 'Enter a phone number in international format. E.g. for the United States a valid phone number would be +17327572923.', 'bookly' ) ?></small>
</div>

<form method="post" action="<?php echo Common::escAdminUrl( $self::pageSlug() ) ?>">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                <input class="form-control" type="text" id="bookly-filter" placeholder="<?php esc_attr_e( 'Quick search notifications', 'bookly' ) ?>"/>
            </div>
        </div>
        <div class="col-md-8 form-row justify-content-end pr-2">
            <?php Dialogs\Sms\Dialog::renderNewNotificationButton() ?>
            <?php Dialogs\TableSettings\Dialog::renderButton( 'sms_notifications', 'BooklyL10n', esc_attr( add_query_arg( 'tab', 'notifications' ) ) ) ?>
        </div>
    </div>


    <table id="bookly-js-notification-list" class="table table-striped w-100">
        <thead>
        <tr>
            <?php foreach ( $datatables['sms_notifications']['settings']['columns'] as $column => $show ) : ?>
                <?php if ( $show ) : ?>
                    <?php if ( $column  === 'type' ) : ?>
                        <th width="1"></th>
                    <?php else : ?>
                        <th><?php echo esc_html( $datatables['sms_notifications']['titles'][ $column ] ) ?></th>
                    <?php endif ?>
                <?php endif ?>
            <?php endforeach ?>
            <th width="75"></th>
            <th width="16"><?php Inputs::renderCheckBox( null, null, null, array( 'id' => 'bookly-check-all' ) ) ?></th>
        </tr>
        </thead>
    </table>

    <div class="text-right my-3">
        <?php Inputs::renderCsrf() ?>
        <?php Buttons::renderDelete( 'bookly-js-delete-notifications' ) ?>
    </div>

    <div class="alert alert-info">
        <div class="row">
            <div class="col-md-12">
                <?php if ( is_multisite() ) : ?>
                    <p><?php printf( __( 'To send scheduled notifications please refer to <a href="%1$s">Bookly Multisite</a> add-on <a href="%2$s">message</a>.', 'bookly' ), Common::prepareUrlReferrers( 'http://codecanyon.net/item/bookly-multisite-addon/13903524?ref=ladela', 'cron_setup' ), network_admin_url( 'admin.php?page=bookly-multisite-network' ) ) ?></p>
                <?php else : ?>
                    <p><?php esc_html_e( 'To send scheduled notifications please execute the following command hourly with your cron:', 'bookly' ) ?></p>
                    <code>wget -q -O - <?php echo site_url( 'wp-cron.php' ) ?></code>
                <?php endif ?>
            </div>
        </div>
    </div>
</form>
<?php Dialogs\Sms\Dialog::render() ?>