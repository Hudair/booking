<?php
namespace BooklyPro\Backend\Modules\Customers\ProxyProviders;

use Bookly\Backend\Modules\Customers\Proxy;
use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Controls\Buttons;

/**
 * Class Local
 * @package BooklyPro\Backend\Modules\Customers\ProxyProviders
 */
class Local extends Proxy\Pro
{
    /**
     * @inheritDoc
     */
    public static function importCustomers()
    {
        $fs = BooklyLib\Utils\Common::getFilesystem();
        @ini_set( 'auto_detect_line_endings', true );
        $fields = array();
        foreach ( array( 'full_name', 'first_name', 'last_name', 'phone', 'email', 'birthday' ) as $field ) {
            if ( self::parameter( $field ) ) {
                $fields[] = $field;
            }
        }
        if ( $fs->exists( $_FILES['import_customers_file']['tmp_name'] ) ) {
            $rows = $fs->get_contents_array( $_FILES['import_customers_file']['tmp_name'] );
            if ( $rows ) {
                foreach ( $rows as $row ) {
                    $line = str_getcsv( $row, self::parameter( 'import_customers_delimiter' ) );
                    if ( $line[0] != '' ) {
                        $customer = new BooklyLib\Entities\Customer();
                        foreach ( $line as $number => $value ) {
                            if ( $number < count( $fields ) ) {
                                if ( $fields[ $number ] == 'birthday' ) {
                                    $dob = date_create( $value );
                                    if ( $dob !== false ) {
                                        $customer->setBirthday( $dob->format( 'Y-m-d' ) );
                                    }
                                } else {
                                    $method = 'set' . implode( '', array_map( 'ucfirst', explode( '_', $fields[ $number ] ) ) );
                                    $customer->$method( $value );
                                }
                            }
                        }
                        $customer->save();
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    public static function renderImportButton()
    {
        echo '<div class="col-auto">';
        Buttons::render( null, 'btn-default w-100 mb-3', __( 'Import', 'bookly' ), array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-import-customers-dialog' ), '{caption}…', '<i class="far fa-fw fa-share-square fa-rotate-90 mr-lg-1"></i>', true );
        echo '</div>';
    }

    /**
     * @inheritDoc
     */
    public static function renderExportButton()
    {
        echo '<div class="col-auto">';
        Buttons::render( null, 'btn-default w-100 mb-3', __( 'Export to CSV', 'bookly' ), array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-export-customers-dialog' ), '{caption}…', '<i class="far fa-fw fa-share-square mr-lg-1"></i>', true );
        echo '</div>';
    }

    /**
     * @inheritDoc
     */
    public static function renderImportDialog()
    {
        self::renderTemplate( 'import_dialog' );
    }

    /**
     * @inheritDoc
     */
    public static function renderExportDialog( $settings, $columns )
    {
        self::renderTemplate( 'export_dialog', compact( 'settings', 'columns' ) );
    }
}