<?php
namespace BooklyPro\Lib\Notifications\Assets\Combined;

use Bookly\Lib\Notifications\Assets\Item\ICS as ItemICS;

/**
 * Class ICS
 * @package Bookly\Lib\Notifications\Assets\Item
 */
class ICS extends ItemICS
{
    protected $data;

    /**
     * Constructor.
     *
     * @param Codes $codes
     */
    public function __construct( Codes $codes )
    {
        $this->data =
            "BEGIN:VCALENDAR\n"
            . "VERSION:2.0\n"
            . "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\n"
            . "CALSCALE:GREGORIAN\n";
            foreach ( $codes->cart_info as $item ) {
                $this->data .= sprintf(
                    "BEGIN:VEVENT\n"
                    . "DTSTART:%s\n"
                    . "DTEND:%s\n"
                    . "SUMMARY:%s\n"
                    . "DESCRIPTION:%s\n"
                    . "LOCATION:%s\n"
                    . "END:VEVENT\n",
                    $this->_formatDateTime( $item['appointment_start'] ),
                    $this->_formatDateTime( $item['appointment_end'] ),
                    $this->_escape( $item['service_name'] ),
                    $this->_escape( sprintf( "%s\n%s", $item['service_name'], $item['staff_name'] ) ),
                    $this->_escape( sprintf( "%s", $item['location'] ) )
                );
            }
        $this->data .= 'END:VCALENDAR';
    }
}