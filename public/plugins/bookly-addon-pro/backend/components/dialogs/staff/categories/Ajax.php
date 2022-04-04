<?php
namespace BooklyPro\Backend\Components\Dialogs\Staff\Categories;

use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Entities;

/**
 * Class Ajax
 * @package BooklyPro\Backend\Components\Dialogs\Staff\Categories
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * Update staff categories
     */
    public static function updateStaffCategories()
    {
        $categories          = self::parameter( 'categories', array() );
        $existing_categories = array();
        foreach ( $categories as $category ) {
            if ( strpos( $category['id'], 'new' ) === false ) {
                $existing_categories[] = $category['id'];
            }
        }

        // Delete categories
        Entities\StaffCategory::query( 'c' )->delete()->whereNotIn( 'c.id', $existing_categories )->execute();
        foreach ( $categories as $position => $category_data ) {
            if ( strpos( $category_data['id'], 'new' ) === false ) {
                $category = Entities\StaffCategory::find( $category_data['id'] );
            } else {
                $category = new Entities\StaffCategory();
            }
            $category
                ->setPosition( $position )
                ->setName( $category_data['name'] )
                ->save();
        }

        wp_send_json_success( Entities\StaffCategory::query()->sortBy( 'position' )->fetchArray() );
    }
}