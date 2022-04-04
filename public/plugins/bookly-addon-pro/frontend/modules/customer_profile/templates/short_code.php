<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;

$color = get_option( 'bookly_app_color', '#f4662f' );
$compound_tokens = array();
$custom_fields = BooklyLib\Config::customFieldsActive() && get_option( 'bookly_custom_fields_enabled' ) && isset ( $attributes['custom_fields'] )
    ? explode( ',', $attributes['custom_fields'] )
    : array();
$columns = isset( $attributes['columns'] ) ? explode( ',', $attributes['columns'] ) : array();
$with_cancel = in_array( 'cancel', $columns );
$container = 'bookly-js-appointments-list-' . mt_rand( 0, PHP_INT_MAX );
?>
<?php if ( is_user_logged_in() ) : ?>
<style>
    .bookly-customer-appointment-list .bookly-colored-button {
        background: <?php echo $color ?>!important;
    }
</style>
    <div class="bookly-customer-appointment-list <?php echo $container ?>">
        <h2><?php esc_html_e( 'Appointments', 'bookly' ) ?></h2>
        <?php if ( ! empty( $columns ) || ! empty( $custom_fields ) ) : ?>
            <table class="bookly-appointments-table" data-columns="<?php echo esc_attr( json_encode( $columns ) ) ?>" data-custom_fields="<?php echo esc_attr( implode(',', $custom_fields ) ) ?>" data-page="0">
                <?php if ( isset( $attributes['show_column_titles'] ) && $attributes['show_column_titles'] ) : ?>
                    <thead>
                        <tr>
                            <?php foreach ( $columns as $column ) : ?>
                                <?php if ( $column != 'cancel' ) : ?>
                                    <th><?php echo $titles[ $column ] ?></th>
                                <?php endif ?>
                            <?php endforeach ?>
                            <?php foreach ( $custom_fields as $column ) : ?>
                                <th><?php if ( isset( $titles[ $column ] ) ) echo $titles[ $column ] ?></th>
                            <?php endforeach ?>
                            <?php if ( $with_cancel ) : ?>
                                <th><?php echo $titles['cancel'] ?></th>
                            <?php endif ?>
                        </tr>
                    </thead>
                <?php endif ?>
                <?php if ( empty( $appointments ) ) : ?>
                    <tr class="bookly--no-appointments"><td colspan="<?php echo count( $columns ) + count( $custom_fields ) ?>"><?php esc_html_e( 'No appointments found.', 'bookly' ) ?></td></tr>
                <?php else : ?>
                    <?php include '_rows.php' ?>
                <?php endif ?>
            </table>
            <?php if ( $more ) : ?>
                <button class="bookly-btn-default bookly-js-show-past bookly-colored-button" style="float: right; width: auto" data-spinner-size="40" data-style="zoom-in">
                    <span><?php esc_html_e( 'Show past appointments', 'bookly' ) ?></span>
                </button>
            <?php endif ?>
        <?php endif ?>
    </div>

    <script type="text/javascript">
        (function (win, fn) {
            var done = false, top = true,
                doc = win.document,
                root = doc.documentElement,
                modern = doc.addEventListener,
                add = modern ? 'addEventListener' : 'attachEvent',
                rem = modern ? 'removeEventListener' : 'detachEvent',
                pre = modern ? '' : 'on',
                init = function(e) {
                    if (e.type == 'readystatechange') if (doc.readyState != 'complete') return;
                    (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                    if (!done) { done = true; fn.call(win, e.type || e); }
                },
                poll = function() {
                    try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
                    init('poll');
                };
            if (doc.readyState == 'complete') fn.call(win, 'lazy');
            else {
                if (!modern) if (root.doScroll) {
                    try { top = !win.frameElement; } catch(e) { }
                    if (top) poll();
                }
                doc[add](pre + 'DOMContentLoaded', init, false);
                doc[add](pre + 'readystatechange', init, false);
                win[add](pre + 'load', init, false);
            }
        })(window, function() {
            window.booklyCustomerProfile(
                <?php echo json_encode( compact( 'container', 'ajaxurl' ) ) ?>
            );
        });
    </script>
<?php else : ?>
    <?php wp_login_form() ?>
<?php endif ?>