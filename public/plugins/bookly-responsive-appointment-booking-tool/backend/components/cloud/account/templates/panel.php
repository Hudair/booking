<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Components\Cloud\Recharge;
/**
 * @var Bookly\Lib\Cloud\API $cloud
 */
?>
<?php Recharge\Amounts\Manual\Button::renderBalance() ?>
<div class="btn-group">
    <a id="bookly-open-account-settings" class="btn <?php echo esc_attr( $cloud->account->getEmailConfirmed() ? 'btn-primary' : 'btn-danger' ) ?> text-truncate" href="<?php echo Common::escAdminUrl( Bookly\Backend\Modules\CloudSettings\Page::pageSlug() ) ?>">
        <i class="fas <?php echo esc_attr( $cloud->account->getEmailConfirmed() ? 'fa-user' : 'fa-user-slash' ) ?>"></i><span class="d-none d-sm-inline ml-2"><?php echo esc_html( $cloud->account->getUserName() ) ?></span>
    </a>
    <?php if ( ! $cloud->account->getEmailConfirmed() ) : ?>
        <button id="bookly-open-email-confirm" type="button" class="btn btn-success text-nowrap ladda-button" data-spinner-color="#666666" data-style="zoom-in" data-spinner-size="40">
            <span class="ladda-label"><i class="fas fa-exclamation-circle"></i><span class="d-none d-md-inline-block ml-2"><?php esc_html_e( 'Confirm email', 'bookly' ) ?>…</span></span>
        </button>
    <?php endif ?>
    <button id="bookly-logout" type="button" class="btn btn-white border text-nowrap rounded-right ladda-button" data-spinner-color="#666666" data-style="zoom-in" data-spinner-size="40">
        <span class="ladda-label"><i class="fas fa-sign-out-alt"></i><span class="d-none d-md-inline-block ml-2"><?php esc_html_e( 'Log out', 'bookly' ) ?></span></span>
    </button>
</div>
<?php Recharge\Dialog::render() ?>

<?php if ( ! $cloud->account->getCountry() ): ?>
    <?php include '_setup_country.php' ?>
<?php endif ?>
<?php if ( ! $cloud->account->getEmailConfirmed() ): ?>
    <?php include "_confirm_email.php" ?>
<?php endif ?>
