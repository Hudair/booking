<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Settings\Inputs;
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Ace;
use Bookly\Backend\Modules\Settings\Codes;
?>
<div class="tab-pane" id="bookly_settings_woo_commerce">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'woo_commerce' ) ) ?>" id="woocommerce">
        <div class="card-body">
            <div class="form-group">
                <h4><?php esc_html_e( 'Instructions', 'bookly' ) ?></h4>
                <p>
                    <?php _e( 'You need to install and activate WooCommerce plugin before using the options below.<br/><br/>Once the plugin is activated do the following steps:', 'bookly' ) ?>
                </p>
                <ol>
                    <li><?php esc_html_e( 'Create a product in WooCommerce that can be placed in cart.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'In the form below enable WooCommerce option.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'Select the product that you created at step 1 in the drop down list of products.', 'bookly' ) ?></li>
                    <li><?php esc_html_e( 'If needed, edit item data which will be displayed in the cart. Besides cart item data Bookly passes address and account fields into WooCommerce if you collect them in your booking form.', 'bookly' ) ?></li>
                </ol>
                <p>
                    <?php esc_html_e( 'Note that once you have enabled WooCommerce option in Bookly the built-in payment methods will no longer work. All your customers will be redirected to WooCommerce cart instead of standard payment step.', 'bookly' ) ?>
                </p>
            </div>

            <?php Selects::renderSingle( 'bookly_wc_enabled', 'WooCommerce' ) ?>
            <?php if ( $wc_warning ): ?>
                <div class='alert alert-danger form-group my-n2 p-1'><i class='fas pl-1 fa-times'></i> <?php echo $wc_warning ?></div>
            <?php endif ?>
            <div class="form-group border-left mt-3 ml-4 pl-3 bookly_wc_enabled-related">
                <div class="form-group">
                    <label for="bookly_wc_product"><?php esc_html_e( 'Booking product', 'bookly' ) ?></label>
                    <select id="bookly_wc_product" class="form-control custom-select" name="bookly_wc_product">
                        <?php foreach ( $goods as $item ) : ?>
                            <option value="<?php echo $item['id'] ?>" <?php selected( get_option( 'bookly_wc_product' ), $item['id'] ) ?>>
                                <?php echo $item['name'] ?>
                            </option>
                        <?php endforeach ?>
                    </select>
                </div>

                <?php Inputs::renderText( 'bookly_l10n_wc_cart_info_name', __( 'Cart item data', 'bookly' ) ) ?>
                <?php Ace\Editor::render( 'bookly-settings-woo-commerce', 'bookly_wc_cart_info', Codes::getJson( 'woocommerce' ), get_option( 'bookly_l10n_wc_cart_info_value', '' ) ) ?>
                <input type="hidden" name="bookly_l10n_wc_cart_info_value" value="<?php echo esc_attr( get_option( 'bookly_l10n_wc_cart_info_value', '' ) ) ?>">
            </div>
        </div>

        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>