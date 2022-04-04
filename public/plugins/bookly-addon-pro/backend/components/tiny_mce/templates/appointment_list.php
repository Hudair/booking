<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
?>
<div id="bookly-tinymce-appointment-popup" style="display: none">
    <form id="bookly-appointment-list-shortcode-form">
        <table>
            <tr>
                <th class="bookly-title-col"><?php esc_html_e( 'Titles', 'bookly' ) ?></th>
                <td>
                    <label><input type="checkbox" id="bookly-show-column-titles"/><?php esc_html_e( 'Yes', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <th class="bookly-title-col"><?php esc_html_e( 'Columns', 'bookly' ) ?></th>
                <td>
                    <label><input type="checkbox" data-column="category" /><?php esc_html_e( 'Category', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="service" /><?php esc_html_e( 'Service', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="staff" /><?php esc_html_e( 'Staff', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="date" /><?php esc_html_e( 'Date', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="time" /><?php esc_html_e( 'Time', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="price" /><?php esc_html_e( 'Price', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="online_meeting" /><?php esc_html_e( 'Online meeting', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="status" /><?php esc_html_e( 'Status', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <label><input type="checkbox" data-column="cancel" /><?php esc_html_e( 'Cancel', 'bookly' ) ?></label>
                </td>
            </tr>
            <tr>
                <th colspan="2"><?php esc_html_e( 'Custom Fields', 'bookly' ) ?></th>
            </tr>
            <?php foreach ( $custom_fields as $field ) : ?>
                <?php if ( $field->type != 'file' ) : ?>
                    <tr>
                        <td class="bookly-cf-col"><?php echo Common::stripScripts( $field->label ) ?></td>
                        <td>
                            <label><input type="checkbox" data-custom_field="<?php echo $field->id ?>"/><?php esc_html_e( 'Yes', 'bookly' ) ?></label>
                        </td>
                    </tr>
                <?php endif ?>
            <?php endforeach ?>
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
    #bookly-short-code-form table td { padding: 5px; vertical-align: 0; }
    #bookly-short-code-form table th.bookly-title-col { width: 80px; }
</style>

<script type="text/javascript">
    jQuery(function ($) {
        var $form = $('#bookly-appointment-list-shortcode-form'),
            $add_button_appointment = $('#add-ap-appointment'),
            $insert                 = $('button.bookly-js-insert-shortcode', $form);

        $add_button_appointment.on('click', function () {
            window.parent.tb_show(<?php echo json_encode( __( 'Add Bookly appointments list', 'bookly' ) ) ?>, this.href);
            window.setTimeout(function(){
                $('#TB_window').css({
                    'overflow-x': 'auto',
                    'overflow-y': 'hidden'
                });
            },100);
        });

        $insert.on('click', function (e) {
            e.preventDefault();

            var shortcode = '[bookly-appointments-list',
                column;

            // columns
            var columns = $('[data-column]:checked');
            if (columns.length) {
                column = [];
                $.each(columns, function() {
                    column.push($(this).data('column'));
                });
                shortcode += ' columns="' + column.join(',') + '"';
            }
            // custom_fields
            var custom_fields = $('[data-custom_field]:checked');
            if (custom_fields.length) {
                column = [];
                $.each(custom_fields, function() {
                    column.push($(this).data('custom_field'));
                });
                shortcode += ' custom_fields="' + column.join(',') + '"';
            }


            if ($('#bookly-show-column-titles:checked').length) {
                shortcode += ' show_column_titles="1"';
            }

            window.send_to_editor(shortcode + ']');
            window.parent.tb_remove();
            return false;
        });
    });
</script>