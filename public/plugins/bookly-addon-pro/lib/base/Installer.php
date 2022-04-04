<?php
namespace BooklyPro\Lib\Base;

/**
 * Class Installer
 * @package BooklyPro\Lib\Base
 */
abstract class Installer
{
    /** @var array */
    protected $options = array();
    /** @var string */
    private $prefix;

    /******************************************************************************************************************
     * Public methods                                                                                                 *
     ******************************************************************************************************************/

    /**
     * Install.
     */
    public function install()
    {
        $data_loaded_option_name = $this->getPrefix() . 'data_loaded';

        // Create tables and load data if it hasn't been loaded yet.
        if ( ! get_option( $data_loaded_option_name ) ) {
            $this->createTables();
            $this->loadData();
        }

        update_option( $data_loaded_option_name, '1' );
    }

    /**
     * Uninstall.
     */
    public function uninstall()
    {
        $this->removeData();
        $this->dropTables();
    }

    /**
     * Get options.
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Create tables.
     */
    public function createTables()
    {

    }

    /**
     * Drop tables (@see \Bookly\Backend\Modules\Debug\Ajax ).
     */
    public function dropTables()
    {
        foreach ( $this->getEmbeddedInstallers() as $installer ) {
            $installer->dropTables();
        }
        $this->drop( $this->getTables() );
    }

    /**
     * Load data.
     */
    public function loadData()
    {
        // Add default options.

        $plugin_prefix = $this->getPrefix();
        add_option( $plugin_prefix . 'data_loaded', '0' );
        add_option( $plugin_prefix . 'db_version',  $this->getVersion() );
        add_option( $plugin_prefix . 'installation_time', time() );
        add_option( $plugin_prefix . 'envato_purchase_code', '' );
        add_option( $plugin_prefix . 'grace_start', time() + 60 * DAY_IN_SECONDS );

        // Add plugin options.
        foreach ( $this->options as $name => $value ) {
            add_option( $name, $value );
            if ( strpos( $name, 'bookly_l10n_' ) === 0 ) {
                do_action( 'wpml_register_single_string', 'bookly', $name, $value );
            }
        }
    }

    /**
     * Remove data.
     */
    public function removeData()
    {
        foreach ( $this->getEmbeddedInstallers() as $installer ) {
            $installer->removeData();
        }

        // Remove options.
        foreach ( $this->options as $name => $value ) {
            delete_option( $name );
        }
        $plugin_prefix = $this->getPrefix();
        delete_option( $plugin_prefix . 'data_loaded' );
        delete_option( $plugin_prefix . 'db_version' );
        delete_option( $plugin_prefix . 'installation_time' );
        delete_option( $plugin_prefix . 'grace_start' );
        delete_option( $plugin_prefix . 'envato_purchase_code' );
    }

    /**
     * Get root namespace of called class.
     *
     * @return string
     */
    public static function getRootNamespace()
    {
        return strtok( __NAMESPACE__, '\\' );
    }

    /**
     * Get plugin entities
     *
     * @return array
     */
    public static function getEntityClasses()
    {
        $classes = array();
        $fs = self::getFilesystem();
        $files = $fs->dirlist( static::getDirectory() . '/lib/entities' );
        if ( $files ) {
            foreach ( $files as $file ) {
                $classes[] = static::getRootNamespace() . '\Lib\Entities\\' . basename( $file['name'], '.php' );
            }
        }

        return $classes;
    }

    /******************************************************************************************************************
     * Private methods                                                                                                *
     ******************************************************************************************************************/

    /**
     * Drop tables.
     *
     * @param array $tables
     */
    private function drop( array $tables )
    {
        if ( $tables ) {
            global $wpdb;

            $query_foreign_keys = sprintf(
                'SELECT TABLE_NAME, CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE REFERENCED_TABLE_SCHEMA = SCHEMA() AND REFERENCED_TABLE_NAME IN (%s)',
                implode( ', ', array_fill( 0, count( $tables ), '%s' ) )
            );

            $schema = $wpdb->get_results( $wpdb->prepare( $query_foreign_keys, $tables ) );
            foreach ( $schema as $foreign_key ) {
                $wpdb->query( "ALTER TABLE `$foreign_key->TABLE_NAME` DROP FOREIGN KEY `$foreign_key->CONSTRAINT_NAME`" );
            }

            $wpdb->query( 'DROP TABLE IF EXISTS `' . implode( '`, `', $tables ) . '` CASCADE;' );
        }
    }

    /**
     * Get path to add-on directory.
     *
     * @return string
     */
    private static function getDirectory()
    {
        return dirname( dirname( __DIR__ ) );
    }

    /**
     * Get options prefix.
     *
     * @return string
     */
    protected function getPrefix()
    {
        if ( $this->prefix === null ) {
            $this->prefix = str_replace( array( '-addon', '-' ), array( '', '_' ), basename( self::getDirectory() ) ) . '_';
        }

        return $this->prefix;
    }

    /**
     * Get plugin version.
     *
     * @return string
     */
    private function getVersion()
    {
        if ( ! function_exists( 'get_plugin_data' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $main_file   = self::getDirectory() . DIRECTORY_SEPARATOR . 'main.php';
        $plugin_data = get_plugin_data( $main_file, false, false );

        return $plugin_data['Version'];
    }

    /**
     * Get embedded add-ons installers.
     *
     * @return Installer[]
     */
    private function getEmbeddedInstallers()
    {
        static $installers;

        if ( $installers === null ) {
            $installers = array();
            $addons_path = self::getDirectory() . '/lib/addons/';
            if ( is_dir( $addons_path ) ) {
                foreach ( glob( $addons_path . 'bookly-addon-*', GLOB_ONLYDIR ) as $path ) {
                    include_once $path . '/autoload.php';
                    $namespace = implode( '', array_map( 'ucfirst', explode( '-', str_replace( '-addon-', '-', basename( $path ) ) ) ) );
                    $embedded_installer = '\\' . $namespace . '\Lib\Installer';
                    $installers[] = new $embedded_installer();
                }
            }
        }

        return $installers;
    }

    /**
     * Get plugin table names.
     *
     * @return array
     */
    private function getTables()
    {
        global $wpdb;
        $fs     = self::getFilesystem();
        $result = array();
        $dir    = self::getDirectory() . '/lib/entities';
        $files  = $fs->dirlist( $dir );

        if ( $files ) {
            $pattern = '/(static|protected)\s+\$table\s+=\s+\'bookly_(?<table>\w+)/i';

            foreach ( $files as $file ) {
                $source = $fs->get_contents( $dir . DIRECTORY_SEPARATOR . $file['name'] );
                preg_match_all( $pattern, $source, $matches, PREG_SET_ORDER, 0 );
                if ( $matches && array_key_exists( 'table', $matches[0] ) ) {
                    $result[] = $wpdb->prefix . 'bookly_' . $matches[0]['table'];
                }
            }
        }

        return $result;
    }

    /**
     * @return \WP_Filesystem_Direct
     */
    private static function getFilesystem()
    {
        // Emulate WP_Filesystem to avoid FS_METHOD and filters overriding "direct" type
        if ( ! class_exists( 'WP_Filesystem_Direct', false ) ) {
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        }

        return new \WP_Filesystem_Direct( null );
    }
}