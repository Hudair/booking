<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="alert alert-info my-2">
    <div class="d-flex">
        <div class="mr-3"><i class="fas fa-info-circle fa-2x"></i></div>
        <div class="flex-fill">
            <div>
                <?php esc_html_e( 'The booking form on this step may have different set or states of its elements. It depends on various conditions such as installed/activated add-ons, settings configuration or choices made on previous steps. Select option and click on the underlined text to edit.', 'bookly' ) ?>
            </div>
            <div class="mt-2">
                <select id="bookly-payment-step-view" class="form-control custom-select">
                    <option value="single-app"><?php esc_html_e( 'Form view in case of single booking', 'bookly' ) ?></option>
                    <option value="several-apps"><?php esc_html_e( 'Form view in case of multiple booking', 'bookly' ) ?></option>
                    <option value="100percents-off-price"><?php esc_html_e( 'Form in case of 100% discount', 'bookly' ) ?></option>
                    <option value="without-intersected-gateways"><?php esc_html_e( 'Form view if there are no common payment methods for client and staff', 'bookly' ) ?></option>
                </select>
            </div>
        </div>
    </div>
</div>