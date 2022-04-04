<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Config;
use Bookly\Lib\Entities\Service;
use Bookly\Backend\Components\Dialogs\Service\Edit\Proxy;
/**
 * @var array $service
 */
?>
<div class="form-group">
    <label><?php esc_html_e( 'Visibility', 'bookly' ) ?></label>
    <div class="custom-control custom-radio">
        <input type="radio" id="bookly-visibility-public" name="visibility" value="public"<?php checked( $service['visibility'], Service::VISIBILITY_PUBLIC ) ?> class="custom-control-input" />
        <label for="bookly-visibility-public" class="custom-control-label"><?php esc_html_e( 'Public', 'bookly' ) ?></label>
    </div>
    <div class="custom-control custom-radio">
        <input type="radio" id="bookly-visibility-private" name="visibility" value="private"<?php checked( $service['visibility'] == Service::VISIBILITY_PRIVATE || ( $service['visibility'] == Service::VISIBILITY_GROUP_BASED && ! Config::customerGroupsActive() ) ) ?>  class="custom-control-input" />
        <label for="bookly-visibility-private" class="custom-control-label"><?php esc_html_e( 'Private', 'bookly' ) ?></label>
    </div>
    <?php Proxy\CustomerGroups::renderVisibilityOption( $service ) ?>
    <small class="form-text text-muted"><?php esc_html_e( 'To make service invisible to your customers set the visibility to "Private".', 'bookly' ) ?></small>
</div>