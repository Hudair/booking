<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var array $recharge
 */
?>
<div class="bookly-js-recharge card text-white bg-primary mb-3" style="cursor: pointer" data-recharge-manual=<?php echo json_encode( $recharge ) ?>>
    <div class="card-header border-bottom-0 text-center p-0" style="height: 6rem">
        <span style="vertical-align: bottom;line-height: 4.8rem;font-size: 2rem">$</span>
        <span style="font-size: 4rem"><?php echo esc_html( $recharge['amount'] ) ?></span>
        <?php if ( $recharge['bonus'] ) : ?>
            <b style="vertical-align: top;line-height: 4rem;font-size: 1.5rem">+<span class="text-warning"><?php echo esc_html( $recharge['bonus'] ) ?></span></b>
        <?php endif ?>
        <?php if ( in_array( 'users_choice', $recharge['tags'] ) ) : ?>
            <br><b style="font-size: 0.7rem;top: -1rem;position: relative;" class="mx-auto bg-danger py-1 px-md-3 px-1 text-truncate text-nowrap text-uppercase"><?php esc_html_e( 'users choice', 'bookly' ) ?></b>
        <?php endif ?>
    </div>
    <div class="card-body text-center">
        <span style="font-size: 2rem"><?php esc_html_e( 'Buy now', 'bookly' ) ?></span>
    </div>
</div>