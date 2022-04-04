<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Dialogs;
use Bookly\Backend\Modules\Notifications;
use Bookly\Lib\Utils\DateTime;

/** @var array $datatables */
?>

<div class="form-row">
    <div class="col-xl-4 col-lg-5 col-md-8">
        <div class="form-group">
            <button type="button" class="btn btn-default w-100 text-truncate text-left" id="bookly-email-logs-date-range" data-date="<?php echo date( 'Y-m-d', strtotime( 'first day of' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of' ) ) ?>">
                <i class="far fa-calendar-alt mr-1"></i>
                <span><?php echo DateTime::formatDate( 'first day of this month' ) ?> - <?php echo DateTime::formatDate( 'last day of this month' ) ?></span>
            </button>
        </div>
    </div>
    <div class="flex-fill justify-content-end form-row">
        <?php Dialogs\TableSettings\Dialog::renderButton( 'email_logs', 'BooklyL10n', esc_attr( add_query_arg( array( 'page' => Notifications\Page::pageSlug(), 'tab' => 'logs' ), admin_url( 'admin.php' ) ) ) ) ?>
    </div>
</div>
<div class="row">
    <div class="col">
        <table id="bookly-email-logs" class="table table-striped w-100">
            <thead>
            <tr>
                <?php foreach ( $datatables['email_logs']['settings']['columns'] as $column => $show ) : ?>
                    <?php if ( $show ) : ?>
                        <?php if ( $column == 'type' ) : ?>
                            <th width="1"></th>
                        <?php else : ?>
                            <th><?php echo $datatables['email_logs']['titles'][ $column ] ?></th>
                        <?php endif ?>
                    <?php endif ?>
                <?php endforeach ?>
                <th width="75"></th>
                <th width="16"><?php Inputs::renderCheckBox( null, null, null, array( 'id' => 'bookly-check-all' ) ) ?></th>
            </tr>
            </thead>
        </table>
        <div class="text-right mt-3">
            <?php Buttons::renderDelete( 'bookly-email-log-delete' ) ?>
        </div>
    </div>
</div>
<div id="bookly-email-logs-dialog" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'Email details', 'bookly' ) ?></h5>
                <button type="button" class="close" data-dismiss="bookly-modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="bookly-email-to"><?php esc_html_e( 'Recipient', 'bookly' ) ?></label>
                    <input type="text" id="bookly-email-to" class="form-control" readonly/>
                </div>
                <div class="form-group">
                    <label for="bookly-email-subject"><?php esc_html_e( 'Subject', 'bookly' ) ?></label>
                    <input type="text" id="bookly-email-subject" class="form-control" readonly/>
                </div>
                <div class="form-group">
                    <label for="bookly-email-body"><?php esc_html_e( 'Message', 'bookly' ) ?></label>
                    <textarea id="bookly-email-body" class="form-control" rows="12" readonly></textarea>
                </div>
                <div class="form-group">
                    <label for="bookly-email-headers"><?php esc_html_e( 'Headers', 'bookly' ) ?></label>
                    <textarea id="bookly-email-headers" class="form-control" rows="4" readonly></textarea>
                </div>
                <div class="form-group">
                    <label for="bookly-email-attachments"><?php esc_html_e( 'Attachments', 'bookly' ) ?></label>
                    <textarea id="bookly-email-attachments" class="form-control" rows="4" readonly></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderCancel( __( 'Close', 'bookly' ) ) ?>
            </div>
        </div>
    </div>
</div>