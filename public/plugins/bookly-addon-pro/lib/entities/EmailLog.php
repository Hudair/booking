<?php
namespace BooklyPro\Lib\Entities;

use Bookly\Lib as BooklyLib;

/**
 * Class EmailLog
 *
 * @package BooklyPro\Lib\Entities
 */
class EmailLog extends BooklyLib\Base\Entity
{
    /** @var  string */
    protected $to;
    /** @var  string */
    protected $subject;
    /** @var  string */
    protected $body;
    /** @var  string */
    protected $headers;
    /** @var  string */
    protected $attach;
    /** @var  string */
    protected $type;
    /** @var string */
    protected $created_at;

    protected static $table = 'bookly_email_log';

    protected static $schema = array(
        'id' => array( 'format' => '%d' ),
        'to' => array( 'format' => '%s' ),
        'subject' => array( 'format' => '%s' ),
        'body' => array( 'format' => '%s' ),
        'headers' => array( 'format' => '%s' ),
        'attach' => array( 'format' => '%s' ),
        'type' => array( 'format' => '%s' ),
        'created_at' => array( 'format' => '%s' ),
    );

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * @return string
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return EmailLog
     */
    public function setTo( $to )
    {
        $this->to = $to;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return EmailLog
     */
    public function setSubject( $subject )
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return EmailLog
     */
    public function setBody( $body )
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $headers
     * @return EmailLog
     */
    public function setHeaders( $headers )
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttach()
    {
        return $this->attach;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return EmailLog
     */
    public function setType( $type )
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param string $attach
     * @return EmailLog
     */
    public function setAttach( $attach )
    {
        $this->attach = $attach;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     * @return EmailLog
     */
    public function setCreatedAt( $created_at )
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/
}
