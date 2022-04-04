<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="bookly-collapse">
    <a class="h5" href="#<?php echo esc_attr( $id ) ?>" data-toggle="collapse" role="button" aria-expanded="<?php echo esc_attr( (string) $opened ) ?>"><?php echo esc_html( $title ) ?></a>
    <div id="<?php echo esc_attr( $id ) ?>" class="collapse<?php if ( $opened ) : ?> show<?php endif ?>">