<?php
namespace BooklyPro\Lib;

/**
 * Class Boot
 * @package BooklyPro\Lib
 */
class Boot
{
    public static $plugin_title = 'Bookly Pro (Add-on)';
    public static $req_plugin_class = 'Bookly\Lib\Plugin';
    public static $req_version = '20.5';

    const ENV_OK              = 0;
    const ENV_NO_BOOKLY       = 1;
    const ENV_LEGACY_BOOKLY   = 2;
    const ENV_OLD_BOOKLY      = 3;
    const ENV_BOOKLY_INACTIVE = 4;

    /**
     * Boot up.
     */
    public static function up()
    {
        $main_file = self::mainFile();
        $plugin    = self::pluginClass();

        // Register activation/deactivation hooks.
        register_activation_hook( $main_file, function ( $network_wide ) use ( $plugin ) {
            // Enable collecting stats if Pro installed earlier than Bookly.
            if ( get_option( 'bookly_gen_collect_stats' ) === false ) {
                add_option( 'bookly_gen_collect_stats', '1' );
                foreach ( get_users( 'role=administrator' ) as $user ) {
                    add_user_meta( $user->ID, 'bookly_dismiss_collect_stats_notice', '1' );
                    add_user_meta( $user->ID, 'bookly_show_collecting_stats_notice', '1' );
                }
            }

            $env = Boot::checkEnv();

            if ( $env == Boot::ENV_NO_BOOKLY ) {
                // Install Bookly if it is missing.
                if ( Boot::installBookly( $network_wide ) ) {
                    $env = Boot::checkEnv();
                }
            }

            if ( $env == Boot::ENV_OK ) {
                $plugin::activate( $network_wide );
            }
        } );
        register_deactivation_hook( $main_file, function ( $network_wide ) use ( $plugin ) {
            if ( Boot::checkEnv() == Boot::ENV_OK ) {
                $plugin::deactivate( $network_wide );
            }
        } );
        register_uninstall_hook( $main_file, array( __CLASS__, 'uninstall' ) );

        // Run plugin.
        add_action( 'plugins_loaded', function () use ( $plugin, $main_file ) {
            $env = Boot::checkEnv();
            if ( $env == Boot::ENV_OK ) {
                $plugin::run();
            } else {
                // Deactivate plugin.
                add_action( 'init', function () use ( $main_file, $env ) {
                    if ( current_user_can( 'activate_plugins' ) ) {
                        add_action( 'admin_init', function () use ( $main_file ) {
                            deactivate_plugins( $main_file, false, is_network_admin() );
                        } );
                        add_action( is_network_admin() ? 'network_admin_notices' : 'admin_notices', function () use ( $env ) {
                            if ( $env == Boot::ENV_LEGACY_BOOKLY ) {
                                printf( '<div class="updated"><h3>%s</h3><p>The plugin has been <strong>deactivated</strong>.</p><p>It seems you have an outdated version of Bookly. We\'ve changed the plugin\'s architecture to improve its quality and stability (read more <a href="https://www.booking-wp-plugin.com/bookly-major-update/" target="_blank">here</a>).</p><p>Please update Bookly in Plugins section of your WordPress Dashboard.</p></div>',
                                    Boot::$plugin_title
                                );
                            } else {
                                printf( '<div class="updated"><h3>%s</h3><p>The plugin has been <strong>deactivated</strong>.</p><p><strong>Bookly v%s</strong> is required.</p></div>',
                                    Boot::$plugin_title,
                                    Boot::$req_version
                                );
                            }
                        } );
                        unset ( $_GET['activate'], $_GET['activate-multi'] );
                    }
                } );
            }
        }, 9, 1 );
    }

    /**
     * Download and install Bookly.
     *
     * @param bool $network_wide
     * @return bool
     */
    public static function installBookly( $network_wide )
    {
        if ( ! function_exists( 'plugins_api' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        }
        $response = plugins_api(
            'plugin_information',
            array(
                'slug'   => 'bookly-responsive-appointment-booking-tool',
                'fields' => array(
                    'sections' => false,
                    'versions' => true,
                ),
            )
        );
        if ( ! is_wp_error( $response ) && property_exists( $response, 'versions' ) ) {
            if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            }
            $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
            $source   = array_key_exists( self::$req_version, $response->versions )
                ? $response->versions[ self::$req_version ] // required version
                : $response->download_link;                 // last version
            if ( $upgrader->install( $source ) === true ) {
                activate_plugin( 'bookly-responsive-appointment-booking-tool/main.php', '', $network_wide );

                return true;
            }
        }

        return false;
    }

    /**
     * Check environment.
     *
     * @return int
     */
    public static function checkEnv()
    {
        if ( ! class_exists( self::$req_plugin_class ) ) {
            if ( ! function_exists( 'get_plugins' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }
            $plugins = get_plugins();
            if ( isset ( $plugins['bookly-responsive-appointment-booking-tool/main.php'] ) ) {
                return version_compare( $plugins['bookly-responsive-appointment-booking-tool/main.php']['Version'], self::$req_version, '>=' )
                    ? self::ENV_BOOKLY_INACTIVE
                    : self::ENV_OLD_BOOKLY;
            }
            if ( isset ( $plugins['appointment-booking/main.php'] ) ) {
                return self::ENV_LEGACY_BOOKLY;
            }

            return self::ENV_NO_BOOKLY;
        }

        return version_compare( call_user_func( array( self::$req_plugin_class, 'getVersion' ) ), self::$req_version, '>=' )
            ? self::ENV_OK
            : (
                call_user_func( array( self::$req_plugin_class, 'getBasename' ) ) == 'appointment-booking/main.php'
                    ? self::ENV_LEGACY_BOOKLY
                    : self::ENV_OLD_BOOKLY
            );
    }

    /**
     * Uninstall plugin.
     *
     * @param $network_wide
     */
    public static function uninstall( $network_wide )
    {
        if ( $network_wide !== false && has_action( 'bookly_plugin_uninstall' ) ) {
            $slug = basename( dirname( __DIR__ ) );
            do_action( 'bookly_plugin_uninstall', $slug );
        } else {
            /** @var Base\Installer $installer */
            $installer_class = strtok( __NAMESPACE__, '\\' ) . '\Lib\Installer';
            $installer = new $installer_class();
            $installer->uninstall();
        }
    }

    /**
     * Get path to plugin main file.
     *
     * @return string
     */
    public static function mainFile()
    {
        return dirname( __DIR__ ) . '/main.php';
    }

    /**
     * Get plugin class.
     *
     * @return \Bookly\Lib\Base\Plugin
     */
    public static function pluginClass()
    {
        return strtok( __NAMESPACE__, '\\' ) . '\Lib\Plugin';
    }
}