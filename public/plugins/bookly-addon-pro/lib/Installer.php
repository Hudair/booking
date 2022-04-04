<?php
namespace BooklyPro\Lib;

use Bookly\Lib as BooklyLib;

/**
 * Class Installer
 *
 * @package BooklyPro\Lib
 */
class Installer extends Base\Installer
{
    /** @var array */
    private $notifications = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Notifications email & sms.
        $default_settings = json_decode( '{"status":"any","option":2,"services":{"any":"any","ids":[]},"offset_hours":2,"perform":"before","at_hour":9,"before_at_hour":18,"offset_before_hours":-24,"offset_bidirectional_hours":0}', true );
        $this->notifications[] = array(
            'gateway' => 'email',
            'type' => 'customer_new_wp_user',
            'name' => __( 'Notification to customer about their WordPress user login details', 'bookly' ),
            'subject' => __( 'New customer', 'bookly' ),
            'message' => __( "Hello.\n\nAn account was created for you at {site_address}\n\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ),
            'active' => 1,
            'to_customer' => 1,
            'settings' => $default_settings,
        );
        $this->notifications[] = array(
            'gateway' => 'email',
            'type' => 'staff_new_wp_user',
            'name' => __( 'New staff member\'s WordPress user login details', 'bookly' ),
            'subject' => __( 'New staff member', 'bookly' ),
            'message' => __( "Hello.\n\nAn account was created for you at {site_address}\n\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ),
            'active' => 1,
            'to_staff' => 1,
            'settings' => $default_settings,
        );
        $settings = $default_settings;
        $settings['option'] = 2;
        $settings['offset_hours'] = 1;
        $settings['perform'] = 'before';
        $settings['at_hour'] = 18;
        $settings['offset_bidirectional_hours'] = - 24;
        $this->notifications[] = array(
            'gateway' => 'email',
            'type' => 'appointment_reminder',
            'name' => __( 'Evening reminder to customer about next day appointment (requires cron setup)', 'bookly' ),
            'subject' => __( 'Your appointment at {company_name}', 'bookly' ),
            'message' => __( "Dear {client_name}.\n\nWe would like to remind you that you have booked {service_name} tomorrow at {appointment_time}. We are waiting for you at {company_address}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'to_customer' => 1,
            'settings' => $settings,
        );
        $settings = $default_settings;
        $settings['option'] = 2;
        $settings['at_hour'] = 21;
        $this->notifications[] = array(
            'gateway' => 'email',
            'type' => 'appointment_reminder',
            'name' => __( 'Follow-up message in the same day after appointment (requires cron setup)', 'bookly' ),
            'subject' => __( 'Your visit to {company_name}', 'bookly' ),
            'message' => __( "Dear {client_name}.\n\nThank you for choosing {company_name}. We hope you were satisfied with your {service_name}.\n\nThank you and we look forward to seeing you again soon.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'to_customer' => 1,
            'settings' => $settings,
        );
        $settings = $default_settings;
        $settings['at_hour'] = 9;
        $this->notifications[] = array(
            'gateway' => 'email',
            'type' => 'customer_birthday',
            'name' => __( 'Customer birthday greeting (requires cron setup)', 'bookly' ),
            'subject' => __( 'Happy Birthday!', 'bookly' ),
            'message' => __( "Dear {client_name},\n\nHappy birthday!\nWe wish you all the best.\nMay you and your family be happy and healthy.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'to_customer' => 1,
            'settings' => $settings,
        );
        $settings = $default_settings;
        $settings['option'] = 3;
        $settings['before_at_hour'] = 18;
        $settings['offset_before_hours'] = - 24;
        $this->notifications[] = array(
            'gateway' => 'email',
            'type' => 'staff_day_agenda',
            'name' => __( 'Evening notification with the next day agenda to staff member (requires cron setup)', 'bookly' ),
            'subject' => __( 'Your agenda for {tomorrow_date}', 'bookly' ),
            'message' => __( "Hello.\n\nYour agenda for tomorrow is:\n\n{next_day_agenda}", 'bookly' ),
            'to_staff' => 1,
            'settings' => $settings,
        );
        $this->notifications[] = array(
            'gateway' => 'email',
            'type' => 'new_booking_combined',
            'name' => __( 'New booking combined notification', 'bookly' ),
            'subject' => __( 'Your appointment information', 'bookly' ),
            'message' => __( "Dear {client_name}.\n\nThis is a confirmation that you have booked the following items:\n\n{cart_info}\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'active' => 0,
            'to_customer' => 1,
            'settings' => $default_settings,
        );
        $this->notifications[] = array(
            'gateway' => 'sms',
            'type' => 'customer_new_wp_user',
            'name' => __( 'Notification to customer about their WordPress user login details', 'bookly' ),
            'message' => __( "Hello.\nAn account was created for you at {site_address}\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ),
            'active' => 1,
            'to_customer' => 1,
            'settings' => $default_settings,
        );
        $this->notifications[] = array(
            'gateway' => 'sms',
            'type' => 'staff_new_wp_user',
            'name' => __( 'New staff member\'s WordPress user login details', 'bookly' ),
            'message' => __( "Hello.\nAn account was created for you at {site_address}\nYour user details:\nuser: {new_username}\npassword: {new_password}\n\nThanks.", 'bookly' ),
            'active' => 1,
            'to_staff' => 1,
            'settings' => $default_settings,
        );
        $settings = $default_settings;
        $settings['existing_event_with_date']['at_hour'] = 9;
        $this->notifications[] = array(
            'gateway' => 'sms',
            'type' => 'customer_birthday',
            'name' => __( 'Customer birthday greeting (requires cron setup)', 'bookly' ),
            'message' => __( "Dear {client_name},\nHappy birthday!\nWe wish you all the best.\nMay you and your family be happy and healthy.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'to_customer' => 1,
            'settings' => $settings,
        );
        $this->notifications[] = array(
            'gateway' => 'sms',
            'type' => 'new_booking_combined',
            'name' => __( 'New booking combined notification', 'bookly' ),
            'message' => __( "Dear {client_name}.\nThis is a confirmation that you have booked the following items:\n{cart_info}\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'to_customer' => 1,
            'active' => 0,
            'settings' => $default_settings,
        );
        $orders = class_exists( 'Bookly\Lib\Utils\DateTime', false ) ? BooklyLib\Utils\DateTime::getDatePartsOrder() : array( 'month', 'day', 'year' );
        $birthday_labels = array_combine( $orders, array( __( 'Birthday', 'bookly' ), '', '' ) );

        $this->options = array(
            // Appearance
            'bookly_app_show_address' => '0',
            'bookly_app_show_birthday' => '0',
            'bookly_app_show_facebook_login_button' => '0',
            'bookly_api_server_error_time' => '0',
            // Google Calendar.
            'bookly_gc_client_id' => '',
            'bookly_gc_client_secret' => '',
            'bookly_gc_event_title' => '{service_name}',
            'bookly_gc_sync_mode' => '1.5-way',
            'bookly_gc_limit_events' => '50',
            'bookly_gc_event_description' => '{#each participants as participant}' . __( 'Name', 'bookly' ) . ': {participant.client_name}' . PHP_EOL . __( 'Email', 'bookly' ) . ': {participant.client_email}' . PHP_EOL . __( 'Phone', 'bookly' ) . ': {participant.client_phone}' . PHP_EOL . '{/each}',
            // Purchase reminder.
            'bookly_pr_show_time' => time() + 7776000,
            'bookly_pr_data' => array(
                'SW1wb3J0YW50ITxici8+SXQgbG9va3MgbGlrZSB5b3UgYXJlIHVzaW5nIGFuIGlsbGVnYWwgY29weSBvZiBCb29rbHkgUHJvLiBBbmQgaXQgbWF5IGNvbnRhaW4gYSBtYWxpY2lvdXMgY29kZSwgYSB0cm9qYW4gb3IgYSBiYWNrZG9vci4=',
                'Q29uc2lkZXIgc3dpdGNoaW5nIHRvIHRoZSBsZWdhbCBjb3B5IG9mIEJvb2tseSBQcm8gdGhhdCBpbmNsdWRlcyBhbGwgZmVhdHVyZXMsIGxpZmV0aW1lIGZyZWUgdXBkYXRlcywgYW5kIDI0Lzcgc3VwcG9ydC4=',
                'WW91IGNhbiBidXkgYSBsZWdhbCBjb3B5IG9uIG91ciB3ZWJzaXRlIDxhIGhyZWY9Imh0dHBzOi8vd3d3LmJvb2tpbmctd3AtcGx1Z2luLmNvbSIgdGFyZ2V0PSJfYmxhbmsiPnd3dy5ib29raW5nLXdwLXBsdWdpbi5jb208L2E+LCBvciBjb250YWN0IHVzIGF0IDxhIGhyZWY9Im1haWx0bzpzdXBwb3J0QGJvb2tseS5pbmZvIj5zdXBwb3J0QGJvb2tseS5pbmZvPC9hPiBmb3IgYW55IGFzc2lzdGFuY2Uu',
            ),
            // Grace.
            'bookly_grace_notifications' => array( 'bookly' => '0', 'add-ons' => 0, 'sent' => '0' ),
            // Appearance.
            'bookly_save_email_logs' => '1',
            'bookly_app_show_time_zone_switcher' => '0',
            'bookly_app_show_tips' => '0',
            'bookly_l10n_label_tips' => __( 'Tips', 'bookly' ),
            'bookly_l10n_button_apply_tips' => __( 'Apply', 'bookly' ),
            'bookly_l10n_button_applied_tips' => __( 'Applied', 'bookly' ),
            'bookly_l10n_tips_error' => __( 'Incorrect value', 'bookly' ),
            'bookly_l10n_label_pay_paypal' => __( 'I will pay now with PayPal', 'bookly' ),
            // Settings -> URL.
            'bookly_url_cancel_confirm_page_url' => home_url(),
            'bookly_url_final_step_url' => '',
            // Settings -> Appointments.
            'bookly_appointments_main_value' => 'provider',
            'bookly_appointments_displayed_time_slots' => 'all',
            'bookly_appointments_time_delimiter' => '0',
            // Settings -> Customers.
            'bookly_cst_address_show_fields' => array(
                'country' => array( 'show' => 1 ),
                'state' => array( 'show' => 1 ),
                'postcode' => array( 'show' => 1 ),
                'city' => array( 'show' => 1 ),
                'street' => array( 'show' => 1 ),
                'street_number' => array( 'show' => 1 ),
                'additional_address' => array( 'show' => 1 ),
            ),
            'bookly_cst_limit_statuses' => array( 'waitlisted' ),
            'bookly_cst_new_account_role' => 'subscriber',
            'bookly_l10n_cst_address_template' => '{#if country}{country}, {/if}{#if state}{state}, {/if}{#if postcode}{postcode}, {/if}{#if city}{city}, {/if}{#if street}{street} {/if}{#if street_number}{street_number} {/if}{#if additional_address}{additional_address}{/if}',
            // Settings -> Payments -> PayPal.
            'bookly_paypal_enabled' => '0',
            'bookly_paypal_sandbox' => '0',
            'bookly_paypal_api_password' => '',
            'bookly_paypal_api_signature' => '',
            'bookly_paypal_api_username' => '',
            'bookly_paypal_id' => '',
            'bookly_paypal_increase' => '0',
            'bookly_paypal_addition' => '0',
            'bookly_paypal_send_tax' => '0',
            'bookly_paypal_timeout' => '0',
            // Payment.
            'bookly_l10n_info_payment_step_several_apps' => __( 'Please tell us how you would like to pay: ', 'bookly' ),
            'bookly_l10n_info_payment_step_with_100percents_off_price' => __( 'You are not required to pay for the booked services, click Next to complete the booking process.', 'bookly' ),
            'bookly_l10n_info_payment_step_without_intersected_gateways' =>  __( 'No payment methods available for one or more staff. Please contact service provider.', 'bookly' ),
            // Address.
            'bookly_l10n_info_address' => __( 'Address', 'bookly' ),
            'bookly_l10n_label_country' => __( 'Country', 'bookly' ),
            'bookly_l10n_label_state' => __( 'State/Region', 'bookly' ),
            'bookly_l10n_label_postcode' => __( 'Postal Code', 'bookly' ),
            'bookly_l10n_label_city' => __( 'City', 'bookly' ),
            'bookly_l10n_label_street' => __( 'Street Address', 'bookly' ),
            'bookly_l10n_label_street_number' => __( 'Street Number', 'bookly' ),
            'bookly_l10n_label_additional_address' => __( 'Additional Address', 'bookly' ),
            'bookly_l10n_invalid_day' => __( 'Invalid day', 'bookly' ),
            'bookly_l10n_required_day' => __( 'Day is required', 'bookly' ),
            'bookly_l10n_required_month' => __( 'Month is required', 'bookly' ),
            'bookly_l10n_required_year' => __( 'Year is required', 'bookly' ),
            'bookly_l10n_required_country' => __( 'Country is required', 'bookly' ),
            'bookly_l10n_required_state' => __( 'State is required', 'bookly' ),
            'bookly_l10n_required_postcode' => __( 'Postcode is required', 'bookly' ),
            'bookly_l10n_required_city' => __( 'City is required', 'bookly' ),
            'bookly_l10n_required_street' => __( 'Street is required', 'bookly' ),
            'bookly_l10n_required_street_number' => __( 'Street number is required', 'bookly' ),
            'bookly_l10n_required_additional_address' => __( 'Additional address is required', 'bookly' ),
            // Card.
            'bookly_l10n_label_ccard_code' => __( 'Card Security Code', 'bookly' ),
            'bookly_l10n_label_ccard_expire' => __( 'Expiration Date', 'bookly' ),
            'bookly_l10n_label_ccard_number' => __( 'Credit Card Number', 'bookly' ),
            // Birthday.
            'bookly_l10n_label_birthday_day' => $birthday_labels['day'],
            'bookly_l10n_label_birthday_month' => $birthday_labels['month'],
            'bookly_l10n_label_birthday_year' => $birthday_labels['year'],
            // Settings -> Facebook.
            'bookly_fb_app_id' => '',
            // Settings -> Permissions.
            'bookly_staff_new_account_role' => 'subscriber',
            // WooCommerce.
            'bookly_wc_enabled' => '0',
            'bookly_wc_product' => '',
            'bookly_l10n_wc_cart_info_name' => __( 'Appointment', 'bookly' ),
            'bookly_l10n_wc_cart_info_value' => __( 'Date', 'bookly' ) . ": {appointment_date}\n" . __( 'Time', 'bookly' ) . ": {appointment_time}\n" . __( 'Service', 'bookly' ) . ': {service_name}',
            // Zoom.
            'bookly_zoom_authentication' => 'jwt',
            'bookly_zoom_jwt_api_key' => '',
            'bookly_zoom_jwt_api_secret' => '',
            'bookly_zoom_oauth_client_id' => '',
            'bookly_zoom_oauth_client_secret' => '',
            'bookly_zoom_oauth_token' => '',
            // Calendar
            'bookly_cal_frontend_enabled' => '0',
        );
    }

    /**
     * @inheritDoc
     */
    public function loadData()
    {
        parent::loadData();

        // Insert notifications.
        foreach ( $this->notifications as $data ) {
            $notification = new BooklyLib\Entities\Notification();
            $notification->setFields( $data )->save();
        }

        // Make 'Collect stats' notice appear again.
        if ( get_option( 'bookly_gen_collect_stats' ) == '0' ) {
            foreach ( get_users( 'role=administrator' ) as $user ) {
                delete_user_meta( $user->ID, 'bookly_dismiss_collect_stats_notice' );
            }
        }

        // Create WP role for bookly supervisor
        $capabilities = array();
        if ( $subscriber = get_role( 'subscriber' ) ) {
            $capabilities = $subscriber->capabilities;
        }
        $capabilities['view_admin_dashboard'] = true;
        $capabilities['manage_bookly_appointments'] = true;
        add_role( 'bookly_supervisor', 'Bookly Supervisor', $capabilities );
        // Create WP role for bookly administrator
        $capabilities = array();
        if ( $subscriber = get_role( 'subscriber' ) ) {
            $capabilities = $subscriber->capabilities;
        }
        $capabilities['view_admin_dashboard'] = true;
        $capabilities['manage_bookly'] = true;
        add_role( 'bookly_administrator', 'Bookly Administrator', $capabilities );
    }

    /**
     * @inheritDoc
     */
    public function removeData()
    {
        global $wpdb;

        parent::removeData();

        // Can't remove notifications.

        // Remove user meta.
        $meta_names = array( 'bookly_grace_hide_admin_notice_time', 'bookly_show_purchase_reminder' );
        $wpdb->query( $wpdb->prepare( sprintf( 'DELETE FROM `' . $wpdb->usermeta . '` WHERE meta_key IN (%s)',
            implode( ', ', array_fill( 0, count( $meta_names ), '%s' ) ) ), $meta_names ) );

        // Remove supervisor role
        remove_role( 'bookly_supervisor' );
        remove_role( 'bookly_administrator' );
    }

    /**
     * @inheritDoc
     */
    public function createTables()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $charset_collate = $wpdb->has_cap( 'collation' )
            ? $wpdb->get_charset_collate()
            : 'DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci';

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\EmailLog::getTableName() . '` (
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

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffCategory::getTableName() . '` (
                `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `name`     VARCHAR(255) NOT NULL,
                `position` INT NOT NULL DEFAULT 9999
             ) ENGINE = INNODB
             ' . $charset_collate
        );

        $wpdb->query(
            'ALTER TABLE `' . BooklyLib\Entities\Staff::getTableName() . '`
             ADD CONSTRAINT
                FOREIGN KEY (category_id)
                REFERENCES ' . Entities\StaffCategory::getTableName() . '(id)
                ON DELETE SET NULL
                ON UPDATE CASCADE'
        );

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\StaffPreferenceOrder::getTableName() . '` (
                `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `service_id`  INT UNSIGNED NOT NULL,
                `staff_id`    INT UNSIGNED NOT NULL,
                `position`    INT NOT NULL DEFAULT 9999,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . BooklyLib\Entities\Service::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . BooklyLib\Entities\Staff::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            ' . $charset_collate
        );
    }

}