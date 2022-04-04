<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Ace;
use Bookly\Backend\Modules\Settings\Codes;
/**
 * @var array $goods
 * @var array $service
 */
?>
<div class="bookly-js-service-wc-container">
    <div class='form-group'>
        <label for='bookly_wc_product'><?php esc_html_e( 'Booking product', 'bookly' ) ?></label>
        <select id="bookly_wc_product" class="form-control custom-select" name="wc_product_id">
            <?php foreach ( $goods as $item ) : ?>
                <option value="<?php echo $item['id'] ?>" <?php selected( $service['wc_product_id'], $item['id'] ) ?>>
                    <?php echo esc_html( $item['name'] ) ?>
                </option>
            <?php endforeach ?>
        </select>
    </div>
    <div id="bookly-js-wc-settings">
        <div class='form-group'>
            <label for='bookly_wc_cart_info_name'><?php esc_attr_e( 'Cart item data', 'bookly' ) ?></label>
            <input type='text' id='bookly_wc_cart_info_name' class='form-control' name='wc_cart_info_name' value='<?php esc_attr_e( $service['wc_cart_info_name'] ) ?>'>
        </div>

        <?php Ace\Editor::render( 'bookly-settings-woo-commerce', 'bookly_wc_cart_info', Codes::getJson( 'woocommerce' ), $service['wc_cart_info'] ) ?>
        <input type="hidden" name="wc_cart_info" value="<?php echo esc_attr( $service['wc_cart_info'] ) ?>">
    </div>
</div>