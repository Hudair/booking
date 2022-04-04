<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;

/**
 * @var array $recharges
 */
?>
<div class="form-row flex-nowrap bookly-js-auto-recharge-selector">
    <div class="col">
        <div class="dropdown">
            <button class="bookly-js-auto-recharge-dropdown btn btn-default dropdown-toggle d-flex align-items-center w-100" type="button" data-toggle="dropdown">
                <span class="flex-grow-1 text-left"><?php esc_html_e( 'Amount', 'bookly' ) ?>:</span>
                <span>
                    <span class="bookly-js-best-offer-auto badge badge-warning" style="display: none"><small><strong><?php esc_html_e( 'best offer', 'bookly' ) ?></strong></small></span>
                    <span class="bookly-js-users-choice-auto badge badge-danger" style="display: none"><small><strong><?php esc_html_e( 'users choice', 'bookly' ) ?></strong></small></span>
                    $<span class="bookly-js-auto-amount"></span>
                    <span class="text-success bookly-js-auto-bonus"> + <span></span></span>
                </span>
            </button>
            <div class="dropdown-menu dropdown-menu-right text-right w-100 shadow">
                <h6 class="my-2 mx-4 text-muted small"><?php esc_html_e( 'Select amount of Auto-Recharge', 'bookly' ) ?></h6>
                <?php foreach ( $recharges as $recharge ) : ?>
                    <div class="dropdown-divider m-0"></div>
                    <button type="button" class="dropdown-item text-wrap" data-recharge-data=<?php echo json_encode( $recharge ) ?>>
                        <?php if ( in_array( 'best_offer', $recharge['tags'] ) ) : ?>
                            <span class="bookly-js-best-offer badge badge-warning"><small><strong><?php esc_html_e( 'best offer', 'bookly' ) ?></strong></small></span>
                        <?php endif ?>
                        <?php if ( in_array( 'users_choice', $recharge['tags'] ) ) : ?>
                            <span class="bookly-js-users-choice badge badge-danger"><small><strong><?php esc_html_e( 'users choice', 'bookly' ) ?></strong></small></span>
                        <?php endif ?>
                        $<?php echo esc_html( $recharge['amount'] ) ?>
                        <?php if ( $recharge['bonus'] ) : ?>
                            <span class="text-success">+ <?php echo esc_html( $recharge['bonus'] ) ?></span>
                        <?php endif ?>
                    </button>
                <?php endforeach ?>
            </div>
        </div>
    </div>
    <div class="col-auto">
        <?php Buttons::render( null, 'bookly-js-auto-recharge-enable btn-success', __( 'Enable', 'bookly' ) . '…' ) ?>
        <span class="bookly-js-auto-recharge-enabled mr-2">
            <i class="fas fa-fw fa-check-circle text-success"></i> <?php esc_html_e( 'Enabled', 'bookly' ) ?>
        </span>
        <?php Buttons::render( null, 'bookly-js-auto-confirm-disable btn-danger', __( 'Disable', 'bookly' ) . '…' ) ?>
    </div>
</div>