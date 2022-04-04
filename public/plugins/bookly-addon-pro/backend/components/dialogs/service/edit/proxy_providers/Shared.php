<?php
namespace BooklyPro\Backend\Components\Dialogs\Service\Edit\ProxyProviders;

use Bookly\Backend\Components\Dialogs\Service\Edit\Proxy;
use BooklyPro\Lib;

/**
 * Class Shared
 * @package BooklyPro\Backend\Modules\Services\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritDoc
     */
    public static function enqueueAssetsForServices()
    {
        self::enqueueScripts( array(
            'module' => array( 'js/pro-services.js' => array( 'jquery' ), ),
            'bookly' => array(
                'backend/components/ace/resources/js/ace.js' => array(),
                'backend/components/ace/resources/js/ext-language_tools.js' => array(),
                'backend/components/ace/resources/js/mode-bookly.js' => array(),
                'backend/components/ace/resources/js/editor.js' => array( 'bookly-pro-services.js' ),
            ),
        ) );

        self::enqueueStyles( array(
            'bookly' => array( 'backend/components/ace/resources/css/ace.css', )
        ) );

        wp_localize_script( 'bookly-pro-services.js', 'BooklyProL10nServiceEditDialog', array(
            'capacity_error' => __( 'Min capacity should not be greater than max capacity.', 'bookly' ),
            'recurrence_error' => __( 'You must select at least one repeat option for recurring services.', 'bookly' ),
        ) );
    }

    /**
     * @inheritDoc
     */
    public static function prepareUpdateService( array $data )
    {
        // Saving staff preferences for service, when the form is submitted
        /** @var Lib\Entities\StaffPreferenceOrder[] $staff_preferences */
        $staff_preferences = Lib\Entities\StaffPreferenceOrder::query()
            ->where( 'service_id', $data['id'] )
            ->indexBy( 'staff_id' )
            ->find();
        $data['min_time_prior_booking'] = $data['min_time_prior_booking'] === '' ? null : $data['min_time_prior_booking'];
        $data['min_time_prior_cancel'] = $data['min_time_prior_cancel'] === '' ? null : $data['min_time_prior_cancel'];
        if ( array_key_exists( 'positions', $data ) ) {
            foreach ( (array) $data['positions'] as $position => $staff_id ) {
                if ( array_key_exists( $staff_id, $staff_preferences ) ) {
                    $staff_preferences[ $staff_id ]->setPosition( $position )->save();
                } else {
                    $preference = new Lib\Entities\StaffPreferenceOrder();
                    $preference
                        ->setServiceId( $data['id'] )
                        ->setStaffId( $staff_id )
                        ->setPosition( $position )
                        ->save();
                }
            }
        }

        // Staff preference period.
        $data['staff_preference_settings'] = json_encode( array(
            'period' => array(
                'before' => isset( $data['staff_preferred_period_before'] ) ? max( 0, (int) $data['staff_preferred_period_before'] ) : 0,
                'after'  => isset( $data['staff_preferred_period_after'] ) ? max( 0, (int) $data['staff_preferred_period_after'] ) : 0,
            ),
            'random' => (bool) $data['staff_preferred_random'],
        ) );

        return $data;
    }
}