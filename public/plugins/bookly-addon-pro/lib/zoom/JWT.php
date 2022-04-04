<?php
namespace BooklyPro\Lib\Zoom;

use BooklyPro\Lib\Config;
use BooklyPro\Lib\Zoom\Jwt\JWT as JWTLib;

/**
 * Class JWT
 * @package BooklyPro\Lib\Zoom
 */
class JWT extends BaseAuth
{
    /** @var string */
    protected $jwt_api_key;
    /** @var string */
    protected $jwt_api_secret;

    /**
     * @inheritDoc
     */
    protected function init( $staff = null )
    {
        if ( $staff ) {
            $this
                ->setJwtApiKey( $staff->getZoomJwtApiKey() )
                ->setJwtApiSecret( $staff->getZoomJwtApiSecret() );
        } else {
            $this
                ->setJwtApiKey( Config::zoomJwtApiKey() )
                ->setJwtApiSecret( Config::zoomJwtApiSecret() );
        }
    }

    /**
     * Sets jwt_api_key
     *
     * @param string $jwt_api_key
     * @return $this
     */
    public function setJwtApiKey( $jwt_api_key )
    {
        $this->jwt_api_key = $jwt_api_key;

        return $this;
    }

    /**
     * Sets jwt_api_secret
     *
     * @param string $jwt_api_secret
     * @return $this
     */
    public function setJwtApiSecret( $jwt_api_secret )
    {
        $this->jwt_api_secret = $jwt_api_secret;

        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function getBearerToken()
    {
        $token = array(
            'iss' => $this->jwt_api_key,
            'exp' => time() + 60,
        );

        return JWTLib::encode( $token, $this->jwt_api_secret );
    }

}