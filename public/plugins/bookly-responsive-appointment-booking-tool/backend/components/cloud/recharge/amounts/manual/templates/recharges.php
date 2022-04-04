<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var array $recharges
 * @var array|null $best_offer
 */
?>
<?php if ( $best_offer ) : ?>
<?php include '_best_offer.php' ?>
<div class="form-row mb-2">
    <div class="col"><hr/></div>
    <div class="col-auto"><h5 class="text-muted"><?php esc_html_e( 'Other options', 'bookly' ) ?></h5></div>
    <div class="col"><hr/></div>
</div>
<?php endif ?>
<div class="form-row">
    <?php foreach ( $recharges as $recharge ) : ?>
    <?php if ( $best_offer && $recharge['id'] == $best_offer['id'] ) : continue; endif ?>
    <div class="col-4">
        <?php self::renderTemplate( '_button', compact( 'recharge' ) ) ?>
    </div>
    <?php endforeach ?>
</div>