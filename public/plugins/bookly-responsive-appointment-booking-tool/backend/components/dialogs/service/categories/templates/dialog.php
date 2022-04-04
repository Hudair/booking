<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Elements;
?>
<form id="bookly-service-categories-modal" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'Categories', 'bookly' ) ?></h5>
                <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <ul id="bookly-services-categories" class="list-unstyled"></ul>
                <?php Buttons::renderAdd( 'bookly-js-new-category', null, __( 'Add category', 'bookly' ), array(), false ) ?>
                <small class="d-block text-muted mt-3"><?php esc_html_e( 'Adjust the order of categories in your booking form', 'bookly' ) ?></small>
            </div>
            <div class="modal-footer">
                <?php Buttons::renderSubmit() ?>
                <?php Buttons::renderCancel() ?>
            </div>
        </div>
    </div>
</form>
<div class="collapse" id="bookly-new-category-template">
    <li class="form-group">
        <div class="row align-items-center">
            <input type="hidden" name="category_id" value="{{id}}"/>
            <div class="col-auto"><?php Elements::renderReorder() ?></div>
            <div class="col-auto px-1"><input type="text" class="form-control" name="category_name" value="{{name}}"/></div>
            <div class="col-auto"><a href="#"><i class="far fa-fw fa-trash-alt text-danger bookly-js-delete-category" title="<?php esc_attr_e( 'Delete', 'bookly' ) ?>"></i></a></div>
        </div>
    </li>
</div>