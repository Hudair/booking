<?php
namespace BooklyPro\Lib\Zoom;

/**
 * Class Authentication
 * @package BooklyPro\Lib\Zoom
 */
abstract class Authentication
{
    const TYPE_DEFAULT = 'default';
    const TYPE_JWT     = 'jwt';
    const TYPE_OAuth   = 'oauth';
}