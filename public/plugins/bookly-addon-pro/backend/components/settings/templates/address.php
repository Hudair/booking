<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Elements;
/**
 * @var string $field_name
 * @var string $label
 * @var bool $showed
 */
?>
<div class="form-row mb-1">
    <div class="col-auto">
        <?php Elements::renderReorder() ?>
    </div>
    <div class="col">
        <div class="custom-control custom-checkbox">
            <input type="hidden" name="bookly_cst_address_show_fields[<?php echo $field_name ?>][show]" value="0" />
            <input type="checkbox" class="custom-control-input"
                   id="bookly_cst_address_show_fields_<?php echo $field_name ?>"
                   name="bookly_cst_address_show_fields[<?php echo $field_name ?>][show]"
                   value="1" <?php checked( $showed, true ) ?>
            />
            <label class="custom-control-label" for="bookly_cst_address_show_fields_<?php echo $field_name ?>">
                <?php echo $label ?>
            </label>
        </div>
    </div>
</div>