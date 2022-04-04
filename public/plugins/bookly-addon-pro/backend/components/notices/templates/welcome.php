<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Modules as Backend;
?>
<div class="m-3">
    <div class="h1"><?php esc_html_e( 'Welcome to Bookly Pro and thank you for purchasing our product!', 'bookly' ) ?></div>
    <h4><?php esc_html_e( 'Bookly will simplify the booking process for your customers. This plugin creates another touchpoint to convert your visitors into customers. With Bookly your clients can see your availability, pick the services you provide, book them online and much more.', 'bookly' ) ?></h4>
    <p><?php esc_html_e( 'To start using Bookly, you need to set up the services you provide and specify the staff members who will provide those services.', 'bookly' ) ?></p>
    <ol>
        <li><?php esc_html_e( 'Add staff members.', 'bookly' ) ?></li>
        <li><?php esc_html_e( 'Add services you provide and assign them to staff members.', 'bookly' ) ?></li>
        <li><?php esc_html_e( 'Go to Posts/Pages and click on the \'Add Bookly booking form\' button in the page editor to publish the booking form on your website.', 'bookly' ) ?></li>
    </ol>
    <p><?php esc_html_e( 'Bookly can boost your sales and scale together with your business. With Bookly add-ons you can get more features and functionality to customize your online scheduling system according to your business needs and simplify the process even more.', 'bookly' ) ?></p>
    <hr>
    <a class="btn btn-success" href="<?php echo Common::escAdminUrl( Backend\Staff\Ajax::pageSlug() ) ?>">
        <?php esc_html_e( 'Add Staff Members', 'bookly' ) ?>
    </a>
    <a class="btn btn-success" href="<?php echo Common::escAdminUrl( Backend\Services\Ajax::pageSlug() ) ?>">
        <?php esc_html_e( 'Add Services', 'bookly' ) ?>
    </a>
    <a class="btn btn-success" href="<?php echo Common::escAdminUrl( Backend\Shop\Page::pageSlug() ) ?>">
        <?php esc_html_e( 'Bookly Add-ons', 'bookly' ) ?>
    </a>
    <a class="btn btn-success" href="<?php echo Common::escAdminUrl( Backend\CloudProducts\Page::pageSlug() ) ?>">
        <?php esc_html_e( 'Bookly Cloud', 'bookly' ) ?>
    </a>
</div>