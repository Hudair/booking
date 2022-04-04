<?php
namespace Bookly\Backend\Modules\CloudSms;

use Bookly\Lib;

/**
 * Class Ajax
 * @package Bookly\Backend\Modules\CloudSms
 */
class Ajax extends Lib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array(
            'sendQueue'        => array( 'supervisor', 'staff' ),
            'clearAttachments' => array( 'supervisor', 'staff' ),
        );
    }

    /**
     * Get SMS list.
     */
    public static function getSmsList()
    {
        $dates = explode( ' - ', self::parameter( 'range' ), 2 );
        $start = Lib\Utils\DateTime::applyTimeZoneOffset( $dates[0], 0 );
        $end   = Lib\Utils\DateTime::applyTimeZoneOffset( date( 'Y-m-d', strtotime( '+1 day', strtotime( $dates[1] ) ) ), 0 );

        wp_send_json( Lib\Cloud\API::getInstance()->sms->getSmsList( $start, $end ) );
    }

    /**
     * Get price-list.
     */
    public static function getPriceList()
    {
        wp_send_json( Lib\Cloud\API::getInstance()->sms->getPriceList() );
    }

    /**
     * Send test SMS.
     */
    public static function sendTestSms()
    {
        $cloud = Lib\Cloud\API::getInstance();
        $response = array( 'success' => $cloud->sms->sendSms(
            self::parameter( 'phone_number' ),
            'Bookly test SMS.',
            'Bookly test SMS.',
            0
        ) );

        if ( $response['success'] ) {
            $response['message'] = __( 'SMS has been sent successfully.', 'bookly' );
        } else {
            $response['message'] = implode( ' ', $cloud->getErrors() );
        }

        wp_send_json( $response );
    }

    /**
     * Get Sender IDs list.
     */
    public static function getSenderIdsList()
    {
        wp_send_json( Lib\Cloud\API::getInstance()->sms->getSenderIdsList() );
    }

    /**
     * Request new Sender ID.
     */
    public static function requestSenderId()
    {
        $cloud  = Lib\Cloud\API::getInstance();
        $result = $cloud->sms->requestSenderId( self::parameter( 'sender_id' ) );
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $cloud->getErrors() ) ) );
        } else {
            wp_send_json_success( array( 'request_id' => $result['request_id'] ) );
        }
    }

    /**
     * Cancel request for Sender ID.
     */
    public static function cancelSenderId()
    {
        $cloud  = Lib\Cloud\API::getInstance();
        $result = $cloud->sms->cancelSenderId();
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $cloud->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Reset Sender ID to default (Bookly).
     */
    public static function resetSenderId()
    {
        $cloud  = Lib\Cloud\API::getInstance();
        $result = $cloud->sms->resetSenderId();
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => current( $cloud->getErrors() ) ) );
        } else {
            wp_send_json_success();
        }
    }

    /**
     * Delete notification.
     */
    public static function deleteNotification()
    {
        Lib\Entities\Notification::query()
            ->delete()
            ->where( 'id', self::parameter( 'id' ) )
            ->execute();

        wp_send_json_success();
    }

    /**
     * Get data for notification list.
     */
    public static function getNotifications()
    {
        $types = Lib\Entities\Notification::getTypes( self::parameter( 'gateway' ) );

        $notifications = Lib\Entities\Notification::query()
            ->select( 'id, name, active, type' )
            ->where( 'gateway', self::parameter( 'gateway' ) )
            ->whereIn( 'type', $types )
            ->fetchArray();

        foreach ( $notifications as &$notification ) {
            $notification['order'] = array_search( $notification['type'], $types );
            $notification['icon']  = Lib\Entities\Notification::getIcon( $notification['type'] );
            $notification['title'] = Lib\Entities\Notification::getTitle( $notification['type'] );
        }

        wp_send_json_success( $notifications );
    }

    /**
     * Activate/Suspend notification.
     */
    public static function setNotificationState()
    {
        Lib\Entities\Notification::query()
            ->update()
            ->set( 'active', (int) self::parameter( 'active' ) )
            ->where( 'id', self::parameter( 'id' ) )
            ->execute();

        wp_send_json_success();
    }

    /**
     * Remove notification(s).
     */
    public static function deleteNotifications()
    {
        $notifications = array_map( 'intval', self::parameter( 'notifications', array() ) );
        Lib\Entities\Notification::query()->delete()->whereIn( 'id', $notifications )->execute();
        wp_send_json_success();
    }

    public static function saveAdministratorPhone()
    {
        update_option( 'bookly_sms_administrator_phone', self::parameter( 'bookly_sms_administrator_phone' ) );
        wp_send_json_success();
    }

    /**
     * Send queue
     */
    public static function sendQueue()
    {
        $queue = self::parameter( 'queue', array() );
        $cloud = Lib\Cloud\API::getInstance();
        foreach ( $queue as $notification ) {
            if ( $notification['gateway'] == 'sms' ) {
                $cloud->sms->sendSms( $notification['address'], $notification['message'], $notification['impersonal'], $notification['type_id'] );
            } else {
                Lib\Proxy\Pro::logEmail( $notification['address'], $notification['subject'], $notification['message'], $notification['headers'], isset( $notification['attachments'] ) ? $notification['attachments'] : array(), $notification['type_id'] );
                wp_mail( $notification['address'], $notification['subject'], $notification['message'], $notification['headers'], isset( $notification['attachments'] ) ? $notification['attachments'] : array() );
            }
        }
        self::_deleteAttachmentFiles( self::parameter( 'attachments', array() ) );

        wp_send_json_success();
    }

    /**
     * Delete attachments files
     */
    public static function clearAttachments()
    {
        self::_deleteAttachmentFiles( self::parameter( 'attachments', array() ) );

        wp_send_json_success();
    }

    /**
     * Delete attachment files
     *
     * @param $attachments
     */
    private static function _deleteAttachmentFiles( $attachments )
    {
        $fs = Lib\Utils\Common::getFilesystem();

        foreach ( $attachments as $file ) {
            $fs->delete( $file, false, 'f' );
        }
    }
}