<?php
namespace BooklyPro\Frontend\Modules\CustomerProfile;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Stat;
use BooklyPro\Lib;

/**
 * Class ShortCode
 * @package BooklyPro\Frontend\Modules\CustomerProfile
 */
class ShortCode extends BooklyLib\Base\Component
{
    /**
     * Init component.
     */
    public static function init()
    {
        // Register short code.
        add_shortcode( 'bookly-appointments-list', array( __CLASS__, 'render' ) );

        // Assets.
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'linkStyles' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'linkScripts' ) );
    }

    /**
     * Link styles.
     */
    public static function linkStyles()
    {
        if (
            get_option( 'bookly_gen_link_assets_method' ) == 'enqueue' ||
            BooklyLib\Utils\Common::postsHaveShortCode( 'bookly-appointments-list' )
        ) {
            self::enqueueStyles( array(
                'module' => array( 'css/customer-profile.css' => array( 'bookly-frontend-globals' ) ),
            ) );
        }
    }

    /**
     * Link scripts.
     */
    public static function linkScripts()
    {
        if (
            get_option( 'bookly_gen_link_assets_method' ) == 'enqueue' ||
            BooklyLib\Utils\Common::postsHaveShortCode( 'bookly-appointments-list' )
        ) {
            self::enqueueScripts( array(
                'module' => array( 'js/customer-profile.js' => array( 'bookly-frontend-globals' ) ),
            ) );
            wp_localize_script( 'bookly-customer-profile.js', 'BooklyCustomerProfileL10n', array(
                'csrf_token' => BooklyLib\Utils\Common::getCsrfToken(),
                'show_more'  => __( 'Show more', 'bookly' ),
            ) );
        }
    }

    /**
     * Render shortcode.
     *
     * @param array $attributes
     * @return string
     */
    public static function render( $attributes )
    {
        global $sitepress;

        // Disable caching.
        BooklyLib\Utils\Common::noCache();

        $customer = new BooklyLib\Entities\Customer();
        $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) );
        if ( $customer->isLoaded() ) {
            $appointments = Lib\Utils\Common::translateAppointments( $customer->getUpcomingAppointments() );
            $expired      = $customer->getPastAppointments( 1, 1 );
            $more   = ! empty ( $expired['appointments'] );
        } else {
            $appointments = array();
            $more   = false;
        }

        // Prepare URL for AJAX requests.
        $ajaxurl = admin_url( 'admin-ajax.php' );

        // Support WPML.
        if ( $sitepress instanceof \SitePress ) {
            $ajaxurl = add_query_arg( array( 'lang' => $sitepress->get_current_language() ) , $ajaxurl );
        }

        $titles = array();
        if ( @$attributes['show_column_titles'] ) {
            $titles = array(
                'category' => BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_category' ),
                'service' => BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ),
                'staff' => BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ),
                'date' => __( 'Date', 'bookly' ),
                'time' => __( 'Time', 'bookly' ),
                'price' => __( 'Price', 'bookly' ),
                'cancel' => __( 'Cancel', 'bookly' ),
                'online_meeting' => __( 'Online meeting', 'bookly' ),
                'status' => __( 'Status', 'bookly' ),
            );
            if ( BooklyLib\Config::customFieldsActive() && get_option( 'bookly_custom_fields_enabled' ) ) {
                foreach ( (array) BooklyLib\Proxy\CustomFields::getTranslated() as $field ) {
                    if ( ! in_array( $field->type, array( 'captcha', 'text-content', 'file' ) ) ) {
                        $titles[ $field->id ] = $field->label;
                    }
                }
            }
        }

        $url_cancel = add_query_arg( array( 'action' => 'bookly_cancel_appointment', 'csrf_token' => BooklyLib\Utils\Common::getCsrfToken() ) , $ajaxurl );
        if ( is_user_logged_in() ) {
            Stat::record( 'view_customer_profile', 1 );
        }

        return self::renderTemplate( 'short_code', compact( 'ajaxurl', 'appointments', 'attributes', 'url_cancel', 'titles', 'more' ), false );
    }
}