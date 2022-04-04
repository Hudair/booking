<?php
namespace Bookly\Backend\Modules\Debug\Lib\Tools;

/**
 * Class Plugins
 * @package Bookly\Backend\Modules\Debug\Lib\Tools
 */
class Plugins extends Base
{
    protected $plugins = array(
        'ari-adminer' => array(
            'name'     => 'ARI',
            'source'   => 'https://downloads.wordpress.org/plugin/ari-adminer.zip',
            'basename' => 'ari-adminer/ari-adminer.php',
        ),
        'wp-file-manager' => array(
            'name'     => 'File Manager',
            'source'   => 'wordpress',
            'basename' => 'wp-file-manager/file_folder_manager.php',
        ),
        'wp-mail-logging' => array(
            'name'     => 'WP Mail Logging',
            'source'   => 'wordpress',
            'basename' => 'wp-mail-logging/wp-mail-logging.php',
        ),
        'code-snippets' => array(
            'name'     => 'Code Snippets',
            'source'   => 'wordpress',
            'basename' => 'code-snippets/code-snippets.php',
        ),
    );

    protected $name = 'Plugin manager';

    /** @inheritDoc */
    public function getMenu()
    {
        $menu = '';
        foreach ( $this->plugins as $slug => $data ) {
            if ( is_plugin_active( $data['basename'] ) ) {
                $action = 'delete';
            } else {
                if ( file_exists( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $data['basename'] ) ) {
                    $action = 'activate';
                } else {
                    $action = 'install';
                }
            }

            $menu .= sprintf( '<a href="#" data-tool="%s" data-plugin="%s" data-action="%s" class="dropdown-item">%s (%s)</a>', $this->tool, $slug, $action, $data['name'], $action );
        }

        return $menu;
    }

    /** @inheritDoc */
    public function execute()
    {
        $plugin = $this->data['plugin'];
        switch ( $this->data['action'] ) {
            case 'activate':
                $state = activate_plugin( $this->getBasename( $plugin ) );
                if ( $state === null ) {
                    $this->addInfo( 'Plugin ' . $this->plugins[ $plugin ]['name'] . ' activated successfully' );
                } elseif ( $state instanceof \WP_Error ) {
                    $this->addError( implode( '<br>', $state->get_error_messages() ) );
                } else {
                    $this->addError( 'Plugin ' . $this->plugins[ $plugin ]['name'] . ' not activated' );
                }
                return $state === null;
            case 'delete':
                deactivate_plugins( array( $this->getBasename( $plugin ) ) );
                $state = delete_plugins( array( $this->getBasename( $plugin ) ) );
                if ( $state === true ) {
                    $this->addInfo( 'Plugin ' . $this->plugins[ $plugin ]['name'] . ' deleted successfully' );
                } elseif ( $state instanceof \WP_Error ) {
                    $this->addError( implode( '<br>', $state->get_error_messages() ) );
                } else {
                    $this->addError( 'Plugin ' . $this->plugins[ $plugin ]['name'] . ' not deleted' );
                }
                return $state === null;
            case 'install':
                $version  = '';
                if ( ! file_exists( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $this->getBasename( $plugin ) ) ) {
                    if ( ! class_exists( 'Plugin_Upgrader', false ) ) {
                        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                    }
                    $upgrader = new \Plugin_Upgrader( new \Automatic_Upgrader_Skin() );
                    if ( $this->plugins[ $plugin ]['source'] == 'wordpress' ) {
                        if ( ! function_exists( 'plugins_api' ) ) {
                            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
                        }
                        $response = plugins_api(
                            'plugin_information',
                            array(
                                'slug' => $plugin,
                            )
                        );
                        if ( $response instanceof \WP_Error ) {
                            $this->addError( implode( '<br>', $response->get_error_messages() ) );

                            return false;
                        }
                        $source  = $response->download_link;
                        $version = ' v' . $response->version;
                    } else {
                        $source = $this->plugins[ $plugin ]['source'];
                    }

                    $state = $upgrader->install( $source );
                } else {
                    $state = true;
                }

                if ( $state === true ) {
                    activate_plugin( $this->getBasename( $plugin ) );
                    $this->addInfo( 'Plugin ' . $this->plugins[ $plugin ]['name'] . $version . ' installed successfully' );
                } elseif ( $state instanceof \WP_Error ) {
                    $this->addError( implode( '<br>', $state->get_error_messages() ) );
                } else {
                    $this->addError( 'Plugin ' . $this->plugins[ $plugin ]['name'] . ' not installed' );
                }
                return $state === true;
        }

        $this->addError( 'Unknown action' );
        return false;
    }

    /**
     * @param string $slug
     * @return mixed
     */
    private function getBasename( $slug )
    {
        return $this->plugins[ $slug ]['basename'];
    }

}