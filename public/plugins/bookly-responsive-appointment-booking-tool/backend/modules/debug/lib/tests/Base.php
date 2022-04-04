<?php
namespace Bookly\Backend\Modules\Debug\Lib\Tests;

/**
 * Class Base
 * @package Bookly\Backend\Modules\Debug\Lib\Tests
 */
abstract class Base
{
    /** @var array  */
    protected $errors = array();
    /** @var mixed  */
    protected $data;
    /** @var string */
    protected $name;

    /**
     * Base constructor.
     *
     * @param array|string|null $data
     */
    public function __construct( $data = null )
    {
        $this->data = $data;
    }

    /**
     * Execute test
     *
     * @return bool
     */
    public function execute()
    {
        return true;
    }

    /**
     * Get test error.
     *
     * @return string
     */
    public function error()
    {
        return implode( '<br>', $this->errors );
    }

    /**
     * Add error.
     *
     * @param string $error
     */
    public function addError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Get test name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}