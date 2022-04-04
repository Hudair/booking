<?php
namespace BooklyPro\Lib;

use Bookly\Lib as BooklyLib;

/**
 * Class API
 * @package BooklyPro\Lib
 */
abstract class API extends BooklyLib\API
{
    /**
     * Verify purchase code.
     *
     * @param string $purchase_code
     * @param BooklyLib\Base\Plugin $plugin_class
     * @param int|null $blog_id
     * @return array
     */
    public static function verifyPurchaseCode( $purchase_code, $plugin_class, $blog_id = null )
    {
    return array( 'valid' => true, );
        $url = add_query_arg(
            array(
                'purchase_code' => $purchase_code,
                'site_url'      => get_site_url( $blog_id ),
            ),
            self::API_URL . '/1.0/plugins/' . $plugin_class::getSlug() . '/purchase-code'
        );
        $response = wp_remote_get( $url, array(
            'sslverify' => false,
            'timeout'   => 25,
        ) );

        if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
            $json = json_decode( $response['body'], true );
            if ( isset( $json['success'] ) ) {
                if ( (bool) $json['success'] ) {
                    return array( 'valid' => true, );
                } elseif ( isset ( $json['error'] ) ) {
                    switch ( $json['error'] ) {
                        case 'already_in_use':
                            return array(
                                'valid' => false,
                                'error' => sprintf(
                                    __( '%s is used on another domain %s.<br/>In order to use the purchase code on this domain, please dissociate it in the admin panel of the other domain.<br/>If you do not have access to the admin area, please contact our technical support at support@bookly.info to transfer the license manually.', 'bookly' ),
                                    $purchase_code,
                                    isset ( $json['data'] ) ? implode( ', ', $json['data'] ) : ''
                                ),
                            );
                        case 'connection':
                            // ... Please try again later.
                            break;
                        case 'invalid':
                        default:
                            return array(
                                'valid' => false,
                                'error' => sprintf(
                                    __( '%s is not a valid purchase code for %s.', 'bookly' ),
                                    $purchase_code,
                                    $plugin_class::getTitle()
                                ),
                            );
                    }
                }
            }
        }

        return array(
            'valid' => false,
            'error' => __( 'Purchase code verification is temporarily unavailable. Please try again later.', 'bookly' )
        );
    }

    /**
     * Get purchase code data.
     *
     * @param string $purchase_code
     * @param BooklyLib\Base\Plugin $plugin_class
     * @param null $blog_id
     */
    public static function getPurchaseCodeData( $purchase_code, $plugin_class, $blog_id = null )
    {
        $url = add_query_arg(
            array(
                'purchase_code' => $purchase_code,
                'site_url'      => get_site_url( $blog_id ),
            ),
            self::API_URL . '/1.2/plugins/' . $plugin_class::getSlug() . '/purchase-code-data'
        );
        $response = wp_remote_get( $url, array(
            'sslverify' => false,
            'timeout'   => 25,
        ) );

        $json = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $json['status'] ) ) {
            return $json;
        }

        return array(
            'status' => 'connection',
        );
    }

    /**
     * Verify support for purchase code.
     *
     * @param string                $purchase_code
     * @param BooklyLib\Base\Plugin $plugin_class
     * @param int|null              $blog_id
     * @return array
     */
    public static function verifySupport( $purchase_code, $plugin_class, $blog_id = null )
    {
        $url = add_query_arg(
            array(
                'purchase_code' => $purchase_code,
                'site_url'      => get_site_url( $blog_id ),
            ),
            self::API_URL . '/1.1/plugins/' . $plugin_class::getSlug() . '/support'
        );
        $response = wp_remote_get( $url, array(
            'sslverify' => false,
            'timeout'   => 25,
        ) );

        $json = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( isset( $json['success'] ) ) {
            if ( (bool) $json['success'] ) {
                return array( 'valid' => true, );
            } elseif ( isset ( $json['error'] ) ) {
                switch ( $json['error'] ) {
                    case 'connection':
                        // ... Please try again later.
                        break;
                    case 'expired':
                        return array(
                            'valid' => false,
                            'message' => sprintf(
                                __( 'Your support period has expired on %s.', 'bookly' ),
                                array_key_exists( 'supported_until', $json )
                                    ? BooklyLib\Utils\DateTime::formatDate( $json['supported_until'] )
                                    : ''
                            ),
                        );
                        break;
                }
            }
        }

        return array(
            'valid' => false,
            'message' => __( 'Purchase code verification is temporarily unavailable. Please try again later.', 'bookly' )
        );
    }

    /**
     * Detach purchase code from current domain.
     *
     * @param BooklyLib\Base\Plugin $plugin_class
     * @param int|null $blog_id
     * @return bool
     */
    public static function detachPurchaseCode( $plugin_class, $blog_id = null )
    {
    return true;
        $url = add_query_arg(
            array(
                'site_url' => get_site_url( $blog_id ),
            ),
            self::API_URL . '/1.0/purchase-code/' . $plugin_class::getPurchaseCode( $blog_id )
        );

        $response = wp_remote_request( $url, array(
            'method'    => 'DELETE',
            'sslverify' => false,
            'timeout'   => 25,
        ) );

        if ( ! is_wp_error( $response ) && isset( $response['body'] ) ) {
            $json = json_decode( $response['body'], true );
            if ( isset ( $json['success'] ) && $json['success'] ) {
                return true;
            }
        }

        return false;
    }
}