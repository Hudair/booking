<?php
namespace BooklyPro\Lib\Base;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib;

/**
 * Class Plugin
 * @package BooklyPro\Lib\Base
 */
abstract class Plugin
{
    /**
     * Register hooks.
     *
     * @param string $plugin_class
     */
    public static function registerHooks( $plugin_class )
    {
        /** @var BooklyLib\Base\Plugin $plugin_class */

        if ( is_admin() ) {
            if ( $plugin_class::getSlug() != BooklyLib\Plugin::getSlug() && ! $plugin_class::embedded() ) {
                add_filter( 'bookly_save_purchase_codes', function ( $errors, $purchase_codes, $blog_id ) use ( $plugin_class ) {
                    $option = $plugin_class::getPurchaseCodeOption();
                    if ( array_key_exists( $option, (array) $purchase_codes ) ) {
                        $purchase_code = preg_replace( '/[ \t\x00-\x1F\x7F-\xFF]/', '', $purchase_codes[ $option ] );
                        if ( $purchase_code == '' ) {
                            $plugin_class::updatePurchaseCode( '', $blog_id );
                        } else {
                            $result = Lib\API::verifyPurchaseCode( $purchase_code, $plugin_class, $blog_id );
                            if ( $result['valid'] ) {
                                $plugin_class::updatePurchaseCode( $purchase_code, $blog_id );
                                $grace_notifications = get_option( 'bookly_grace_notifications' );
                                $grace_notifications['add-ons'] = '0';
                                if ( $blog_id ) {
                                    update_blog_option( $blog_id, 'bookly_grace_notifications', $grace_notifications );
                                } else {
                                    update_option( 'bookly_grace_notifications', $grace_notifications );
                                }
                            } else {
                                if ( $purchase_code == $plugin_class::getPurchaseCode( $blog_id ) ) {
                                    $plugin_class::updatePurchaseCode( '', $blog_id );
                                }
                                $errors[] = $result['error'];
                            }
                        }
                    }

                    return $errors;
                }, 10, 3 );
            }
        }
    }

    /**
     * Init plugin update checker.
     *
     * @param BooklyLib\Base\Plugin $plugin_class
     */
    public static function initPluginUpdateChecker( $plugin_class )
    {
        include_once Lib\Plugin::getDirectory() . '/lib/utils/plugin-update-checker.php';

        $purchase_code = $plugin_class::getPurchaseCode();
        add_filter( 'puc_manual_check_link-' . $plugin_class::getSlug(), function () use ( $purchase_code ) {
            return $purchase_code != '' ? __( 'Check for updates', 'bookly' ) : '';
        }, 10, 1 );

        add_filter( 'puc_manual_check_message-' . $plugin_class::getSlug(), function ( $message, $status ) {
            switch ( $status ) {
                case 'no_update':        return __( 'This plugin is up to date.', 'bookly' );
                case 'update_available': return __( 'A new version of this plugin is available.', 'bookly' );
                default:                 return sprintf( __( 'Unknown update checker status "%s"', 'bookly' ), htmlentities( $status ) );
            }
        }, 10, 2 );

        add_filter( 'puc_request_info_result-' . $plugin_class::getSlug(), function ( $pluginInfo, $result ) use ( $plugin_class ) {
            if ( is_wp_error( $result ) ) {
                if ( get_option( 'bookly_api_server_error_time' ) == '0' ) {
                    update_option( 'bookly_api_server_error_time', current_time( 'timestamp' ) );
                }
            } elseif ( isset( $result['body'] ) ) {
                $response = json_decode( $result['body'], true );
                if ( isset( $response['options'] ) ) {
                    foreach ( $response['options'] as $option => $value ) {
                        $value !== null ? update_option( $option, $value ) : delete_option( $option );
                    }
                }
                update_option( 'bookly_api_server_error_time', '0' );
                if ( isset( $response['licensed'] ) && ! $response['licensed'] ) {
                    update_option( $plugin_class::getPurchaseCodeOption(), '' );
                }
                $bookly_update_plugins = (array) get_site_transient( 'bookly_update_plugins' );
                if ( isset( $response['inclusions'] ) ) {
                    $bookly_update_plugins[ $plugin_class::getSlug() ] = $response['inclusions'];
                } else {
                    unset( $bookly_update_plugins[ $plugin_class::getSlug() ] );
                }
                set_site_transient( 'bookly_update_plugins', $bookly_update_plugins );
            }

            return $pluginInfo;
        }, 10, 2 );

        add_filter( 'puc_request_info_query_args-' . $plugin_class::getSlug(), function( $queryArgs ) use ( $purchase_code ) {
            $queryArgs['site_url']      = site_url();
            $queryArgs['purchase_code'] = $purchase_code;
            $queryArgs['bookly']        = BooklyLib\Plugin::getVersion();
            $queryArgs['bookly-addon-pro'] = Lib\Plugin::getVersion();
            unset ( $queryArgs['checking_for_updates'] );

            return $queryArgs;
        }, 10, 1 );

        add_filter( 'puc_request_info_options-' . $plugin_class::getSlug(), function ( $options ) {
            $options['sslverify'] = false;

            return $options;
        }, 10, 1 );

        \PucFactory::buildUpdateChecker(
            BooklyLib\API::API_URL . '/1.2/plugins/' . $plugin_class::getSlug() . '/update',
            $plugin_class::getMainFile(),
            $plugin_class::getSlug(),
            24
        );

    }
}