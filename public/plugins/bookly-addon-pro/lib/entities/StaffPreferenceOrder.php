<?php
namespace BooklyPro\Lib\Entities;

use Bookly\Lib;

class StaffPreferenceOrder extends Lib\Base\Entity
{
    /** @var  int */
    protected $service_id;
    /** @var  int */
    protected $staff_id;
    /** @var  int */
    protected $position;

    protected static $table = 'bookly_staff_preference_orders';

    protected static $schema = array(
        'id'         => array( 'format' => '%d' ),
        'service_id' => array( 'format' => '%d', 'reference' => array( 'entity' => 'Service', 'namespace' => '\Bookly\Lib\Entities' ) ),
        'staff_id'   => array( 'format' => '%d', 'reference' => array( 'entity' => 'Staff',   'namespace' => '\Bookly\Lib\Entities' ) ),
        'position'   => array( 'format' => '%d', 'sequent' => true ),
    );

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets service_id
     *
     * @return int
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Sets service_id
     *
     * @param int $service_id
     * @return $this
     */
    public function setServiceId( $service_id )
    {
        $this->service_id = $service_id;

        return $this;
    }

    /**
     * Gets staff_id
     *
     * @return int
     */
    public function getStaffId()
    {
        return $this->staff_id;
    }

    /**
     * Sets staff_id
     *
     * @param int $staff_id
     * @return $this
     */
    public function setStaffId( $staff_id )
    {
        $this->staff_id = $staff_id;

        return $this;
    }

    /**
     * Gets position
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition( $position )
    {
        $this->position = $position;

        return $this;
    }
}