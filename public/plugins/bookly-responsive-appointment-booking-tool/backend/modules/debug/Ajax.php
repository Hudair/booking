<?php
namespace Bookly\Backend\Modules\Debug;

use Bookly\Lib;
use Bookly\Backend\Modules\Debug\Lib\QueryBuilder;
use Bookly\Backend\Modules\Debug\Lib\Schema;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\Debug
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( 'runTest' => 'anonymous' );
    }

    /**
     * Export database data.
     */
    public static function exportData()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $result = array();
        $schema = new Schema();
        foreach ( apply_filters( 'bookly_plugins', array() ) as $plugin ) {
            /** @var Lib\Base\Plugin $plugin */
            $installer_class = $plugin::getRootNamespace() . '\Lib\Installer';
            /** @var Lib\Base\Installer $installer */
            $installer = new $installer_class();
            $result['plugins'][ $plugin::getBasename() ] = $plugin::getVersion();

            foreach ( $plugin::getEntityClasses() as $entity_class ) {
                $table_name = $entity_class::getTableName();
                $result['entities'][ $entity_class ] = array(
                    'fields' => array_keys( $schema->getTableStructure( $table_name ) ),
                    'values' => $wpdb->get_results( 'SELECT * FROM ' . $table_name, ARRAY_N )
                );
            }
            $plugin_prefix   = $plugin::getPrefix();
            $options_postfix = array( 'data_loaded', 'grace_start', 'db_version', 'installation_time' );
            foreach ( $options_postfix as $option ) {
                $option_name = $plugin_prefix . $option;
                $result['options'][ $option_name ] = get_option( $option_name );
            }

            $result['options'][ $plugin::getPurchaseCodeOption() ] = $plugin::getPurchaseCode();
            foreach ( $installer->getOptions() as $option_name => $option_value ) {
                $result['options'][ $option_name ] = get_option( $option_name );
            }
        }

        header( 'Content-type: application/json' );
        header( 'Content-Disposition: attachment; filename=bookly_db_export_' . date( 'YmdHis' ) . '.json' );
        echo json_encode( $result );

        exit ( 0 );
    }

    /**
     * Import database data.
     */
    public static function importData()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;
        $fs = Lib\Utils\Common::getFilesystem();

        if ( $_FILES['import']['name'] ) {
            $json = $fs->get_contents( $_FILES['import']['tmp_name'] );
            if ( $json !== false) {
                $wpdb->query( 'SET FOREIGN_KEY_CHECKS = 0' );

                $data = json_decode( $json, true );
                /** @var Lib\Base\Plugin[] $bookly_plugins */
                $bookly_plugins = apply_filters( 'bookly_plugins', array() );
                /** @since Bookly 17.7 */
                if ( isset( $data['plugins'] ) ) {
                    foreach ( $bookly_plugins as $plugin ) {
                        if ( ! array_key_exists( $plugin::getBasename(), $data['plugins'] ) ) {
                            deactivate_plugins( $plugin::getBasename(), true, is_network_admin() );
                        }
                    }
                }
                foreach ( array_merge( array( 'bookly-responsive-appointment-booking-tool', 'bookly-addon-pro' ), array_keys( $bookly_plugins ) ) as $slug ) {
                    if ( ! array_key_exists( $slug, $bookly_plugins ) ) {
                        continue;
                    }
                    /** @var Lib\Base\Plugin $plugin */
                    $plugin = $bookly_plugins[ $slug ];
                    unset( $bookly_plugins[ $slug ] );
                    $installer_class = $plugin::getRootNamespace() . '\Lib\Installer';
                    /** @var Lib\Base\Installer $installer */
                    $installer = new $installer_class();

                    // Drop all data and options.
                    $installer->removeData();
                    $installer->dropTables();
                    $installer->createTables();

                    // Insert tables data.
                    foreach ( $plugin::getEntityClasses() as $entity_class ) {
                        if ( isset ( $data['entities'][ $entity_class ]['values'][0] ) ) {
                            $table_name = $entity_class::getTableName();
                            $query = sprintf(
                                'INSERT INTO `%s` (`%s`) VALUES (%%s)',
                                $table_name,
                                implode( '`,`', $data['entities'][ $entity_class ]['fields'] )
                            );
                            $placeholders = array();
                            $values       = array();
                            $counter      = 0;
                            foreach ( $data['entities'][ $entity_class ]['values'] as $row ) {
                                $params = array();
                                foreach ( $row as $value ) {
                                    if ( $value === null ) {
                                        $params[] = 'NULL';
                                    } else {
                                        $params[] = '%s';
                                        $values[] = $value;
                                    }
                                }
                                $placeholders[] = implode( ',', $params );
                                if ( ++ $counter > 50 ) {
                                    // Flush.
                                    $wpdb->query( $wpdb->prepare( sprintf( $query, implode( '),(', $placeholders ) ), $values ) );
                                    $placeholders = array();
                                    $values       = array();
                                    $counter      = 0;
                                }
                            }
                            if ( ! empty ( $placeholders ) ) {
                                $wpdb->query( $wpdb->prepare( sprintf( $query, implode( '),(', $placeholders ) ), $values ) );
                            }
                        }
                    }

                    // Insert options data.
                    foreach ( $installer->getOptions() as $option_name => $option_value ) {
                        add_option( $option_name, $data['options'][ $option_name ] );
                    }

                    $plugin_prefix   = $plugin::getPrefix();
                    $options_postfix = array( 'data_loaded', 'grace_start', 'db_version' );
                    foreach ( $options_postfix as $option ) {
                        $option_name = $plugin_prefix . $option;
                        add_option( $option_name, $data['options'][ $option_name ] );
                    }
                }

                header( 'Location: ' . admin_url( 'admin.php?page=bookly-debug&status=imported' ) );
            }
        }

        header( 'Location: ' . admin_url( 'admin.php?page=bookly-debug' ) );

        exit ( 0 );
    }

    /**
     * manual
     */
    public static function getFieldData()
    {
        /** @global \wpdb $wpdb*/
        global $wpdb;

        $table      = self::parameter( 'table' );
        $column     = self::parameter( 'column' );

        /*  SELECT CONCAT ( '\'', CONCAT_WS( '.', SUBSTR(TABLE_NAME,4), COLUMN_NAME ), '\' => "' , COLUMN_TYPE, ' ', IF(IS_NULLABLE = 'YES','null', 'not null') ,
                IF ( EXTRA = 'auto_increment', ' auto_increment primary key',
            CONCAT ( IF (COLUMN_DEFAULT is NULL, IF(IS_NULLABLE = 'NO', '', ' default null' ), CONCAT(' default \'',COLUMN_DEFAULT, '\'')))) , '",') AS data
              FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = SCHEMA()
               AND TABLE_NAME LIKE 'wp_bookly_%'
          ORDER BY TABLE_NAME, ORDINAL_POSITION
         */

        $fields = array(
            'bookly_appointments.id' => 'int unsigned not null auto_increment primary key',
            'bookly_appointments.location_id' => 'int unsigned null default null',
            'bookly_appointments.staff_id' => 'int unsigned not null',
            'bookly_appointments.staff_any' => "tinyint(1) not null default '0'",
            'bookly_appointments.service_id' => 'int unsigned null default null',
            'bookly_appointments.custom_service_name' => 'varchar(255) null default null',
            'bookly_appointments.custom_service_price' => 'decimal(10,2) null default null',
            'bookly_appointments.start_date' => 'datetime null default null',
            'bookly_appointments.end_date' => 'datetime null default null',
            'bookly_appointments.extras_duration' => "int not null default '0'",
            'bookly_appointments.internal_note' => 'text null default null',
            'bookly_appointments.google_event_id' => 'varchar(255) null default null',
            'bookly_appointments.google_event_etag' => 'varchar(255) null default null',
            'bookly_appointments.outlook_event_id' => 'varchar(255) null default null',
            'bookly_appointments.outlook_event_change_key' => 'varchar(255) null default null',
            'bookly_appointments.outlook_event_series_id' => 'varchar(255) null default null',
            'bookly_appointments.online_meeting_provider' => "enum('zoom','google_meet') null default null",
            'bookly_appointments.online_meeting_id' => 'varchar(255) null default null',
            'bookly_appointments.online_meeting_data' => 'text null default null',
            'bookly_appointments.created_from' => "enum('bookly','google','outlook') not null default 'bookly'",
            'bookly_appointments.created_at' => 'datetime not null',
            'bookly_appointments.updated_at' => 'datetime not null',
            'bookly_categories.id' => 'int unsigned not null auto_increment primary key',
            'bookly_categories.name' => 'varchar(255) not null',
            'bookly_categories.position' => "int not null default '9999'",
            'bookly_coupon_customers.id' => 'int unsigned not null auto_increment primary key',
            'bookly_coupon_customers.coupon_id' => 'int unsigned not null',
            'bookly_coupon_customers.customer_id' => 'int unsigned not null',
            'bookly_coupon_services.id' => 'int unsigned not null auto_increment primary key',
            'bookly_coupon_services.coupon_id' => 'int unsigned not null',
            'bookly_coupon_services.service_id' => 'int unsigned not null',
            'bookly_coupon_staff.id' => 'int unsigned not null auto_increment primary key',
            'bookly_coupon_staff.coupon_id' => 'int unsigned not null',
            'bookly_coupon_staff.staff_id' => 'int unsigned not null',
            'bookly_coupons.id' => 'int unsigned not null auto_increment primary key',
            'bookly_coupons.code' => "varchar(255) not null default ''",
            'bookly_coupons.discount' => "decimal(3,0) not null default '0'",
            'bookly_coupons.deduction' => "decimal(10,2) not null default '0.00'",
            'bookly_coupons.usage_limit' => "int unsigned not null default '1'",
            'bookly_coupons.used' => "int unsigned not null default '0'",
            'bookly_coupons.once_per_customer' => "tinyint(1) not null default '0'",
            'bookly_coupons.date_limit_start' => 'date null default null',
            'bookly_coupons.date_limit_end' => 'date null default null',
            'bookly_coupons.min_appointments' => "int unsigned not null default '1'",
            'bookly_coupons.max_appointments' => 'int unsigned null default null',
            'bookly_custom_statuses.id' => 'int unsigned not null auto_increment primary key',
            'bookly_custom_statuses.slug' => 'varchar(255) not null',
            'bookly_custom_statuses.name' => 'varchar(255) null default null',
            'bookly_custom_statuses.busy' => "tinyint(1) not null default '1'",
            'bookly_custom_statuses.color' => "varchar(255) not null default '#dddddd'",
            'bookly_custom_statuses.position' => "int not null default '9999'",
            'bookly_customer_appointment_files.id' => 'int unsigned not null auto_increment primary key',
            'bookly_customer_appointment_files.customer_appointment_id' => 'int unsigned not null',
            'bookly_customer_appointment_files.file_id' => 'int unsigned not null',
            'bookly_customer_appointments.id' => 'int unsigned not null auto_increment primary key',
            'bookly_customer_appointments.series_id' => 'int unsigned null default null',
            'bookly_customer_appointments.package_id' => 'int unsigned null default null',
            'bookly_customer_appointments.customer_id' => 'int unsigned not null',
            'bookly_customer_appointments.appointment_id' => 'int unsigned not null',
            'bookly_customer_appointments.payment_id' => 'int unsigned null default null',
            'bookly_customer_appointments.order_id' => 'int unsigned null default null',
            'bookly_customer_appointments.number_of_persons' => "int unsigned not null default '1'",
            'bookly_customer_appointments.units' => "int unsigned not null default '1'",
            'bookly_customer_appointments.notes' => 'text null default null',
            'bookly_customer_appointments.extras' => 'text null default null',
            'bookly_customer_appointments.extras_multiply_nop' => "tinyint(1) not null default '1'",
            'bookly_customer_appointments.extras_consider_duration' => "tinyint(1) not null default '1'",
            'bookly_customer_appointments.custom_fields' => 'text null default null',
            'bookly_customer_appointments.status' => "varchar(255) not null default 'approved'",
            'bookly_customer_appointments.status_changed_at' => 'datetime null default null',
            'bookly_customer_appointments.token' => 'varchar(255) null default null',
            'bookly_customer_appointments.time_zone' => 'varchar(255) null default null',
            'bookly_customer_appointments.time_zone_offset' => 'int null default null',
            'bookly_customer_appointments.rating' => 'int null default null',
            'bookly_customer_appointments.rating_comment' => 'text null default null',
            'bookly_customer_appointments.locale' => 'varchar(8) null default null',
            'bookly_customer_appointments.collaborative_service_id' => 'int unsigned null default null',
            'bookly_customer_appointments.collaborative_token' => 'varchar(255) null default null',
            'bookly_customer_appointments.compound_service_id' => 'int unsigned null default null',
            'bookly_customer_appointments.compound_token' => 'varchar(255) null default null',
            'bookly_customer_appointments.created_from' => "enum('frontend','backend') not null default 'frontend'",
            'bookly_customer_appointments.created_at' => 'datetime not null',
            'bookly_customer_appointments.updated_at' => 'datetime not null',
            'bookly_customer_groups.id' => 'int unsigned not null auto_increment primary key',
            'bookly_customer_groups.name' => 'varchar(255) not null',
            'bookly_customer_groups.description' => 'text not null',
            'bookly_customer_groups.appointment_status' => "varchar(255) not null default ''",
            'bookly_customer_groups.discount' => "varchar(100) not null default '0'",
            'bookly_customer_groups.skip_payment' => "tinyint(1) not null default '0'",
            'bookly_customer_groups.gateways' => 'varchar(255) null default null',
            'bookly_customer_groups_services.id' => 'int unsigned not null auto_increment primary key',
            'bookly_customer_groups_services.group_id' => 'int unsigned not null',
            'bookly_customer_groups_services.service_id' => 'int unsigned not null',
            'bookly_customers.id' => 'int unsigned not null auto_increment primary key',
            'bookly_customers.wp_user_id' => 'bigint unsigned null default null',
            'bookly_customers.facebook_id' => 'bigint unsigned null default null',
            'bookly_customers.group_id' => 'int unsigned null default null',
            'bookly_customers.full_name' => "varchar(255) not null default ''",
            'bookly_customers.first_name' => "varchar(255) not null default ''",
            'bookly_customers.last_name' => "varchar(255) not null default ''",
            'bookly_customers.phone' => "varchar(255) not null default ''",
            'bookly_customers.email' => "varchar(255) not null default ''",
            'bookly_customers.birthday' => 'date null default null',
            'bookly_customers.country' => 'varchar(255) null default null',
            'bookly_customers.state' => 'varchar(255) null default null',
            'bookly_customers.postcode' => 'varchar(255) null default null',
            'bookly_customers.city' => 'varchar(255) null default null',
            'bookly_customers.street' => 'varchar(255) null default null',
            'bookly_customers.street_number' => 'varchar(255) null default null',
            'bookly_customers.additional_address' => 'varchar(255) null default null',
            'bookly_customers.notes' => 'text not null',
            'bookly_customers.info_fields' => 'text null default null',
            'bookly_customers.stripe_account' => 'varchar(255) null default null',
            'bookly_customers.created_at' => 'datetime not null',
            'bookly_discounts.id' => 'int unsigned not null auto_increment primary key',
            'bookly_discounts.title' => "varchar(255) null default ''",
            'bookly_discounts.type' => "enum('nop','appointments') not null default 'nop'",
            'bookly_discounts.threshold' => 'int unsigned null default null',
            'bookly_discounts.discount' => "decimal(3,0) not null default '0'",
            'bookly_discounts.deduction' => "decimal(10,2) not null default '0.00'",
            'bookly_discounts.date_start' => 'date null default null',
            'bookly_discounts.date_end' => 'date null default null',
            'bookly_discounts.enabled' => "tinyint(1) not null default '0'",
            'bookly_email_log.id' => 'int unsigned not null auto_increment primary key',
            'bookly_email_log.to' => 'varchar(255) not null',
            'bookly_email_log.subject' => 'varchar(255) not null',
            'bookly_email_log.body' => 'text not null',
            'bookly_email_log.headers' => 'text not null',
            'bookly_email_log.attach' => 'text not null',
            'bookly_email_log.type' => "varchar(255) not null default ''",
            'bookly_email_log.created_at' => 'datetime not null',
            'bookly_files.id' => 'int unsigned not null auto_increment primary key',
            'bookly_files.name' => 'text not null',
            'bookly_files.slug' => 'varchar(32) not null',
            'bookly_files.path' => 'text not null',
            'bookly_files.custom_field_id' => 'int null default null',
            'bookly_holidays.id' => 'int unsigned not null auto_increment primary key',
            'bookly_holidays.staff_id' => 'int unsigned null default null',
            'bookly_holidays.parent_id' => 'int unsigned null default null',
            'bookly_holidays.date' => 'date not null',
            'bookly_holidays.repeat_event' => "tinyint(1) not null default '0'",
            'bookly_locations.id' => 'int unsigned not null auto_increment primary key',
            'bookly_locations.name' => "varchar(255) null default ''",
            'bookly_locations.info' => 'text null default null',
            'bookly_locations.position' => "int not null default '9999'",
            'bookly_log.id' => 'int unsigned not null auto_increment primary key',
            'bookly_log.action' => "enum('create','update','delete') null default null",
            'bookly_log.target' => 'varchar(255) null default null',
            'bookly_log.target_id' => 'int unsigned null default null',
            'bookly_log.author' => 'varchar(255) null default null',
            'bookly_log.details' => 'text null default null',
            'bookly_log.ref' => 'varchar(255) null default null',
            'bookly_log.comment' => 'varchar(255) null default null',
            'bookly_log.created_at' => 'datetime not null',
            'bookly_news.id' => 'int unsigned not null auto_increment primary key',
            'bookly_news.news_id' => 'int unsigned not null',
            'bookly_news.title' => 'text null default null',
            'bookly_news.media_type' => "enum('image','youtube') not null default 'image'",
            'bookly_news.media_url' => 'varchar(255) not null',
            'bookly_news.text' => 'text null default null',
            'bookly_news.button_url' => 'varchar(255) null default null',
            'bookly_news.button_text' => 'varchar(255) null default null',
            'bookly_news.seen' => "tinyint(1) not null default '0'",
            'bookly_news.updated_at' => 'datetime not null',
            'bookly_news.created_at' => 'datetime not null',
            'bookly_notifications.id' => 'int unsigned not null auto_increment primary key',
            'bookly_notifications.gateway' => "enum('email','sms') not null default 'email'",
            'bookly_notifications.type' => "varchar(255) not null default ''",
            'bookly_notifications.active' => "tinyint(1) not null default '0'",
            'bookly_notifications.name' => "varchar(255) not null default ''",
            'bookly_notifications.subject' => "varchar(255) not null default ''",
            'bookly_notifications.message' => 'text null default null',
            'bookly_notifications.to_staff' => "tinyint(1) not null default '0'",
            'bookly_notifications.to_customer' => "tinyint(1) not null default '0'",
            'bookly_notifications.to_admin' => "tinyint(1) not null default '0'",
            'bookly_notifications.to_custom' => "tinyint(1) not null default '0'",
            'bookly_notifications.custom_recipients' => 'varchar(255) null default null',
            'bookly_notifications.attach_ics' => "tinyint(1) not null default '0'",
            'bookly_notifications.attach_invoice' => "tinyint(1) not null default '0'",
            'bookly_notifications.settings' => 'text null default null',
            'bookly_orders.id' => 'int unsigned not null auto_increment primary key',
            'bookly_orders.token' => 'varchar(255) null default null',
            'bookly_packages.id' => 'int unsigned not null auto_increment primary key',
            'bookly_packages.location_id' => 'int unsigned null default null',
            'bookly_packages.staff_id' => 'int unsigned null default null',
            'bookly_packages.service_id' => 'int unsigned not null',
            'bookly_packages.customer_id' => 'int unsigned not null',
            'bookly_packages.internal_note' => 'text null default null',
            'bookly_packages.created_at' => 'datetime not null',
            'bookly_payments.id' => 'int unsigned not null auto_increment primary key',
            'bookly_payments.coupon_id' => 'int unsigned null default null',
            'bookly_payments.type' => "enum('local','free','paypal','authorize_net','stripe','2checkout','payu_biz','payu_latam','payson','mollie','woocommerce','cloud_stripe') not null default 'local'",
            'bookly_payments.total' => "decimal(10,2) not null default '0.00'",
            'bookly_payments.tax' => "decimal(10,2) not null default '0.00'",
            'bookly_payments.paid' => "decimal(10,2) not null default '0.00'",
            'bookly_payments.paid_type' => "enum('in_full','deposit') not null default 'in_full'",
            'bookly_payments.gateway_price_correction' => "decimal(10,2) null default '0.00'",
            'bookly_payments.status' => "enum('pending','completed','rejected') not null default 'completed'",
            'bookly_payments.token' => 'varchar(255) null default null',
            'bookly_payments.details' => 'text null default null',
            'bookly_payments.created_at' => 'datetime not null',
            'bookly_payments.updated_at' => 'datetime not null',
            'bookly_schedule_item_breaks.id' => 'int unsigned not null auto_increment primary key',
            'bookly_schedule_item_breaks.staff_schedule_item_id' => 'int unsigned not null',
            'bookly_schedule_item_breaks.start_time' => 'time null default null',
            'bookly_schedule_item_breaks.end_time' => 'time null default null',
            'bookly_sent_notifications.id' => 'int unsigned not null auto_increment primary key',
            'bookly_sent_notifications.ref_id' => 'int unsigned not null',
            'bookly_sent_notifications.notification_id' => 'int unsigned not null',
            'bookly_sent_notifications.created_at' => 'datetime not null',
            'bookly_series.id' => 'int unsigned not null auto_increment primary key',
            'bookly_series.repeat' => 'varchar(255) null default null',
            'bookly_series.token' => 'varchar(255) not null',
            'bookly_service_discounts.id' => 'int unsigned not null auto_increment primary key',
            'bookly_service_discounts.service_id' => 'int unsigned not null',
            'bookly_service_discounts.discount_id' => 'int unsigned not null',
            'bookly_service_extras.id' => 'int unsigned not null auto_increment primary key',
            'bookly_service_extras.service_id' => 'int unsigned not null',
            'bookly_service_extras.attachment_id' => 'int unsigned null default null',
            'bookly_service_extras.title' => "varchar(255) null default ''",
            'bookly_service_extras.duration' => "int not null default '0'",
            'bookly_service_extras.price' => "decimal(10,2) not null default '0.00'",
            'bookly_service_extras.min_quantity' => "int not null default '0'",
            'bookly_service_extras.max_quantity' => "int not null default '1'",
            'bookly_service_extras.position' => "int not null default '9999'",
            'bookly_service_schedule_breaks.id' => 'int unsigned not null auto_increment primary key',
            'bookly_service_schedule_breaks.service_schedule_day_id' => 'int unsigned not null',
            'bookly_service_schedule_breaks.start_time' => 'time null default null',
            'bookly_service_schedule_breaks.end_time' => 'time null default null',
            'bookly_service_schedule_days.id' => 'int unsigned not null auto_increment primary key',
            'bookly_service_schedule_days.service_id' => 'int unsigned not null',
            'bookly_service_schedule_days.day_index' => 'smallint null default null',
            'bookly_service_schedule_days.start_time' => 'time null default null',
            'bookly_service_schedule_days.end_time' => 'time null default null',
            'bookly_service_special_days.id' => 'int unsigned not null auto_increment primary key',
            'bookly_service_special_days.service_id' => 'int unsigned not null',
            'bookly_service_special_days.date' => 'date null default null',
            'bookly_service_special_days.start_time' => 'time null default null',
            'bookly_service_special_days.end_time' => 'time null default null',
            'bookly_service_special_days_breaks.id' => 'int unsigned not null auto_increment primary key',
            'bookly_service_special_days_breaks.service_special_day_id' => 'int unsigned not null',
            'bookly_service_special_days_breaks.start_time' => 'time null default null',
            'bookly_service_special_days_breaks.end_time' => 'time null default null',
            'bookly_service_taxes.id' => 'int unsigned not null auto_increment primary key',
            'bookly_service_taxes.service_id' => 'int unsigned not null',
            'bookly_service_taxes.tax_id' => 'int unsigned not null',
            'bookly_services.id' => 'int unsigned not null auto_increment primary key',
            'bookly_services.category_id' => 'int unsigned null default null',
            'bookly_services.type' => "enum('simple','collaborative','compound','package') not null default 'simple'",
            'bookly_services.title' => "varchar(255) null default ''",
            'bookly_services.attachment_id' => 'int unsigned null default null',
            'bookly_services.duration' => "int not null default '900'",
            'bookly_services.slot_length' => "varchar(255) not null default 'default'",
            'bookly_services.price' => "decimal(10,2) not null default '0.00'",
            'bookly_services.color' => "varchar(255) not null default '#FFFFFF'",
            'bookly_services.deposit' => "varchar(100) not null default '100%'",
            'bookly_services.capacity_min' => "int not null default '1'",
            'bookly_services.capacity_max' => "int not null default '1'",
            'bookly_services.one_booking_per_slot' => "tinyint(1) not null default '0'",
            'bookly_services.padding_left' => "int not null default '0'",
            'bookly_services.padding_right' => "int not null default '0'",
            'bookly_services.info' => 'text null default null',
            'bookly_services.start_time_info' => "varchar(255) null default ''",
            'bookly_services.end_time_info' => "varchar(255) null default ''",
            'bookly_services.same_staff_for_subservices' => "tinyint(1) not null default '0'",
            'bookly_services.units_min' => "int unsigned not null default '1'",
            'bookly_services.units_max' => "int unsigned not null default '1'",
            'bookly_services.package_life_time' => 'int null default null',
            'bookly_services.package_size' => 'int null default null',
            'bookly_services.package_unassigned' => "tinyint(1) not null default '0'",
            'bookly_services.appointments_limit' => 'int null default null',
            'bookly_services.limit_period' => "enum('off','day','week','month','year','upcoming','calendar_day','calendar_week','calendar_month','calendar_year') not null default 'off'",
            'bookly_services.staff_preference' => "enum('order','least_occupied','most_occupied','least_occupied_for_period','most_occupied_for_period','least_expensive','most_expensive') not null default 'most_expensive'",
            'bookly_services.staff_preference_settings' => 'text null default null',
            'bookly_services.recurrence_enabled' => "tinyint(1) not null default '1'",
            'bookly_services.recurrence_frequencies' => "set('daily','weekly','biweekly','monthly') not null default 'daily,weekly,biweekly,monthly'",
            'bookly_services.time_requirements' => "enum('required','optional','off') not null default 'required'",
            'bookly_services.collaborative_equal_duration' => "tinyint(1) not null default '0'",
            'bookly_services.online_meetings' => "enum('off','zoom','google_meet') not null default 'off'",
            'bookly_services.final_step_url' => "varchar(512) not null default ''",
            'bookly_services.wc_product_id' => "int unsigned not null default '0'",
            'bookly_services.wc_cart_info_name' => 'varchar(255) null default null',
            'bookly_services.wc_cart_info' => 'text null default null',
            'bookly_services.min_time_prior_booking' => 'int null default null',
            'bookly_services.min_time_prior_cancel' => 'int null default null',
            'bookly_services.visibility' => "enum('public','private','group') not null default 'public'",
            'bookly_services.position' => "int not null default '9999'",
            'bookly_shop.id' => 'int unsigned not null auto_increment primary key',
            'bookly_shop.plugin_id' => 'int unsigned not null',
            'bookly_shop.type' => "enum('plugin','bundle') not null default 'plugin'",
            'bookly_shop.highlighted' => "tinyint(1) not null default '0'",
            'bookly_shop.priority' => "int unsigned null default '0'",
            'bookly_shop.demo_url' => 'varchar(255) null default null',
            'bookly_shop.title' => 'varchar(255) not null',
            'bookly_shop.slug' => 'varchar(255) not null',
            'bookly_shop.description' => 'text not null',
            'bookly_shop.url' => 'varchar(255) not null',
            'bookly_shop.icon' => 'varchar(255) not null',
            'bookly_shop.price' => 'decimal(10,2) not null',
            'bookly_shop.sales' => 'int unsigned not null',
            'bookly_shop.rating' => 'decimal(10,2) not null',
            'bookly_shop.reviews' => 'int unsigned not null',
            'bookly_shop.published' => 'datetime not null',
            'bookly_shop.seen' => "tinyint(1) not null default '0'",
            'bookly_shop.created_at' => 'datetime not null',
            'bookly_special_days_breaks.id' => 'int unsigned not null auto_increment primary key',
            'bookly_special_days_breaks.staff_special_day_id' => 'int unsigned not null',
            'bookly_special_days_breaks.start_time' => 'time null default null',
            'bookly_special_days_breaks.end_time' => 'time null default null',
            'bookly_staff.id' => 'int unsigned not null auto_increment primary key',
            'bookly_staff.category_id' => 'int unsigned null default null',
            'bookly_staff.wp_user_id' => 'bigint unsigned null default null',
            'bookly_staff.attachment_id' => 'int unsigned null default null',
            'bookly_staff.full_name' => 'varchar(255) null default null',
            'bookly_staff.email' => 'varchar(255) null default null',
            'bookly_staff.phone' => 'varchar(255) null default null',
            'bookly_staff.time_zone' => 'varchar(255) null default null',
            'bookly_staff.info' => 'text null default null',
            'bookly_staff.working_time_limit' => 'int unsigned null default null',
            'bookly_staff.visibility' => "enum('public','private','archive') not null default 'public'",
            'bookly_staff.position' => "int not null default '9999'",
            'bookly_staff.google_data' => 'text null default null',
            'bookly_staff.outlook_data' => 'text null default null',
            'bookly_staff.zoom_authentication' => "enum('default','jwt','oauth') not null default 'default'",
            'bookly_staff.zoom_jwt_api_key' => 'varchar(255) null default null',
            'bookly_staff.zoom_jwt_api_secret' => 'varchar(255) null default null',
            'bookly_staff.zoom_oauth_token' => 'text null default null',
            'bookly_staff.icalendar' => "tinyint(1) not null default '0'",
            'bookly_staff.icalendar_token' => 'text null default null',
            'bookly_staff.icalendar_days_before' => "int not null default '365'",
            'bookly_staff.icalendar_days_after' => "int not null default '365'",
            'bookly_staff.color' => "varchar(255) not null default '#dddddd'",
            'bookly_staff.gateways' => 'varchar(255) null default null',
            'bookly_staff_categories.id' => 'int unsigned not null auto_increment primary key',
            'bookly_staff_categories.name' => 'varchar(255) not null',
            'bookly_staff_categories.position' => "int not null default '9999'",
            'bookly_staff_locations.id' => 'int unsigned not null auto_increment primary key',
            'bookly_staff_locations.staff_id' => 'int unsigned not null',
            'bookly_staff_locations.location_id' => 'int unsigned not null',
            'bookly_staff_locations.custom_services' => "tinyint(1) not null default '0'",
            'bookly_staff_locations.custom_schedule' => "tinyint(1) not null default '0'",
            'bookly_staff_locations.custom_special_days' => "tinyint(1) not null default '0'",
            'bookly_staff_preference_orders.id' => 'int unsigned not null auto_increment primary key',
            'bookly_staff_preference_orders.service_id' => 'int unsigned not null',
            'bookly_staff_preference_orders.staff_id' => 'int unsigned not null',
            'bookly_staff_preference_orders.position' => "int not null default '9999'",
            'bookly_staff_schedule_items.id' => 'int unsigned not null auto_increment primary key',
            'bookly_staff_schedule_items.staff_id' => 'int unsigned not null',
            'bookly_staff_schedule_items.location_id' => 'int unsigned null default null',
            'bookly_staff_schedule_items.day_index' => 'int unsigned not null',
            'bookly_staff_schedule_items.start_time' => 'time null default null',
            'bookly_staff_schedule_items.end_time' => 'time null default null',
            'bookly_staff_services.id' => 'int unsigned not null auto_increment primary key',
            'bookly_staff_services.staff_id' => 'int unsigned not null',
            'bookly_staff_services.service_id' => 'int unsigned not null',
            'bookly_staff_services.location_id' => 'int unsigned null default null',
            'bookly_staff_services.price' => "decimal(10,2) not null default '0.00'",
            'bookly_staff_services.deposit' => "varchar(100) not null default '100%'",
            'bookly_staff_services.capacity_min' => "int not null default '1'",
            'bookly_staff_services.capacity_max' => "int not null default '1'",
            'bookly_staff_special_days.id' => 'int unsigned not null auto_increment primary key',
            'bookly_staff_special_days.staff_id' => 'int unsigned not null',
            'bookly_staff_special_days.location_id' => 'int unsigned null default null',
            'bookly_staff_special_days.date' => 'date null default null',
            'bookly_staff_special_days.start_time' => 'time null default null',
            'bookly_staff_special_days.end_time' => 'time null default null',
            'bookly_staff_special_hours.id' => 'int unsigned not null auto_increment primary key',
            'bookly_staff_special_hours.staff_id' => 'int unsigned not null',
            'bookly_staff_special_hours.service_id' => 'int unsigned not null',
            'bookly_staff_special_hours.location_id' => 'int unsigned null default null',
            'bookly_staff_special_hours.start_time' => 'time null default null',
            'bookly_staff_special_hours.end_time' => 'time null default null',
            'bookly_staff_special_hours.days' => "varchar(255) not null default '1,2,3,4,5,6,7'",
            'bookly_staff_special_hours.price' => "decimal(10,2) not null default '0.00'",
            'bookly_stats.id' => 'int unsigned not null auto_increment primary key',
            'bookly_stats.name' => 'varchar(255) not null',
            'bookly_stats.value' => 'text null default null',
            'bookly_stats.created_at' => 'datetime not null',
            'bookly_sub_services.id' => 'int unsigned not null auto_increment primary key',
            'bookly_sub_services.type' => "enum('service','spare_time') not null default 'service'",
            'bookly_sub_services.service_id' => 'int unsigned not null',
            'bookly_sub_services.sub_service_id' => 'int unsigned null default null',
            'bookly_sub_services.duration' => 'int null default null',
            'bookly_sub_services.position' => "int not null default '9999'",
            'bookly_taxes.id' => 'int unsigned not null auto_increment primary key',
            'bookly_taxes.title' => "varchar(255) null default ''",
            'bookly_taxes.rate' => "decimal(10,3) not null default '0.000'",
        );

        $prefix_len = strlen( $wpdb->prefix );
        $key        = substr( $table, $prefix_len ) . '.' . $column;
        if ( isset( $fields[ $key ] ) ) {
            wp_send_json_success( $fields[ $key ] );
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Execute query
     */
    public static function executeQuery()
    {
        $success = self::execute( self::parameter( 'query' ) );

        if ( $success === true ) {
            wp_send_json_success( array( 'message' => 'Query completed successfully' ) );
        } else {
            wp_send_json_error( array( 'message' => $success ) );
        }
    }

    /**
     * Execute query
     */
    public static function dropColumn()
    {
        global $wpdb;

        /** @var Lib\Base\Entity $entity */
        $entity = self::parameter( 'entity' );
        $column = self::parameter( 'column' );
        $table  = $entity::getTableName();

        $get_foreign_keys = sprintf(
            'SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = SCHEMA() AND TABLE_NAME = "%s" AND COLUMN_NAME = "%s" AND REFERENCED_TABLE_NAME IS NOT NULL',
            $table,
            $column
        );
        $constraints = $wpdb->get_results( $wpdb->prepare( $get_foreign_keys, $column ) );
        foreach ( $constraints as $foreign_key ) {
            $wpdb->query( "ALTER TABLE `$table` DROP FOREIGN KEY `$foreign_key->CONSTRAINT_NAME`" );
        }

        $query  = 'ALTER TABLE `' . $table . '` DROP COLUMN `' . $column . '`';

        $success = self::execute( $query );

        if ( $success === true ) {
            wp_send_json_success( array( 'message' => 'Query completed successfully' ) );
        } else {
            wp_send_json_error( array( 'message' => $success ) );
        }
    }

    /**
     * manual
     */
    public static function getConstraintData()
    {
        $table      = self::parameter( 'table' );
        $column     = self::parameter( 'column' );
        $ref_table  = self::parameter( 'ref_table' );
        $ref_column = self::parameter( 'ref_column' );

        wp_send_json_success ( QueryBuilder::getConstraintRules( $table, $column, $ref_table, $ref_column ) );
    }

    /**
     * manual
     */
    public static function addConstraint()
    {
        $table  = self::parameter( 'table' );
        $column = self::parameter( 'column' );
        $ref_table  = self::parameter( 'ref_table' );
        $ref_column = self::parameter( 'ref_column' );

        $sql = sprintf( 'ALTER TABLE `%s` ADD CONSTRAINT FOREIGN KEY (`%s`) REFERENCES `%s` (`%s`)', $table, $column, $ref_table, $ref_column );
        $delete_rule = self::parameter( 'delete_rule' );
        switch ( $delete_rule ) {
            case 'RESTRICT':
            case 'CASCADE':
            case 'SET NULL':
            case 'NO ACTIONS':
                $sql .= ' ON DELETE ' . $delete_rule;
                break;
            default:
                wp_send_json_error( array( 'message' => 'Select ON DELETE action' ) );
        }
        $update_rule = self::parameter( 'update_rule' );
        switch ( $update_rule ) {
            case 'RESTRICT':
            case 'CASCADE':
            case 'SET NULL':
            case 'NO ACTIONS':
                $sql .= ' ON UPDATE ' . $update_rule;
                break;
            default:
                wp_send_json_error( array( 'message' => 'Select ON UPDATE action' ) );
        }

        $success = self::execute( $sql );

        if ( $success === true ) {
            wp_send_json_success( array( 'message' => 'Constraint created' ) );
        } else {
            wp_send_json_error( array( 'message' => $success ) );
        }
    }

    /**
     * manual
     */
    public static function fixConsistency()
    {
        $rule   = self::parameter( 'rule' );
        $table  = self::parameter( 'table' );
        $column = self::parameter( 'column' );
        $ref_table  = self::parameter( 'ref_table' );
        $ref_column = self::parameter( 'ref_column' );

        switch ( $rule ) {
            case 'CASCADE':
                $sql = sprintf( 'DELETE FROM `%s` WHERE `%s` NOT IN ( SELECT `%s` FROM `%s` )',
                    $table, $column, $ref_column, $ref_table );
                break;
            case 'SET NULL':
                $sql = sprintf( 'UPDATE `%s` SET `%s` = NULL WHERE `%s` NOT IN ( SELECT `%s` FROM `%s` )',
                    $table, $column, $column, $ref_column, $ref_table );
                break;
            default:
                wp_send_json_success( array( 'message' => 'No manipulation actions were performed' ) );
        }

        $success = self::execute( $sql );

        if ( $success === true ) {
            wp_send_json_success( array( 'message' => 'Successful, click Add constraint' ) );
        } else {
            wp_send_json_error( array( 'message' => $success ) );
        }
    }

    public static function fixDataBaseSchema()
    {
        $errors  = array();
        $queries = 0;
        $schema  = new Schema();
        /** @var Lib\Base\Plugin $plugin */
        foreach ( apply_filters( 'bookly_plugins', array() ) as $plugin ) {
            foreach ( $plugin::getEntityClasses() as $entity_class ) {
                $table_name = $entity_class::getTableName();
                if ( ! $schema->existsTable( $table_name ) ) {
                    $queries ++;
                    $success = self::execute( QueryBuilder::getCreateTable( $table_name ) );
                    if ( $success !== true ) {
                        $errors[] = sprintf( 'Can`t create table <b>%s</b>, Error:%s', $table_name, $success );
                    }
                }
                if ( $schema->existsTable( $table_name ) ) {
                    $table_structure = $schema->getTableStructure( $table_name );
                    $entity_schema   = $entity_class::getSchema();

                    // Comparing model schema with real DB schema
                    foreach ( $entity_schema as $column => $data ) {
                        if ( array_key_exists( $column, $table_structure ) ) {
                            $expect = QueryBuilder::getColumnData( $table_name, $column );
                            $actual = $table_structure[ $column ];
                            unset( $expect['key'], $actual['key'] );
                            if ( $expect && array_diff_assoc( $actual, $expect ) ) {
                                $sql = QueryBuilder::getChangeColumn( $table_name, $column );
                                if ( $table_structure[ $column ]['key'] == 'PRI' ) {
                                    $sql = str_replace( ' primary key', '', $sql );
                                }
                                $queries ++;
                                $success = self::execute( $sql );
                                if ( $success !== true ) {
                                    $errors[] = sprintf( 'Can`t change column <b>%s.%s</b>, Error:%s', $table_name, $column, $success );
                                }
                            }
                        } else {
                            $queries ++;
                            $success = self::execute( QueryBuilder::getAddColumn( $table_name, $column ) );
                            if ( $success !== true ) {
                                $errors[] = sprintf( 'Can`t add column <b>%s.%s</b>, Error:%s', $table_name, $column, $success );
                            }
                        }
                    }
                }
            }

            foreach ( $plugin::getEntityClasses() as $entity_class ) {
                $table_name = $entity_class::getTableName();
                if ( $schema->existsTable( $table_name ) ) {
                    $entity_constraints = $entity_class::getConstraints();
                    $table_constraints  = $schema->getTableConstraints( $table_name );
                    // Comparing model constraints with real DB constraints
                    foreach ( $entity_constraints as $constraint ) {
                        $key = $constraint['column_name'] . $constraint['referenced_table_name'] . $constraint['referenced_column_name'];
                        if ( ! array_key_exists( $key, $table_constraints ) ) {
                            $query = QueryBuilder::getAddConstraint( $table_name, $constraint['column_name'], $constraint['referenced_table_name'], $constraint['referenced_column_name'] );
                            if ( $query !== '' ) {
                                $queries ++;
                                $success = self::execute( $query );
                                if ( $success !== true ) {
                                    $errors[] = sprintf( 'Can`t add constraint <b>%s.%s</b> REFERENCES `%s` (`%s`), Error:%s', $table_name, $constraint['column_name'], $constraint['referenced_table_name'], $constraint['referenced_column_name'], $success );
                                }
                            }
                        }
                    }

                    foreach ( $table_constraints as $constraint ) {
                        if ( $constraint['reference_exists'] === false ) {
                            $queries ++;
                            $success = self::execute( QueryBuilder::getDropForeignKey( $table_name, $constraint['constraint_name'] ) );
                            if ( $success !== true ) {
                                $errors[] = sprintf( 'Can`t drop foreign key <b>%s</b>, Error:%s', $constraint['constraint_name'], $success );
                            }
                        }
                    }
                }
            }
        }

        $message = ( $queries - count( $errors ) ) . ' queries completed successfully, with errors ' . count( $errors );
        $errors
            ? wp_send_json_error( compact( 'errors', 'message' ) )
            : wp_send_json_success( compact( 'message' ) );
    }

    /**
     * @param string $sql
     * @return bool|string
     */
    protected static function execute( $sql )
    {
        global $wpdb;

        ob_start();
        $result = $wpdb->query( $sql );
        ob_end_clean();

        return $result !== false ? true : $wpdb->last_error;
    }

    public static function runTest()
    {
        $test_name  = self::parameter( 'test_name' );
        $test_class = '\Bookly\Backend\Modules\Debug\Lib\Tests\\' . $test_name;
        /** @var \Bookly\Backend\Modules\Debug\Lib\Tests\Base $test */
        $test = new $test_class( self::parameter( 'test_data' ) );
        if ( $test->execute() ) {
            wp_send_json_success();
        } else {
            wp_send_json_error( array( 'test_name' => $test->getName(), 'error' => $test->error() ) );
        }
    }

    /**
     *
     */
    public static function runTool()
    {
        $tool_name  = self::parameter( 'tool_name' );
        $tool_class = '\Bookly\Backend\Modules\Debug\Lib\Tools\\' . $tool_name;
        /** @var \Bookly\Backend\Modules\Debug\Lib\Tools\Base $tool */
        $tool = new $tool_class( self::parameter( 'tool_data' ) );
        if ( $tool->execute() ) {
            wp_send_json_success( array( 'test_name' => $tool->getName(), 'alerts' => $tool->alerts() ) );
        } else {
            wp_send_json_error( array( 'test_name' => $tool->getName(), 'alerts' => $tool->alerts() ) );
        }

    }

    /**
     * Extend parent method to control access on staff member level.
     *
     * @param string $action
     * @return bool
     */
    protected static function hasAccess( $action )
    {
        switch ( $action ) {
            case 'runTest':
                if ( self::parameter( 'test_name' ) == 'Session' ) {
                    return true;
                } else {
                    return Lib\Utils\Common::isCurrentUserAdmin();
                }
            default:
                return parent::hasAccess( $action );
        }
    }

    /**
     * Override parent method to exclude actions from CSRF token verification.
     *
     * @param string $action
     * @return bool
     */
    protected static function csrfTokenValid( $action = null )
    {
        $excluded_actions = array(
            'runTest',
        );

        return in_array( $action, $excluded_actions ) || parent::csrfTokenValid( $action );
    }
}