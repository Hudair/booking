<?php
namespace Bookly\Backend\Modules\Debug\Lib\Tools;

/**
 * Class Base
 * @package Bookly\Backend\Modules\Debug\Lib\Tools
 */
abstract class Base
{
    /** @var mixed */
    protected $data;
    /** @var string */
    protected $name;
    /** @var string */
    protected $tool;
    /** @var array */
    protected $alerts = array( 'success' => array(), 'error' => array() );

    /**
     * Base constructor.
     * @param string $data
     */
    public function __construct( $data = null )
    {
        $this->data = $data;
        $path = explode('\\', get_called_class());
        $this->tool = array_pop($path);
    }

    /**
     * Execute tool.
     *
     * @return bool
     */
    public function execute()
    {
        return true;
    }

    /**
     * Get alerts.
     *
     * @return array
     */
    public function alerts()
    {
        return $this->alerts;
    }

    /**
     * Add error.
     *
     * @param string $error
     */
    public function addError($error)
    {
        $this->alerts['error'][] = $error;
    }

    /**
     * Add success info.
     *
     * @param string $info
     */
    public function addInfo($info)
    {
        $this->alerts['success'][] = $info;
    }

    /**
     * Get tool name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get menu
     *
     * @return string
     */
    public function getMenu()
    {
        return '';
    }
}