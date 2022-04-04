<?php
namespace Bookly\Backend\Components\Notices;

use Bookly\Lib;

/**
 * Class Limitation
 * @package Bookly\Backend\Components\Notices
 */
class Limitation extends Lib\Base\Component
{
    /**
     * Render limitation notice.
     */
    public static function forNewService()
    {
        return self::renderTemplate( 'limitation_service', array(), false );
    }

    /**
     * Render limitation notice.
     */
    public static function forNewStaff()
    {
        return self::renderTemplate( 'limitation_staff', array(), false );
    }
}