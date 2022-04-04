<?php
namespace BooklyPro\Lib;

use Bookly\Lib as BooklyLib;

/**
 * Class Updates
 * @package BooklyPro\Lib
 */
class Updater extends BooklyLib\Base\Updater
{
    public function update_4_5()
    {
        add_option( 'bookly_app_show_tips', '0' );
        $this->addL10nOptions( array(
            'bookly_l10n_label_tips' => __( 'Tips', 'bookly' ),
            'bookly_l10n_button_apply_tips' => __( 'Apply', 'bookly' ),
            'bookly_l10n_button_applied_tips' => __( 'Applied', 'bookly' ),
            'bookly_l10n_tips_error' => __( 'Incorrect value', 'bookly' ),
        ) );
    }

    public function update_4_4()
    {
        $this->addL10nOptions( array(
            'bookly_l10n_info_payment_step_without_intersected_gateways' => __( 'No payment methods available for one or more staff. Please contact service provider.', 'bookly' ),
        ) );
        add_option( 'bookly_cal_frontend_enabled', '0' );
    }

    public function update_4_3()
    {
        $order = explode( ',', get_option( 'bookly_pmt_order' ) );
        if ( $order ) {
            $pmt_order = array();
            $gateways = array(
                'stripe' => 'bookly-addon-stripe',
                'authorize_net' => 'bookly-addon-authorize-net',
                '2checkout' => 'bookly-addon-2checkout',
                'payu_biz' => 'bookly-addon-payu-biz',
                'payu_latam' => 'bookly-addon-payu-latam',
                'payson' => 'bookly-addon-payson',
                'mollie' => 'bookly-addon-mollie',
            );
            foreach ( $order as $gateway ) {
                $pmt_order[] = array_key_exists( $gateway, $gateways )
                    ? $gateways[ $gateway ]
                    : $gateway;
            }
            update_option( 'bookly_pmt_order', implode( ',', $pmt_order ) );
        }
    }

    public function update_4_2()
    {
        global $wpdb;

        $charset_collate = $wpdb->has_cap( 'collation' )
            ? $wpdb->get_charset_collate()
            : 'DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci';

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . $this->getTableName( 'bookly_email_log' ) . '` (
                `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `to`         VARCHAR(255) NOT NULL,
                `subject`    VARCHAR(255) NOT NULL,
                `body`       TEXT NOT NULL,
                `headers`    TEXT NOT NULL,
                `attach`     TEXT NOT NULL,
                `type`       VARCHAR(255) NOT NULL DEFAULT "",
                `created_at` DATETIME NOT NULL
             ) ENGINE = INNODB
              ' . $charset_collate
        );

        add_option( 'bookly_save_email_logs', '1' );
    }

    public function update_4_1()
    {
        add_option( 'bookly_appointments_main_value', 'provider' );
        add_option( 'bookly_appointments_displayed_time_slots', 'all' );
    }

    public function update_4_0()
    {
        $notifications[] = array(
            'gateway' => 'email',
            'type' => 'staff_new_wp_user',
            'name' => __( 'New staff member\'s WordPress user login details', 'bookly' ),
            'subject' => __( 'New staff member', 'bookly' ),
            'message' => __( "Hello.\n\nAn account was created for you at {site_address}\n\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ),
            'active' => 1,
            'to_staff' => 1,
            'settings' => '[]',
        );
        $notifications[] = array(
            'gateway' => 'sms',
            'type' => 'staff_new_wp_user',
            'name' => __( 'New staff member\'s WordPress user login details', 'bookly' ),
            'message' => __( "Hello.\n\nAn account was created for you at {site_address}\n\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ),
            'active' => 1,
            'to_staff' => 1,
            'settings' => '[]',
        );

        $this->addNotifications( $notifications );
        add_option( 'bookly_staff_new_account_role', 'subscriber' );
        add_option( 'bookly_appointments_time_delimiter', '0' );
    }

    public function update_3_6()
    {
        $bookly_gc_event_appointment_info = get_option( 'bookly_gc_event_appointment_info' );
        if ( $bookly_gc_event_appointment_info !== false ) {
            if ( $bookly_gc_event_appointment_info !== '' ) {
                $bookly_gc_event_appointment_info .= PHP_EOL;
            }
            $bookly_gc_event_client_info = get_option( 'bookly_gc_event_client_info', '' );
            $replace = array(
                '{appointment_notes}' => '{participant.appointment_notes}',
                '{client_email}' => '{participant.client_email}',
                '{client_first_name}' => '{participant.client_first_name}',
                '{client_last_name}' => '{participant.client_last_name}',
                '{client_name}' => '{participant.client_name}',
                '{client_phone}' => '{participant.client_phone}',
                '{payment_status}' => '{participant.payment_status}',
                '{payment_type}' => '{participant.payment_type}',
                '{status}' => '{participant.status}',
                '{total_price}' => '{participant.total_price}',
                '{client_address}' => '{participant.client_address}',
                '{custom_fields}' => '{participant.custom_fields}',
                '{number_of_persons}' => '{participant.number_of_persons}',
                '{extras}' => '{participant.extras}',
                '{extras_total_price}' => '{participant.extras_total_price}',
            );
            $bookly_gc_event_client_info = '{#each participants as participant}' . PHP_EOL . strtr( $bookly_gc_event_client_info, $replace ) . PHP_EOL . '{/each}';

            add_option( 'bookly_gc_event_description', $bookly_gc_event_appointment_info . $bookly_gc_event_client_info );
            delete_option( 'bookly_gc_event_appointment_info' );
            delete_option( 'bookly_gc_event_client_info' );
        }

        add_option( 'bookly_zoom_authentication', 'jwt' );
        add_option( 'bookly_zoom_oauth_client_id', '' );
        add_option( 'bookly_zoom_oauth_client_secret', '' );
        add_option( 'bookly_zoom_oauth_token', '' );
    }

    public function update_2_9()
    {
        add_option( 'bookly_cst_limit_statuses', array( 'waitlisted' ) );
    }

    public function update_2_5()
    {
        add_option( 'bookly_zoom_jwt_api_key', '' );
        add_option( 'bookly_zoom_jwt_api_secret', '' );
    }

    public function update_2_2()
    {
        $address_show_fields = (array) get_option( 'bookly_cst_address_show_fields' );
        $fields = array();
        foreach ( $address_show_fields as $field_name => $attributes ) {
            if ( (bool) $attributes['show'] ) {
                $fields[] = '{' . $field_name . '}';
            }
        }
        $this->addL10nOptions( array(
            'bookly_l10n_cst_address_template' => implode( ', ', $fields ),
        ) );
    }

    public function update_2_1()
    {
        // Create WP role for bookly supervisor
        $capabilities = array();
        if ( $subscriber = get_role( 'subscriber' ) ) {
            $capabilities = $subscriber->capabilities;
        }

        // Fix subscribers access to dashboard with woocommerce.
        $capabilities['view_admin_dashboard'] = true;

        $capabilities['manage_bookly'] = true;

        add_role( 'bookly_administrator', 'Bookly Administrator', $capabilities );

        $this->addL10nOptions( array(
            'bookly_l10n_info_payment_step_with_100percents_off_price' => __( 'You are not required to pay for the booked services, click Next to complete the booking process.', 'bookly' ),
        ) );

        update_option( 'bookly_pr_data', array(
            'SW1wb3J0YW50ITxici8+SXQgbG9va3MgbGlrZSB5b3UgYXJlIHVzaW5nIGFuIGlsbGVnYWwgY29weSBvZiBCb29rbHkgUHJvLiBBbmQgaXQgbWF5IGNvbnRhaW4gYSBtYWxpY2lvdXMgY29kZSwgYSB0cm9qYW4gb3IgYSBiYWNrZG9vci4=',
            'Q29uc2lkZXIgc3dpdGNoaW5nIHRvIHRoZSBsZWdhbCBjb3B5IG9mIEJvb2tseSBQcm8gdGhhdCBpbmNsdWRlcyBhbGwgZmVhdHVyZXMsIGxpZmV0aW1lIGZyZWUgdXBkYXRlcywgYW5kIDI0Lzcgc3VwcG9ydC4=',
            'WW91IGNhbiBidXkgYSBsZWdhbCBjb3B5IG9uIG91ciB3ZWJzaXRlIDxhIGhyZWY9Imh0dHBzOi8vd3d3LmJvb2tpbmctd3AtcGx1Z2luLmNvbSIgdGFyZ2V0PSJfYmxhbmsiPnd3dy5ib29raW5nLXdwLXBsdWdpbi5jb208L2E+LCBvciBjb250YWN0IHVzIGF0IDxhIGhyZWY9Im1haWx0bzpzdXBwb3J0QGJvb2tseS5pbmZvIj5zdXBwb3J0QGJvb2tseS5pbmZvPC9hPiBmb3IgYW55IGFzc2lzdGFuY2Uu',
        ) );
    }

    public function update_2_0()
    {
        if ( get_option( 'bookly_paypal_timeout', 'missing' ) === 'missing' ) {
            add_option( 'bookly_paypal_timeout', '0' );
        }
    }

    public function update_1_8()
    {
        $this->upgradeCharsetCollate( array(
            'bookly_staff_categories',
            'bookly_staff_preference_orders',
        ) );
    }

    public function update_1_4()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $self = $this;
        $notifications_table = $this->getTableName( 'bookly_notifications' );
        $notifications = array(
            'client_new_wp_user' => array( 'type' => 'customer_new_wp_user', 'name' => __( 'New customer\'s WordPress user login details', 'bookly' ) ),
            'customer_birthday'  => array( 'type' => 'customer_birthday', 'name' => __( 'Customer\'s birthday', 'bookly' ) ),
            'client_approved_appointment_cart' => array( 'type' => 'new_booking_combined', 'name' => __( 'Notification to customer about approved appointments', 'bookly' ) ),
            'client_pending_appointment_cart'  => array( 'type' => 'new_booking_combined', 'name' => __( 'Notification to customer about pending appointments', 'bookly' ) ),
        );

        // Changes in schema
        $disposable_options[] = $this->disposable( __FUNCTION__ . '-1', function () use ( $self, $wpdb, $notifications_table, $notifications ) {
            if ( ! $self->existsColumn( 'bookly_notifications', 'name' ) ) {
                $self->alterTables( array(
                    'bookly_notifications' => array(
                        'ALTER TABLE `%s` ADD COLUMN `name` VARCHAR(255) NOT NULL DEFAULT "" AFTER `active`',
                    ),
                ) );
            }

            $update_name = 'UPDATE `' . $notifications_table . '` SET `name` = %s WHERE `type` = %s AND name = \'\'';
            foreach ( $notifications as $type => $value ) {
                $wpdb->query( $wpdb->prepare( $update_name, $value['name'], $type ) );

                switch ( substr( $type, 0, 6 ) ) {
                    case 'staff_':
                        $wpdb->query( sprintf( 'UPDATE `%s` SET `to_staff` = 1 WHERE `type` = "%s"', $notifications_table, $type ) );
                        break;
                    case 'client':
                        $wpdb->query( sprintf( 'UPDATE `%s` SET `to_customer` = 1 WHERE `type` = "%s"', $notifications_table, $type ) );
                        break;
                }
            }
        } );

        // WPML
        $disposable_options[] = $this->disposable( __FUNCTION__ . '-2', function () use ( $self, $wpdb, $notifications_table, $notifications ) {
            $records = $wpdb->get_results( $wpdb->prepare( 'SELECT id, `type`, `gateway` FROM `' . $notifications_table . '` WHERE COALESCE( `settings`, \'[]\' ) = \'[]\' AND `type` IN (' . implode( ', ', array_fill( 0, count( $notifications ), '%s' ) ) . ')', array_keys( $notifications ) ), ARRAY_A );
            $strings = array();
            foreach ( $records as $record ) {
                $type = $record['type'];
                if ( isset( $notifications[ $type ]['type'] ) && $type != $notifications[ $type ]['type'] ) {
                    $key   = sprintf( '%s_%s_%d', $record['gateway'], $type, $record['id'] );
                    $value = sprintf( '%s_%s_%d', $record['gateway'], $notifications[ $type ]['type'], $record['id'] );
                    $strings[ $key ] = $value;
                    if ( $record['gateway'] == 'email' ) {
                        $strings[ $key . '_subject' ] = $value . '_subject';
                    }
                }
            }
            $self->renameL10nStrings( $strings, false );
        } );

        // Add settings for notifications
        $disposable_options[] = $this->disposable( __FUNCTION__ . '-3', function () use ( $wpdb, $notifications_table, $notifications ) {
            $update_settings  = 'UPDATE `' . $notifications_table . '` SET `type` = %s, `settings` = %s, `active` = %d WHERE id = %d';
            $default_settings = '{"status":"any","option":2,"services":{"any":"any","ids":[]},"offset_hours":2,"perform":"before","at_hour":9,"before_at_hour":18,"offset_before_hours":-24,"offset_bidirectional_hours":0}';
            $records = $wpdb->get_results( $wpdb->prepare( 'SELECT id, `type`, `gateway`, `message`, `active`, `subject` FROM `' . $notifications_table . '` WHERE COALESCE( `settings`, \'[]\' ) = \'[]\' AND `type` IN (' . implode( ', ', array_fill( 0, count( $notifications ), '%s' ) ) . ')', array_keys( $notifications ) ), ARRAY_A );
            foreach ( $records as $record ) {
                $new_type = $notifications[ $record['type'] ]['type'];
                switch ( $record['type'] ) {
                    case 'client_approved_appointment_cart':
                    case 'client_pending_appointment_cart':
                        $new_active = get_option( 'bookly_cst_combined_notifications' ) ? $record['active'] : 0;
                        break;
                    default:
                        $new_active = $record['active'];
                }

                $wpdb->query( $wpdb->prepare( $update_settings, $new_type, $default_settings, $new_active, $record['id'] ) );
            }
        } );

        if ( get_option( 'bookly_cst_combined_notifications' ) == 0 ) {
            // Deactivate combine notifications
            $wpdb->query( 'UPDATE `' . $notifications_table . '` SET `active` = 0 WHERE `type` = \'new_booking_combined\'' );
        }
        delete_option( 'bookly_cst_combined_notifications' );
        foreach ( $disposable_options as $option_name ) {
            delete_option( $option_name );
        }
    }

    public function update_1_1()
    {
        global $wpdb;

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . $this->getTableName( 'bookly_staff_categories' ) . '` (
                `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name`     VARCHAR(255) NOT NULL,
                `position` INT NOT NULL DEFAULT 9999
             ) ENGINE = INNODB
             DEFAULT CHARACTER SET = utf8
             COLLATE = utf8_general_ci'
        );

        $wpdb->query(
            'ALTER TABLE `' . $this->getTableName( 'bookly_staff' ) . '`
             ADD CONSTRAINT
                FOREIGN KEY (category_id)
                REFERENCES ' . $this->getTableName( 'bookly_staff_categories' ) . '(id)
                ON DELETE SET NULL
                ON UPDATE CASCADE'
        );

        $bookly_gc_event_client_info = __( 'Name', 'bookly' ) . ': {client_name}' . PHP_EOL . __( 'Email', 'bookly' ) . ': {client_email}' . PHP_EOL . __( 'Phone', 'bookly' ) . ': {client_phone}' . PHP_EOL . '{custom_fields}';
        if ( BooklyLib\Config::serviceExtrasActive() ) {
            $bookly_gc_event_client_info .= PHP_EOL . __( 'Extras', 'bookly' ) . ': {extras}' . PHP_EOL;
        }

        add_option( 'bookly_gc_event_client_info', $bookly_gc_event_client_info );
        add_option( 'bookly_gc_event_appointment_info', '' );
        delete_option( 'bookly_grace_hide_admin_notice_time' );
        $this->renameUserMeta( array( 'show_purchase_reminder' => 'bookly_show_purchase_reminder' ) );

        // Create WP role for bookly supervisor
        $capabilities = array();
        if ( $subscriber = get_role( 'subscriber' ) ) {
            $capabilities = $subscriber->capabilities;
        }
        $capabilities['manage_bookly_appointments'] = true;
        add_role( 'bookly_supervisor', 'Bookly Supervisor', $capabilities );
    }
}