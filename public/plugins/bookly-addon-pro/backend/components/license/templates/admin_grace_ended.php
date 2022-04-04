<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
?>
<div>
    <p><?php esc_html_e( 'Access to your bookings has been disabled.', 'bookly' ) ?></p>
    <p><?php esc_html_e( 'To enable access to your bookings, please verify your license by providing a valid purchase code.', 'bookly' ) ?></p>
</div>
<div class="btn-group-vertical text-left" role="group">
    <button type="button" class="btn btn-link text-left text-success" data-trigger="request_code"><i class="fas fa-fw fa-check-circle mr-1"></i><?php esc_html_e( 'I have already made the purchase', 'bookly' ) ?></button>
    <a type="button" class="btn btn-link text-left" href="<?php echo BooklyLib\Utils\Common::prepareUrlReferrers( 'https://codecanyon.net/user/ladela/portfolio?ref=ladela', 'grace_ended' ) ?>" target="_blank"><i class="fas fa-fw fa-shopping-cart mr-1"></i><?php esc_html_e( 'I want to make a purchase now', 'bookly' ) ?></a>
    <button type="button" class="btn ladda-button btn-link bookly-js-deactivate-pro text-left" data-spinner-size="24" data-spinner-color="#333" data-style="zoom-in" data-redirect="1"><i class="fas fa-fw fa-times-circle mr-1"></i><?php esc_html_e( 'Deactivate Bookly Pro', 'bookly' ) ?></button>
</div>