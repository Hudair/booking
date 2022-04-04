<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<div class="form-group col-sm-4">
    <label<?php if ( $title == '' ) : ?> class="hidden-xs"<?php endif ?>><?php echo $title ?><br/></label>
    <div style="vertical-align: bottom;">
        <select class="form-control custom-select bookly-js-control-input" name="birthday[<?php echo $type ?>]" id="bookly_birthday_<?php echo $type ?>">
            <option value=""><?php echo esc_html( $empty ) ?></option>
            <?php foreach ( $options as $value => $option ) : ?>
                <option value="<?php echo $value ?>"<?php selected( $selected_value, $value ) ?>><?php echo esc_html( $option ) ?></option>
            <?php endforeach ?>
        </select>
    </div>
</div>