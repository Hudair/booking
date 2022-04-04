<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="bookly-tbs">
    <div class="bookly-modal-backdrop show bookly-fade bookly-board-backdrop">
        <div class="card p-4 bookly-js-board bookly-board">
            <div class="h4"><?php esc_html_e( 'License verification required', 'bookly' ) ?></div>
            <div>
                <?php echo $board_body ?>
            </div>
        </div>
    </div>
</div>