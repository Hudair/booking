<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
?>
<div>
    <p><?php printf( __( 'Cannot find your purchase code? See this <a href="%s" target="_blank">page</a>.', 'bookly' ), 'https://help.market.envato.com/hc/en-us/articles/202822600-Where-can-I-find-my-Purchase-Code' ) ?></p>
    <?php
    $addons = apply_filters( 'bookly_plugins', array() );
    unset ( $addons[ BooklyLib\Plugin::getSlug() ] );
    /** @var \Bookly\Lib\Base\Plugin $plugin_class */
    foreach ( $addons as $plugin_class ) :
        if ( $plugin_class::getPurchaseCode() == '' && ! $plugin_class::embedded() ) :
            printf(
                '<label for="%2$s">%1$s:</label>
                    <div class="input-group mb-3">
                        <input id="%2$s" class="form-control" type="text" value="%3$s" autocomplete="off" />
                        <div class="input-group-append"><button type="button" class="btn btn-info ladda-button" data-spinner-size="40" data-style="zoom-in"><span class="ladda-label">%4$s</span></button></div>
                    </div>',
                $plugin_class::getTitle() . ' ' . __( 'Purchase Code', 'bookly' ),
                $plugin_class::getRootNamespace(),
                $plugin_class::getPurchaseCode(),
                esc_html__( 'Apply', 'bookly' )
            );
        endif;
    endforeach ?>
</div>
<hr/>
<div class="ml-auto">
    <a href="" class="btn btn-default"><?php esc_html_e( 'Back', 'bookly' ) ?></a>
</div>