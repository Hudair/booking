<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var array $best_offer
 */
?>
<div class="form-row">
    <div class="card bg-light" style="position: absolute; margin-top:25px; width: 96%; height: 174px; margin-left: 5px"></div>
    <div class="card mx-auto text-white my-3" style="background-color: #28a745; cursor: pointer">
        <div class="card-body text-center pb-2 pt-0" data-recharge-manual=<?php echo json_encode( $best_offer ) ?>>
            <table>
                <tr>
                    <td rowspan="2" style="vertical-align: bottom;line-height: 6.5rem;font-size: 2rem">$</td>
                    <td rowspan="2"><span style="font-size: 6rem"><?php echo esc_html( $best_offer['amount'] ) ?></span></td>
                    <?php if ( $best_offer['bonus'] ) : ?>
                        <td rowspan="2" style="vertical-align: top;font-size: 2rem; line-height: 6rem">+<b class="mr-1 text-warning"><?php echo esc_html( $best_offer['bonus'] ) ?></b></td>
                    <?php endif ?>
                    <td style="padding-top: 22px" class="text-right">
                        <b class="bg-warning px-2 py-1 text-dark text-uppercase" style="font-size: 1rem"><?php esc_html_e( 'Best offer', 'bookly' ) ?></b>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;" class="text-right">
                        <?php if ( in_array( 'users_choice', $best_offer['tags'] ) ) : ?>
                            <b style="font-size: 0.7rem;top: -3px;position: relative;" class="ml-auto bg-danger mt-n2 py-1 px-3 mb-2 text-nowrap text-uppercase"><?php esc_html_e( 'Users choice', 'bookly' ) ?></b><br>
                        <?php endif ?>
                        <div style="font-size: 2rem; background-color: #24983F" class="h4 d-bloc text-center text-light font-weight-bolder py-2 px-3 text-nowrap"><?php esc_html_e( 'Buy now', 'bookly' ) ?></div>
                    </td>
                </tr>
            </table>
            <div class="row" style="color: #a9dcb5">
                <div class="col"><i class="fab fa-2x fa-cc-paypal"></i></div>
                <div class="col"><i class="fab fa-2x fa-cc-mastercard"></i></div>
                <div class="col"><i class="fab fa-2x fa-cc-visa"></i></div>
                <div class="col"><i class="fab fa-2x fa-cc-amex"></i></div>
                <div class="col"><i class="fab fa-2x fa-cc-discover"></i></div>
            </div>
        </div>
    </div>
</div>