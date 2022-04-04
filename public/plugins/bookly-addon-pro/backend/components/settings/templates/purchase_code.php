<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="form-group">
    <label for="<?php echo $plugin_class::getPurchaseCodeOption() ?>"><?php echo $plugin_class::getTitle() ?> <?php esc_html_e( 'Purchase Code', 'bookly' ) ?></label>
    <input id="<?php echo $plugin_class::getPurchaseCodeOption() ?>" class="purchase-code form-control" type="text" name="purchase_code[<?php echo $plugin_class::getPurchaseCodeOption() ?>]" value="<?php echo $purchase_code ?>"<?php echo $blog ?>/>
    <?php if ( $purchase_code != '' ): ?>
        <small class="text-muted form-text">
            <?php printf(
                __( '<a class="%s" href="#">Click here</a> to dissociate this purchase code from the current domain (use to move the plugin to another site).', 'bookly' ), 'bookly-js-detach-pc'
            ) ?>
        </small>
    <?php endif ?>
</div>