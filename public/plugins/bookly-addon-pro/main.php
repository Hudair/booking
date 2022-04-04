<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
Plugin Name: Bookly Pro (Add-on)
Plugin URI: https://www.booking-wp-plugin.com/?utm_source=bookly_admin&utm_medium=plugins_page&utm_campaign=plugins_page
Description: Bookly Pro add-on allows you to use additional features and settings, and install other add-ons for Bookly plugin.
Version: 4.5
Author: Bookly
Author URI: https://www.booking-wp-plugin.com/?utm_source=bookly_admin&utm_medium=plugins_page&utm_campaign=plugins_page
Text Domain: bookly
Domain Path: /languages
License: Commercial
*/

global $wpdb;
// Remove user meta.
$meta_names = array( 'bookly_grace_hide_admin_notice_time', 'bookly_show_purchase_reminder' );
$wpdb->query( $wpdb->prepare( sprintf( 'DELETE FROM `' . $wpdb->usermeta . '` WHERE meta_key IN (%s)',
implode( ', ', array_fill( 0, count( $meta_names ), '%s' ) ) ), $meta_names ) );

add_action( 'plugins_loaded', function(){
remove_action( 'wp_ajax_bookly_update_plugin', [ 'Bookly\Lib\PluginsUpdater', 'updateAddon' ], 10 );
remove_action( 'wp_ajax_bookly_check_update', [ 'Bookly\Lib\PluginsUpdater', 'getAddonsUpdatingData' ], 10 );
remove_action( 'after_plugin_row', [ 'Bookly\Lib\PluginsUpdater', 'renderAfterPluginRow' ], 10 );
});

if ( ! function_exists( 'bookly_pro_loader' ) ) {
    include_once __DIR__ . '/autoload.php';

    BooklyPro\Lib\Boot::up();
}