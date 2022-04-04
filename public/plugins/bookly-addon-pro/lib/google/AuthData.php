<?php
namespace BooklyPro\Lib\Google;

/**
 * Class BaseAuthData
 * @package BooklyPro\Lib\Google
 */
abstract class BaseAuthData
{
    /**
     * Set data from array.
     *
     * @param array $data
     * @return $this
     */
    protected function _setData( array $data )
    {
        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $this->{$key}->_setData( $value );
            } else {
                $this->{$key} = $value;
            }
        }

        return $this;
    }
}

/**
 * Class AuthData
 * @package BooklyPro\Lib\Google
 */
class AuthData extends BaseAuthData
{
    /** @var string */
    public $token;

    /** @var AuthDataCalendar */
    public $calendar;

    /** @var AuthDataChannel */
    public $channel;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->calendar = new AuthDataCalendar();
        $this->channel  = new AuthDataChannel();
    }

    /**
     * Create from JSON string.
     *
     * @param string $json
     * @return static
     */
    public static function fromJson( $json )
    {
        $auth_data = new static();

        return $auth_data->_setData( json_decode( $json, true ) );
    }

    /**
     * Convert to JSON string.
     *
     * @return string
     */
    public function toJson()
    {
        return json_encode( $this );
    }
}

/**
 * Class AuthDataCalendar
 * @package BooklyPro\Lib\Google
 */
class AuthDataCalendar extends BaseAuthData
{
    /** @var string */
    public $id;

    /** @var string */
    public $sync_token;
}

/**
 * Class AuthDataChannel
 * @package BooklyPro\Lib\Google
 */
class AuthDataChannel extends BaseAuthData
{
    /** @var string */
    public $id;

    /** @var string */
    public $resource_id;

    /** @var int */
    public $expiration;
}