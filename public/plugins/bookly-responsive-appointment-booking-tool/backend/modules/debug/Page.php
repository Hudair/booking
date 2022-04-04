<?php
namespace Bookly\Backend\Modules\Debug;

use Bookly\Lib;
use Bookly\Backend\Modules\Debug\Lib\Schema;
use Bookly\Backend\Modules\Debug\Lib\QueryBuilder;

/**
 * Class Page
 * @package Bookly\Backend\Modules\Debug
 */
class Page extends Lib\Base\Component
{
    const TABLE_STATUS_OK      = 1;
    const TABLE_STATUS_ERROR   = 0;
    const TABLE_STATUS_WARNING = 2;
    const TABLE_STATUS_INFO    = 3;

    /**
     * Render page.
     */
    public static function render()
    {
        /** @var \wpdb $wpdb*/
        global $wpdb;

        self::enqueueStyles( array(
            'backend' => array( 'css/fontawesome-all.min.css' => array( 'bookly-backend-globals' ) ),
            'module' => array( 'css/style.css' ),
        ) );

        self::enqueueScripts( array(
            'module' => array( 'js/debug.js' => array( 'bookly-backend-globals' ) ),
        ) );

        $debug  = array();
        $schema = new Schema();
        $trouble = false;
        /** @var Lib\Base\Plugin $plugin */
        foreach ( apply_filters( 'bookly_plugins', array() ) as $plugin ) {
            foreach ( $plugin::getEntityClasses() as $entity_class ) {
                $table_name = $entity_class::getTableName();
                $debug[ $table_name ] = array(
                    'fields'      => null,
                    'constraints' => null,
                    'status'      => null,
                );
                if ( $schema->existsTable( $table_name ) ) {
                    $table_structure    = $schema->getTableStructure( $table_name );
                    $table_constraints  = $schema->getTableConstraints( $table_name );
                    $entity_schema      = $entity_class::getSchema();
                    $entity_constraints = $entity_class::getConstraints();
                    $debug[ $table_name ]['status'] = self::TABLE_STATUS_OK;
                    $debug[ $table_name ]['fields'] = array();

                    // Comparing model schema with real DB schema
                    foreach ( $entity_schema as $field => $data ) {
                        if ( array_key_exists( $field, $table_structure ) ) {
                            $debug[ $table_name ]['fields'][ $field ] = 1;
                            $expect = QueryBuilder::getColumnData( $table_name, $field );
                            $actual = $table_structure[ $field ];
                            unset( $expect['key'], $actual['key'] );
                            $diff = array_diff_assoc( $actual, $expect );
                            if ( $expect && $diff ) {
                                $debug[ $table_name ]['status'] = self::TABLE_STATUS_WARNING;
                                $debug[ $table_name ]['info'][ $field ] = array_keys( $diff );
                                $trouble = true;
                            }
                        } else {
                            $debug[ $table_name ]['fields'][ $field ] = 0;
                            $debug[ $table_name ]['status'] = self::TABLE_STATUS_WARNING;
                            $trouble = true;
                        }
                        unset( $table_structure[ $field ] );
                    }
                    foreach ( $table_structure as $field => $data ) {
                        $data['class'] = $entity_class;
                        if ( $debug[ $table_name ]['status'] != self::TABLE_STATUS_WARNING ) {
                            $debug[ $table_name ]['status'] = self::TABLE_STATUS_INFO;
                        }
                        $debug[ $table_name ]['fields_3d'][ $field ] = $data;
                    }

                    // Comparing model constraints with real DB constraints
                    foreach ( $entity_constraints as $constraint ) {
                        $key = $constraint['column_name'] . $constraint['referenced_table_name'] . $constraint['referenced_column_name'];
                        $debug[ $table_name ]['constraints'][ $key ] = $constraint;
                        if ( array_key_exists ( $key, $table_constraints ) ) {
                            $debug[ $table_name ]['constraints'][ $key ]['status'] = 1;
                        } else {
                            $debug[ $table_name ]['constraints'][ $key ]['status'] = 0;
                            $debug[ $table_name ]['status'] = self::TABLE_STATUS_WARNING;
                            $trouble = true;
                        }
                    }
                    $debug[ $table_name ]['constraints_3d'] = array();
                    foreach ( $table_constraints as $constraint_name => $constraint ) {
                        $key = $constraint['column_name'] . $constraint['referenced_table_name'] . $constraint['referenced_column_name'];
                        if ( ! isset( $debug[ $table_name ]['constraints'][ $key ] ) ) {
                            $debug[ $table_name ]['constraints_3d'][ $key ] = $constraint;
                            $debug[ $table_name ]['constraints_3d'][ $key ]['status'] = 0;
                            if ( $debug[ $table_name ]['status'] != self::TABLE_STATUS_WARNING ) {
                                $debug[ $table_name ]['status'] = self::TABLE_STATUS_INFO;
                            }
                        }
                    }
                } else {
                    $debug[ $table_name ]['status'] = self::TABLE_STATUS_ERROR;
                    $trouble = true;
                }
            }
        }

        $tests = array();
        foreach ( glob( __DIR__ . '/lib/tests/*.php' ) as $path ) {
            $test = basename( $path, '.php' );
            if ( $test !== 'Base' ) {
                $tests[] = $test;
            }
        }

        wp_localize_script( 'bookly-debug.js', 'BooklyL10n', array(
            'csrfToken'      => Lib\Utils\Common::getCsrfToken(),
            'tests'          => $tests,
            'datePicker'     => Lib\Utils\DateTime::datePickerOptions(),
            'dateRange'      => Lib\Utils\DateTime::dateRangeOptions( array( 'lastMonth' => __( 'Last month', 'bookly' ), ) ),
            'charsetCollate' => $wpdb->has_cap( 'collation' )
                ? $wpdb->get_charset_collate()
                : 'DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci'
        ) );

        ksort( $debug );
        $import_status = self::parameter( 'status' );
        $tools = '';
        foreach ( glob( __DIR__ . '/lib/tools/*.php' ) as $path ) {
            $tool = basename( $path, '.php' );
            if ( $tool !== 'Base' ) {
                $tool_class = '\Bookly\Backend\Modules\Debug\Lib\Tools\\' . $tool;
                if ( class_exists( $tool_class, true ) ) {
                    /** @var \Bookly\Backend\Modules\Debug\Lib\Tools\Base $tool */
                    $tool   = new $tool_class;
                    $tools .= $tool->getMenu();
                }
            }
        }
        $db = $wpdb->get_row( 'SELECT version() AS version', ARRAY_A );
        self::renderTemplate( 'index', compact( 'debug', 'import_status', 'tools', 'trouble', 'db' ) );
    }
}