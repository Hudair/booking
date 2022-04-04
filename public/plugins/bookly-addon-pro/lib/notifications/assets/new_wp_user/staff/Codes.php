<?php
namespace BooklyPro\Lib\Notifications\Assets\NewWpUser\Staff;

use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Notifications\Assets\Base;

/**
 * Class Codes
 * @package BooklyPro\Lib\Notifications\Assets\NewWpUser\Staff
 *
 * @property string $staff_email
 * @property string $staff_info
 * @property string $staff_name
 * @property string $staff_phone
 * @property string $site_address
 */
class Codes extends Base\Codes
{
    // Core
    public $new_username;
    public $new_password;
    public $staff_photo;

    /** @var Staff */
    protected $staff;

    /**
     * Constructor.
     *
     * @param Staff $staff
     * @param string $username
     * @param string $password
     */
    public function __construct( Staff $staff, $username, $password )
    {
        $staff_photo  = '';
        $photo = wp_get_attachment_image_src( $staff->getAttachmentId(), 'full' );
        if ( $photo != '' ) {
            if ( $format == 'html' ) {
                // Staff photo as <img> tag.
                $staff_photo = sprintf(
                    '<img src="%s" alt="%s" />',
                    esc_attr( $photo[0] ),
                    esc_attr( $this->staff->getFullName() )
                );
            } else {
                $staff_photo  = $photo[0];
            }
        }
        $this->staff_photo  = $staff_photo;
        $this->staff = $staff;
        $this->new_username = $username;
        $this->new_password = $password;
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
            'site_address' => site_url(),
            'staff_email' => $this->staff->getEmail(),
            'staff_info' => $format == 'html' ? nl2br( $this->staff->getInfo() ) : $this->staff->getInfo(),
            'staff_name' => $this->staff->getFullName(),
            'staff_phone' => $this->staff->getPhone(),
            'staff_photo' => $this->staff_photo,
        );

        return $replace_codes;
    }

}