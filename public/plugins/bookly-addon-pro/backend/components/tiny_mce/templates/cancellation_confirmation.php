<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tinymce-cancellation-confirmation-popup" style="display: none">
    <form id="bookly-cancellation-confirmation-shortcode-form">
        <table>
            <tr>
                <td class="bookly-title-col"><?php esc_html_e( 'Show cancellation reason', 'bookly' ) ?></td>
                <td>
                    <input type="checkbox" id="bookly-show-reason" />
                </td>
            </tr>
            <tr>
                <td></td>
                <td class='wp-core-ui'>
                    <button class="button button-primary bookly-js-insert-shortcode" type="button"><?php esc_attr_e( 'Insert', 'bookly' ) ?></button>
                </td>
            </tr>
        </table>
    </form>
</div>

<style type="text/css">
    #bookly-cancellation-confirmation-shortcode-form { margin-top: 15px; }
    #bookly-cancellation-confirmation-shortcode-form table td { padding: 5px; vertical-align: 0; }
</style>

<script type="text/javascript">
    jQuery(function ($) {
        var $form = $('#bookly-cancellation-confirmation-shortcode-form'),
            $add_button_appointment = $('#add-cancellation-confirmation'),
            $insert                 = $('button.bookly-js-insert-shortcode', $form);

        $add_button_appointment.on('click', function () {
            window.parent.tb_show(<?php echo json_encode( __( 'Add appointment cancellation confirmation', 'bookly' ) ) ?>, this.href);
            window.setTimeout(function(){
                $('#TB_window').css({
                    'overflow-x': 'auto',
                    'overflow-y': 'hidden'
                });
            },100);
        });

        $insert.on('click', function (e) {
            e.preventDefault();

            var shortcode = '[bookly-cancellation-confirmation';

            if ($('#bookly-show-reason:checked').length) {
                shortcode += ' reason="1"';
            }

            window.send_to_editor(shortcode + ']');
            window.parent.tb_remove();
            return false;
        });
    });
</script>