<?php
namespace Bookly\Backend\Modules\Debug\Lib\Tests;

use Bookly\Lib;

/**
 * Class Slug
 * @package Bookly\Backend\Modules\Debug\Lib\Tests
 */
class Slug extends Base
{
    protected $name = 'Check plugins directories';

    /** @inheritDoc */
    public function execute()
    {
        /**
         * @var Lib\Base\Plugin $plugin
         */
        foreach ( apply_filters( 'bookly_plugins', array() ) as $slug => $plugin ) {
            $slug = strtolower( preg_replace( '([A-Z\d])', '-$0', $plugin::getRootNamespace() ) );
            if ( $slug === '-bookly' ) {
                $slug = 'bookly-responsive-appointment-booking-tool';
            } else {
                $slug = str_replace( '-bookly-', 'bookly-addon-', $slug );
            }

            if ( $slug !== $plugin::getSlug() ) {
                $this->addError( $plugin::getTitle() . ' incorrect slug<br>expected=' . $slug . '<br>    real=' . $plugin::getSlug() );
            }
        }

        return empty( $this->errors );
    }
}