<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
/** @var string $field_name */
/** @var bool $hidden */
?>
<div class="bookly-box<?php if ( isset( $hidden ) && $hidden ) : ?> bookly-none<?php endif ?>">
    <div class="bookly-form-group">
        <label><?php echo BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_' . $field_name ) ?></label>
        <div>
            <input type="text"
                   class="<?php echo 'bookly-js-address-' . $field_name ?>"
                   value="<?php echo esc_attr( $field_value ) ?>"
                   maxlength="255"/>
        </div>
        <div class="<?php echo 'bookly-js-address-' . $field_name . '-error' ?> bookly-label-error"></div>
    </div>
</div>
