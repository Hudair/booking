<?php
namespace Bookly\Lib\Utils;

use Bookly\Lib;

/**
 * Class Log
 * @package Bookly\Lib\Utils
 */
abstract class Log
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /**
     * @param        $entity
     * @param string $ref
     * @param string $comment
     */
    public static function createEntity( $entity, $ref = null, $comment = null )
    {
        self::common( self::ACTION_CREATE, $entity->getTableName(), $entity->getId(), json_encode( $entity->getFields() ), $ref, $comment );
    }

    /**
     * @param        $entity
     * @param string $ref
     * @param string $comment
     */
    public static function updateEntity( $entity, $ref = null, $comment = null )
    {
        $modified = array();
        $fields   = $entity->getFields();
        foreach ( array_keys( $entity->getModified() ) as $key ) {
            $modified[ $key ] = $fields[ $key ];
        }

        self::common( self::ACTION_UPDATE, $entity->getTableName(), $entity->getId(), json_encode( $modified ), $ref, $comment );
    }

    /**
     * @param        $entity
     * @param string $ref
     * @param string $comment
     */
    public static function deleteEntity( $entity, $ref = null, $comment = null )
    {
        self::common( self::ACTION_DELETE, $entity->getTableName(), $entity->getId(), json_encode( $entity->getFields() ), $ref, $comment );
    }

    /**
     * @param string $action
     * @param string $target
     * @param string $target_id
     * @param string $details
     * @param string $ref
     * @param string $comment
     *
     * @return void|bool
     */
    public static function common( $action = null, $target = null, $target_id = null, $details = null, $ref = null, $comment = null )
    {
        if ( ! get_option( 'bookly_logs_enabled' ) ) {
            return false;
        }

        $log = new Lib\Entities\Log();
        $log
            ->setAction( $action )
            ->setTarget( $target )
            ->setTargetId( $target_id )
            ->setAuthor( self::getAuthor() )
            ->setRef( $ref )
            ->setComment( $comment )
            ->setDetails( $details )
            ->setCreatedAt( current_time( 'mysql' ) )
            ->save();
    }

    /**
     * @return string
     */
    private static function getAuthor()
    {
        $author_id = get_current_user_id();

        return $author_id ? ( trim( get_user_meta( $author_id, 'first_name', true ) . ' ' . get_user_meta( $author_id, 'last_name', true ) ) ?: get_user_meta( $author_id, 'nickname', true ) ) : '';
    }
}