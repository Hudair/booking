<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
?>
<form id="bookly-archiving-confirmation" class="bookly-modal bookly-fade" tabindex=-1>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title h5"><?php esc_html_e( 'Archiving Staff', 'bookly' ) ?></div>
                <button type="button" class="close bookly-js-close" data-dismiss="bookly-modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <p>
                    <?php esc_html_e( 'You are going to archive item which is involved in upcoming appointments. Please double check and edit appointments before this item archive if needed.', 'bookly' ) ?>
                </p>
            </div>
            <div class="modal-footer">
                <?php Buttons::render( null, 'btn-success', __( 'Ok, continue editing', 'bookly' ), array( 'style' => 'display:none;', 'data-dismiss' => 'bookly-modal' ) ) ?>
                <?php Buttons::render( null, 'btn-danger bookly-js-staff-archive', __( 'Archive', 'bookly' ), array() ) ?>
                <?php Buttons::render( null, 'btn-success bookly-js-edit', __( 'Edit appointments', 'bookly' ) ) ?>
                <?php Buttons::render( null, 'btn-default bookly-js-close', __( 'Cancel', 'bookly' ), array( 'data-dismiss' => 'bookly-modal' ) ) ?>
            </div>
        </div>
    </div>
</form>