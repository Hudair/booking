<?php
namespace BooklyPro\Backend\Components\Gutenberg\CancellationConfirmation;

use Bookly\Lib as BooklyLib;

/**
 * Class Block
 * @package BooklyPro\Backend\Components\Gutenberg\CancellationConfirmation
 */
class Block extends BooklyLib\Base\Block
{
    /**
     * @inheritDoc
     */
    public static function registerBlockType()
    {
        self::enqueueScripts( array(
            'module' => array(
                'js/cancellation-confirmation-block.js' => array( 'wp-blocks', 'wp-components', 'wp-element', 'wp-editor' ),
            ),
        ) );

        wp_localize_script( 'bookly-cancellation-confirmation-block.js', 'BooklyCancellationConfirmationL10n', array(
            'block' => array(
                'title'       => 'Bookly - ' . __( 'Cancellation confirmation', 'bookly' ),
                'description' => __( 'A custom block for displaying cancellation confirmation', 'bookly' ),
            ),
            'reason' => __( 'Show cancellation reason', 'bookly' ),
        ) );

        register_block_type( 'bookly/cancellation-confirmation-block', array(
            'editor_script' => 'bookly-cancellation-confirmation-block.js',
        ) );
    }
}