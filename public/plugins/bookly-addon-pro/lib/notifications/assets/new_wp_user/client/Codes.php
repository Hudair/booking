<?php
namespace BooklyPro\Lib\Notifications\Assets\NewWpUser\Client;

use Bookly\Lib\Entities\Customer;
use Bookly\Lib\Notifications\Assets\ClientBirthday;

/**
 * Class Codes
 * @package BooklyPro\Lib\Notifications\Assets\NewWpUser\Client
 */
class Codes extends ClientBirthday\Codes
{
    // Core
    public $new_username;
    public $new_password;
    public $site_address;

    /**
     * Constructor.
     *
     * @param Customer $customer
     * @param string $username
     * @param string $password
     */
    public function __construct( Customer $customer, $username, $password )
    {
        parent::__construct( $customer );

        $this->new_username = $username;
        $this->new_password = $password;
        $this->site_address = site_url();
    }

    /**
     * @inheritDoc
     */
    protected function getReplaceCodes( $format )
    {
        $replace_codes = parent::getReplaceCodes( $format );

        // Add replace codes.
        $replace_codes += array(
            'new_password' => $this->new_password,
            'new_username' => $this->new_username,
            'site_address' => $this->site_address,
        );

        return $replace_codes;
    }
}