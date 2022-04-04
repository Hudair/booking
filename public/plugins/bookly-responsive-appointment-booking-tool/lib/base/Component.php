<?php
namespace Bookly\Lib\Base;

use Bookly\Lib;

/**
 * Class Component
 * @package Bookly\Lib\Base
 */
abstract class Component extends Cache
{
    /**
     * Array of reflection objects of child classes.
     * @var \ReflectionClass[]
     */
    private static $reflections = array();

    /******************************************************************************************************************
     * Public methods                                                                                                 *
     ******************************************************************************************************************/

    /**
     * Get admin page slug.
     *
     * @return string
     */
    public static function pageSlug()
    {
        return 'bookly-' . str_replace( '_', '-', basename( static::directory() ) );
    }

    /**
     * Render a template file.
     *
     * @param string $template
     * @param array  $variables
     * @param bool   $echo
     * @return void|string
     */
    public static function renderTemplate( $template, $variables = array(), $echo = true )
    {
        extract( array( 'self' => get_called_class() ) );
        extract( $variables );

        // Start output buffering.
        ob_start();
        ob_implicit_flush( 0 );

        include static::directory() . '/templates/' . $template . '.php';

        if ( ! $echo ) {
            return ob_get_clean();
        }

        echo ob_get_clean();
    }

    /******************************************************************************************************************
     * Protected methods                                                                                              *
     ******************************************************************************************************************/

    /**
     * Verify CSRF token.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        return wp_verify_nonce( static::parameter( 'csrf_token' ), 'bookly' ) == 1;
    }

    /**
     * Get path to component directory.
     *
     * @return string
     */
    protected static function directory()
    {
        return dirname( static::reflection()->getFileName() );
    }

    /**
     * Enqueue scripts with wp_enqueue_script.
     *
     * @param array $sources
     */
    protected static function enqueueScripts( array $sources )
    {
        static::registerGlobalAssets();
        static::_enqueue( 'scripts', $sources );
    }

    /**
     * Enqueue styles with wp_enqueue_style.
     *
     * @param array $sources
     */
    protected static function enqueueStyles( array $sources )
    {
        static::registerGlobalAssets();
        static::_enqueue( 'styles', $sources );
    }

    /**
     * Check if there is a parameter with given name in the request.
     *
     * @param string $name
     * @return bool
     */
    protected static function hasParameter( $name )
    {
        return array_key_exists( $name, $_REQUEST );
    }

    /**
     * Get class reflection object.
     *
     * @return \ReflectionClass
     */
    protected static function reflection()
    {
        $class = get_called_class();
        if ( ! isset ( self::$reflections[ $class ] ) ) {
            self::$reflections[ $class ] = new \ReflectionClass( $class );
        }

        return self::$reflections[ $class ];
    }

    /**
     * Get request parameter by name.
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected static function parameter( $name, $default = null )
    {
        return static::hasParameter( $name ) ? stripslashes_deep( $_REQUEST[ $name ] ) : $default;
    }

    /**
     * Get all request parameters.
     *
     * @return mixed
     */
    protected static function parameters()
    {
        return stripslashes_deep( $_REQUEST );
    }

    /**
     * Get all POST parameters.
     *
     * @return mixed
     */
    protected static function postParameters()
    {
        return stripslashes_deep( $_POST );
    }

    /**
     * Register bookly-globals so that other assets can use them as dependency
     */
    protected static function registerGlobalAssets()
    {
        if ( ! ( wp_script_is( 'bookly-frontend-globals', 'registered' )
            || wp_script_is( 'bookly-backend-globals', 'registered' ) ) )
        {
            Component::_register( 'scripts', array(
                'backend' => array(
                    'bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                    'js/datatables.min.js' => array( 'jquery' ),
                    'js/moment.min.js' => array(),
                    'js/daterangepicker.js' => array( 'bookly-moment.min.js', 'jquery' ),
                    'js/dropdown.js' => array( 'jquery' ),
                    'js/alert.js' => array( 'jquery' ),
                ),
                'frontend' => array(
                    'js/spin.min.js' => array( 'jquery' ),
                    'js/ladda.min.js' => array( 'jquery' ),
                ),
                'alias' => array(
                    'bookly-frontend-globals' => array( 'bookly-spin.min.js', 'bookly-ladda.min.js' ),
                    'bookly-backend-globals' => array( 'bookly-bootstrap.min.js', 'bookly-datatables.min.js', 'bookly-daterangepicker.js', 'bookly-dropdown.js', 'bookly-alert.js', 'bookly-spin.min.js', 'bookly-ladda.min.js',  ),
                )
            ) );

            Component::_register( 'styles', array(
                'backend' => array( 'bootstrap/css/bootstrap.min.css', ),
                'frontend' => array( 'css/ladda.min.css', ),
                'alias' => array(
                    'bookly-frontend-globals' => array( 'bookly-ladda.min.css' ),
                    'bookly-backend-globals' => array( 'bookly-bootstrap.min.css', 'bookly-ladda.min.css' ),
                )
            ) );

            wp_localize_script( 'bookly-spin.min.js', 'BooklyL10nGlobal', Lib\Proxy\Shared::prepareL10nGlobal( array(
                'csrf_token' => Lib\Utils\Common::getCsrfToken(),
                'addons' => array(),
            ) ) );
        }
    }

    /******************************************************************************************************************
     * Private methods                                                                                                *
     ******************************************************************************************************************/

    /**
     * Register scripts or styles with wp_register_script/wp_register_style
     *
     * @param string $type
     * @param array $sources
     */
    private static function _register( $type, array $sources )
    {
        $func = $type == 'scripts' ? 'wp_register_script' : 'wp_register_style';
        static::_assets( $func, $sources );
    }

    /**
     * Enqueue scripts or styles with wp_enqueue_script/wp_enqueue_style
     *
     * @param string $type
     * @param array $sources
     */
    private static function _enqueue( $type, array $sources )
    {
        $func = $type == 'scripts' ? 'wp_enqueue_script' : 'wp_enqueue_style';
        static::_assets( $func, $sources );
    }

    /**
     * Process assets with given function
     *
     * @param callable $func
     * @param array $sources
     * array(
     *  resource_directory => array(
     *      file[ => deps],
     *      ...
     *  ),
     *  ...
     * )
     */
    private static function _assets( $func, array $sources )
    {
        $plugin_class   = Lib\Base\Plugin::getPluginFor( get_called_class() );
        $assets_version = $plugin_class::getVersion();

        foreach ( $sources as $source => $files ) {
            switch ( $source ) {
                case 'alias':
                case 'wp':
                    $path = false;
                    break;
                case 'backend':
                    $path = $plugin_class::getDirectory() . '/backend/resources/path';
                    break;
                case 'frontend':
                    $path = $plugin_class::getDirectory() . '/frontend/resources/path';
                    break;
                case 'module':
                    $path = static::directory() . '/resources/path';
                    break;
                case 'bookly':
                    $path = Lib\Plugin::getDirectory() . '/path';
                    $assets_version = Lib\Plugin::getVersion();
                    break;
                default:
                    $path = $source . '/path';
            }

            foreach ( $files as $key => $value ) {
                $file = is_array( $value ) ? $key : $value;
                $deps = is_array( $value ) ? $value : array();
                if ( $path === false ) {
                    call_user_func( $func, $file, false, $deps, $assets_version );
                } else {
                    call_user_func( $func, 'bookly-' . basename( $file ), plugins_url( $file, $path ), $deps, $assets_version );
                }
            }
        }
    }
}