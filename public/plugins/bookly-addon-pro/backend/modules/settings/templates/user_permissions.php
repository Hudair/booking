<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Inputs as ControlsInputs;
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Modules\Settings\Proxy;

/** @var array $roles */
$admin = current_user_can( 'manage_options' );
?>
<div class="tab-pane" id="bookly_settings_user_permissions">
    <form method="post" action="<?php echo esc_url( add_query_arg( 'tab', 'user_permissions' ) ) ?>">
        <div class="card-body">
            <div class="form-group">
                <label><?php esc_html_e( 'Select who can manage Bookly appointments', 'bookly' ) ?></label>
                <?php foreach ( $roles as $role => $data ) : ?>
                    <?php ControlsInputs::renderCheckBox( $data['name'], $role, array_key_exists( 'manage_options', $data['capabilities'] ) || array_key_exists( 'manage_bookly', $data['capabilities'] ) || array_key_exists( 'manage_bookly_appointments', $data['capabilities'] ), array_key_exists( 'manage_options', $data['capabilities'] ) || array_key_exists( 'manage_bookly', $data['capabilities'] ) ? array( 'disabled' => 'disabled' ) : array( 'name' => 'manage_bookly_appointments[]' ) ) ?>
                <?php endforeach ?>
            </div>
            <div class="form-group">
                <label><?php esc_html_e( 'Select who can administrate Bookly', 'bookly' ) ?></label>
                <?php foreach ( $roles as $role => $data ) : ?>
                    <?php ControlsInputs::renderCheckBox( $data['name'], $role, array_key_exists( 'manage_options', $data['capabilities'] ) || array_key_exists( 'manage_bookly', $data['capabilities'] ), array_key_exists( 'manage_options', $data['capabilities'] ) || ! $admin ? array( 'disabled' => 'disabled' ) : array( 'name' => 'manage_bookly[]' ) ) ?>
                <?php endforeach ?>
            </div>
            <?php Proxy\Pro::renderNewStaffAccountRole() ?>
        </div>
        <div class="card-footer bg-transparent d-flex justify-content-end">
            <?php ControlsInputs::renderCsrf() ?>
            <?php Buttons::renderSubmit() ?>
            <?php Buttons::renderReset( null, 'ml-2' ) ?>
        </div>
    </form>
</div>