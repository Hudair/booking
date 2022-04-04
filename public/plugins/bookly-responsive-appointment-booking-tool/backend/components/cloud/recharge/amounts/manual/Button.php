<?php
namespace Bookly\Backend\Components\Cloud\Recharge\Amounts\Manual;

use Bookly\Backend\Components\Cloud\Recharge\Amounts;
use Bookly\Lib;

/**
 * Class Button
 * @package Bookly\Backend\Components\Cloud\Recharge\Amounts\Manual
 */
class Button extends Lib\Base\Component
{
    public static function renderBalance()
    {
        $cloud = Lib\Cloud\API::getInstance();
        self::enqueueStyles( array(
            'backend' => array( 'css/fontawesome-all.min.css', ),
        ) );

        self::renderTemplate( 'balance', compact( 'cloud' ) );
    }

    public static function renderRecharges()
    {
        $amounts = Amounts::getInstance();
        self::renderTemplate( 'recharges', array(
            'recharges' => $amounts->getItems( Amounts::RECHARGE_TYPE_MANUAL ),
            'best_offer' => $amounts->getTaggedItem( 'best_offer', Amounts::RECHARGE_TYPE_MANUAL )
        ) );
    }
}