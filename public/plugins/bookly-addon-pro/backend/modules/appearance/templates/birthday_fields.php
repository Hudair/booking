<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Editable\Elements;
?>

<div class="bookly-form-group">
    <?php Elements::renderLabel( $editable ) ?>
    <div>
        <select class="bookly-js-select-birthday-<?php echo $type ?>">
            <option value="" data-option="bookly_l10n_option_<?php echo $type ?>"><?php echo esc_html( $empty ) ?></option>
            <?php foreach ( $options as $value => $option ) : ?>
                <option value="<?php echo $value ?>"><?php echo esc_html( $option ) ?></option>
            <?php endforeach ?>
        </select>
    </div>
</div>