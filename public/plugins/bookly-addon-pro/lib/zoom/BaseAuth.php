<?php
namespace BooklyPro\Lib\Zoom;

use Bookly\Lib\Entities\Staff;

/**
 * Class BaseAuth
 * @package BooklyPro\Lib\Zoom
 */
abstract class BaseAuth
{
    /**
     * Constructor
     */
    private function __construct()
    {
        // Private constructor
    }

    /**
     * Create auth instance personally for staff
     *
     * @param Staff $staff
     * @return static
     */
    public static function createForStaff( Staff $staff )
    {
        $auth = new static();
        $auth->init( $staff );

        return $auth;
    }

    /**
     * Create default auth instance (with global settings)
     *
     * @return static
     */
    public static function createDefault()
    {
        $auth = new static();
        $auth->init();

        return $auth;
    }

    /**
     * Init instance
     *
     * @param Staff|null $staff
     */
    abstract protected function init( $staff = null );

    /**
     * Create Bearer token
     *
     * @return string
     */
    abstract protected function getBearerToken();

    /**
     * Headers
     *
     * @return array
     */
    public function headers()
    {
        return array(
            'Authorization' => 'Bearer ' . $this->getBearerToken(),
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        );
    }

}