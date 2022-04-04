<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
?>
<div>
    <p><?php esc_html_e( 'Thank you for choosing Bookly Pro as your booking solution.', 'bookly' ) ?></p>
    <p><?php esc_html_e( 'Please verify your license by providing a valid purchase code. Upon providing the purchase code you will get access to software updates, including feature improvements and important security fixes.', 'bookly' ) ?></p>
    <p><?php echo strtr( esc_html__( 'If you do not provide a valid purchase code within {days}, access to your bookings will be disabled.', 'bookly' ), $days_text ) ?></p>
</div>
<div class="btn-group-vertical align-left" role="group">
    <button type="button" class="btn btn-link text-success text-left" data-trigger="request_code"><span class="text-success"><i class="fas fa-fw fa-check-circle mr-1"></i><?php esc_html_e( 'I have already made the purchase', 'bookly' ) ?></span></button>
    <a type="button" class="btn btn-link text-left" href="<?php echo BooklyLib\Utils\Common::prepareUrlReferrers( 'https://codecanyon.net/user/ladela/portfolio?ref=ladela', 'grace' ) ?>" target="_blank"><i class="fas fa-fw fa-shopping-cart mr-1"></i><?php esc_html_e( 'I want to make a purchase now', 'bookly' ) ?></a>
    <button type="button" class="btn btn-link text-warning text-left" data-trigger="temporary-hide"><i class="fas fa-fw fa-times-circle mr-1"></i><?php esc_html_e( 'I will provide license info later', 'bookly' ) ?></button>
</div>