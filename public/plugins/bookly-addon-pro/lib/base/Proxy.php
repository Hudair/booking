<?php
namespace BooklyPro\Lib\Base;

/**
 * Class Proxy
 * @package BooklyPro\Lib\Base
 */
abstract class Proxy
{
    /**
     * Register proxy methods.
     *
     * @param string $called_class
     * @param \ReflectionClass $reflection
     */
    public static function init( $called_class, \ReflectionClass $reflection )
    {
        $parent_class_name = $reflection->getParentClass()->getName();

        foreach ( $reflection->getMethods( \ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC ) as $method ) {
            if ( $method->class != $called_class ) {
                // Stop if parent class reached.
                break;
            }

            $action   = $parent_class_name . '::' . $method->name;
            $function = function () use ( $method ) {
                $args = func_get_args();
                $res  = $method->invokeArgs( null, $args );

                return $res === null ? $args[0] : $res;
            };

            add_filter( $action, $function, 10, $method->getNumberOfParameters() ?: 1 );
        }
    }

    /**
     * Check if given proxy method can be invoked.
     *
     * @param string $called_class
     * @param string $method
     * @return false|int
     */
    public static function canInvoke( $called_class, $method )
    {
        return has_filter( $called_class . '::' . $method );
    }

    /**
     * Invoke proxy method.
     *
     * @param string $called_class
     * @param string $method
     * @param mixed $args
     * @return mixed
     */
    public static function invoke( $called_class, $method, $args )
    {
        return apply_filters_ref_array( $called_class . '::' . $method, empty ( $args ) ? array( null ) : $args );
    }
}