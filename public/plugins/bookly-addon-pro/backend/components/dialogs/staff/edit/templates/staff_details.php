<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Controls;
use Bookly\Backend\Components\Dialogs\Staff\Edit\Proxy;
/**
 * @var array $categories
 * @var array $gateways
 * @var BooklyLib\Entities\Staff $staff
 */
$accepted_gateways = json_decode( $staff->getGateways(), true );
?>
<div class="form-group">
    <label for="bookly-category"><?php esc_html_e( 'Category', 'bookly' ) ?></label>
    <select name="category_id" class="form-control custom-select" id="bookly-category">
        <option value="0"><?php esc_html_e( 'Uncategorized', 'bookly' ) ?></option>
        <?php foreach ( $categories as $category ) : ?>
            <option value="<?php echo $category['id'] ?>" <?php selected( $category['id'], $staff->getCategoryId() ) ?>><?php echo esc_html( $category['name'] ) ?></option>
        <?php endforeach ?>
    </select>
</div>
<div class='form-group'>
    <?php Controls\Inputs::renderRadioGroup( __( 'Available payment methods', 'bookly' ), null,
        array(
            'default' => array( 'title' => __( 'Default', 'bookly' ) ),
            'custom' => array( 'title' => __( 'Custom', 'bookly' ) ),
        ),
        $accepted_gateways === null ? 'default' : 'custom', array( 'name' => 'gateways' ) ) ?>
</div>
<div class="form-group border-left ml-4 pl-3">
    <ul id="bookly-js-gateways-list"
        data-icon-class='fas fa-hand-holding-usd'
        data-txt-select-all="<?php esc_attr_e( 'All methods', 'bookly' ) ?>"
        data-txt-all-selected="<?php esc_attr_e( 'All methods', 'bookly' ) ?>"
        data-txt-nothing-selected="<?php esc_attr_e( 'No methods selected', 'bookly' ) ?>"
    >
        <?php foreach ( $gateways as $gateway => $title ): ?>
            <li data-input-name="gateways_list[]" data-value="<?php echo $gateway ?>" data-selected="<?php echo (int) ( $accepted_gateways ? in_array( $gateway, $accepted_gateways ) : true ) ?>">
                <?php echo esc_html( $title ) ?>
            </li>
        <?php endforeach ?>
    </ul>
    <?php Proxy\CustomerGroups::renderPaymentGatewaysHelp() ?>
</div>