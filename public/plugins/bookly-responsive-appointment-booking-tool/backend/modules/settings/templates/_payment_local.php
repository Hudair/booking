<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Settings\Selects;
use Bookly\Backend\Components\Controls\Elements;
?>
<div class="card bookly-collapse" data-slug="local">
    <div class="card-header d-flex align-items-center">
        <?php Elements::renderReorder() ?>
        <a href="#bookly_pmt_locally" class="ml-2" role="button" data-toggle="collapse">
            <?php esc_html_e( 'Service paid locally', 'bookly' ) ?>
        </a>
    </div>
    <div id="bookly_pmt_locally" class="collapse show">
        <div class="card-body">
            <?php Selects::renderSingle( 'bookly_pmt_local' ) ?>
        </div>
    </div>
</div>
