<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Modules\Settings;
/**
 * @var Bookly\Lib\Entities\Staff $staff
 */
?>
<div class="form-group">
    <label for="zoom_authentication">Zoom <?php esc_html_e( 'integration', 'bookly' ) ?></label>
    <?php if ( $zoom['credentials_required'] ) : ?>
    <div>
        <?php printf( __( 'Please configure Zoom <a href="%s">settings</a> first', 'bookly' ), Common::escAdminUrl( Settings\Page::pageSlug(), array( 'tab' => 'online_meetings' ) ) ) ?>
    </div>
    <?php else: ?>
        <select class="form-control custom-select" name="zoom_authentication" id="zoom_authentication">
            <?php foreach ( $zoom['options'] as $option ) : ?>
                <option value="<?php echo $option['value'] ?>" <?php selected( $option['selected'] ) ?>><?php echo esc_html( $option['title'] ) ?></option>
            <?php endforeach ?>
        </select>
        <small class="form-text text-muted"><?php esc_html_e( 'This setting allows to set up personal Zoom account for staff member', 'bookly' ) ?></small>
    <?php endif ?>
</div>
<div class="form-group border-left ml-4 pl-3 bookly-js-zoom-settings bookly-js-zoom-jwt" style="display: none">
    <div class="form-group">
        <label for="bookly-zoom_jwt_api_key"><?php esc_html_e( 'API Key', 'bookly' ) ?></label>
        <input type="text" class="form-control" id="bookly-zoom_jwt_api_key" name="zoom_jwt_api_key" value="<?php echo esc_attr( $staff->getZoomJwtApiKey() ) ?>"/>
    </div>
    <div class="form-group">
        <label for="bookly-zoom_jwt_api_secret"><?php esc_html_e( 'API Secret', 'bookly' ) ?></label>
        <input type="text" class="form-control" id="bookly-zoom_jwt_api_secret" name="zoom_jwt_api_secret" value="<?php echo esc_attr( $staff->getZoomJwtApiSecret() ) ?>"/>
    </div>
</div>
<div class="form-group border-left ml-4 pl-3 bookly-js-zoom-settings bookly-js-zoom-oauth" style="display: none">
    <div class="bookly-js-zoom-connected" <?php if ( !$staff->getZoomOAuthToken() ) : ?> style="display: none;"<?php endif ?>>
        <?php esc_html_e( 'Connected', 'bookly' ) ?> (
        <span class="custom-control custom-checkbox d-inline-block">
            <input class="custom-control-input" id="bookly-zoom_oauth_disconnect" type="checkbox" name="zoom_oauth_disconnect" value="1">
            <label class="custom-control-label" for="bookly-zoom_oauth_disconnect"><?php esc_html_e( 'disconnect', 'bookly' ) ?></label>
        </span>
        )
    </div>
    <div class="bookly-js-zoom-disconnected" <?php if ( $staff->getZoomOAuthToken() ) : ?> style="display: none;"<?php endif ?>>
        <a href="#" class="bookly-js-zoom_oauth_connect"><?php esc_html_e( 'Connect', 'bookly' ) ?></a>
    </div>
</div>