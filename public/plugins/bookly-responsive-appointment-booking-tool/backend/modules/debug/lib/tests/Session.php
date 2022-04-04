<?php
namespace Bookly\Backend\Modules\Debug\Lib\Tests;

/**
 * Class Session
 * @package Bookly\Backend\Modules\Debug\Lib\Tests
 */
class Session extends Base
{
    protected $name = 'Check php session';

    /** @inheritDoc */
    public function execute()
    {
        if ( $this->data === null ) {
            $params = array(
                'action'    => 'bookly_run_test',
                'test_name' => 'Session',
                'test_data' => 'init',
            );

            $url = add_query_arg( $params, admin_url( 'admin-ajax.php' ) );
            $response = wp_remote_get( $url, array(
                'timeout' => 60,
            ) );
            $json = json_decode( wp_remote_retrieve_body( $response ), true );
            if ( isset( $json['success'] ) && $json['success'] ) {
                $params['test_data'] = 'check';
                $url = add_query_arg( $params, admin_url( 'admin-ajax.php' ) );
                $response = wp_remote_get( $url, array(
                    'timeout' => 60,
                    'cookies' => wp_remote_retrieve_cookies( $response ),
                ) );
                $json = json_decode( wp_remote_retrieve_body( $response ), true );
                if ( isset( $json['success'] ) && $json['success'] ) {
                    return true;
                }
            }
            $this->addError( 'Failed' );
            return false;
        } elseif ( $this->data === 'init' ) {
            @session_start();
            $_SESSION['bookly-test-session'] = 2;
            wp_send_json_success();
        } elseif ( $this->data === 'check' ) {
            @session_start();
            $_SESSION['bookly-test-session'] === 2
                ? wp_send_json_success()
                : wp_send_json_error();
        }

        return false;
    }

}