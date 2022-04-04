<?php
namespace BooklyPro\Frontend\Modules\CancellationConfirmation;

use Bookly\Lib as BooklyLib;

/**
 * Class Controller
 * @package BooklyPro\Frontend\Modules\CancellationConfirmation
 */
class ShortCode extends BooklyLib\Base\Component
{
    /**
     * Init component.
     */
    public static function init()
    {
        // Register short code.
        add_shortcode( 'bookly-cancellation-confirmation', array( __CLASS__, 'render' ) );
    }

    /**
     * Render shortcode.
     *
     * @param array $attributes
     * @return string
     */
    public static function render( $attributes )
    {
        // Disable caching.
        BooklyLib\Utils\Common::noCache();

        // Prepare URL for AJAX requests.
        $ajax_url = admin_url( 'admin-ajax.php' );

        $token = self::parameter( 'bookly-appointment-token', '' );

        return self::renderTemplate( 'short_code', compact( 'ajax_url', 'token', 'attributes' ), false );
    }
}