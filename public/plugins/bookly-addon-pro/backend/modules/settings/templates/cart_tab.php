<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Controls\Elements;
use Bookly\Lib\Config;
?>
<div class="tab-pane" id="bookly_settings_cart">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'cart' ) ) ?>">
        <div class="card-body">
            <div class="form-group">
                <label for="bookly_cart_show_columns"><?php esc_html_e( 'Columns', 'bookly' ) ?></label><br/>
                <div id="bookly_cart_show_columns">
                    <?php foreach ( (array) get_option( 'bookly_cart_show_columns' ) as $column => $attr ) : ?>
                        <div class="form-row mb-1"<?php if ( ( $column == 'deposit' && ! Config::depositPaymentsActive() )
                            || ( $column == 'tax' && ! Config::taxesActive() ) ) : ?> style="display:none"<?php endif ?>>
                            <div class="col-auto">
                                <?php Elements::renderReorder() ?>
                            </div>
                            <div class="col">
                                <div class="custom-control custom-checkbox">
                                    <input type="hidden" name="bookly_cart_show_columns[<?php echo $column ?>][show]" value="0" />
                                    <input type="checkbox" class="custom-control-input"
                                           id="bookly_cart_show_columns_<?php echo $column ?>"
                                           name="bookly_cart_show_columns[<?php echo $column ?>][show]"
                                           value="1" <?php checked( $attr['show'], true ) ?>
                                    />
                                    <label class="custom-control-label" for="bookly_cart_show_columns_<?php echo $column ?>">
                                        <?php echo isset( $cart_columns[ $column ] ) ? $cart_columns[ $column ] : '' ?>
                                    </label>
                                </div>
                            </div>
                        </div>
                    <?php endforeach ?>
                </div>
                <small class="text-muted form-text">
                    <?php if ( Config::cartActive() ) : ?>
                        <?php esc_html_e( 'Select columns that you want to display in a cart summary before the booking confirmation. Uncheck the box to hide the column. Drag the sandwich icon to change the order of fields.', 'bookly' ) ?>
                    <?php else : ?>
                        <?php esc_html_e( 'If you use {cart_info} code in notifications, you can select the columns that you want to display and set the order of fields here. Uncheck the box to hide the column.', 'bookly' ) ?>
                    <?php endif ?>
                </small>
            </div>
        </div>

        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php Inputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>