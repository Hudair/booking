<?php
namespace BooklyPro\Backend\Modules\Appointments\ProxyProviders;

use Bookly\Backend\Modules\Appointments\Proxy;
use Bookly\Backend\Components\Controls\Buttons;

/**
 * Class Local
 * @package BooklyPro\Backend\Modules\Appointments\ProxyProviders
 */
class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function renderExportButton()
    {
        echo '<div class="col-auto">';
        Buttons::render( null, 'btn-default w-100 mb-3', __( 'Export to CSV', 'bookly' ), array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-export-dialog' ), '{caption}…', '<i class="far fa-fw fa-share-square mr-lg-1"></i>', true );
        echo '</div>';
    }

    /**
     * @inheritDoc
     */
    public static function renderExportDialog( $datatables )
    {
        self::renderTemplate( 'export_dialog', compact( 'datatables' ) );
    }

    /**
     * @inheritDoc
     */
    public static function renderPrintButton()
    {
        echo '<div class="col-auto">';
        Buttons::render( null, 'btn-default w-100 mb-3', __( 'Print', 'bookly' ), array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-print-dialog' ), '{caption}…', '<i class="fas fa-fw fa-print mr-lg-1"></i>', true );
        echo '</div>';
    }

    /**
     * @inheritDoc
     */
    public static function renderPrintDialog( $datatables )
    {
        self::renderTemplate( 'print_dialog', compact( 'datatables' ) );
    }
}