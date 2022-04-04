<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib\Utils\DateTime;
?>
<div class="form-group bookly-js-service bookly-js-service-simple">
    <label for="padding_left_<?php echo $service['id'] ?>">
        <?php esc_html_e( 'Padding time (before and after)', 'bookly' ) ?>
    </label>
    <div class="form-row">
        <div class="col-6">
            <select id="padding_left_<?php echo $service['id'] ?>" class="form-control custom-select" name="padding_left">
                <option value="0"><?php esc_html_e( 'OFF', 'bookly' ) ?></option>
                <?php for ( $j = $time_interval; $j <= 1440; $j += $time_interval ) : ?><?php if ( $service['padding_left'] > 0 && $service['padding_left'] / 60 > $j - $time_interval && $service['padding_left'] / 60 < $j ) : ?><option value="<?php echo esc_attr( $service['padding_left'] ) ?>" selected><?php echo DateTime::secondsToInterval( $service['padding_left'] ) ?></option><?php endif ?><option value="<?php echo $j * 60 ?>" <?php selected( $service['padding_left'], $j * 60 ) ?>><?php echo DateTime::secondsToInterval( $j * 60 ) ?></option><?php endfor ?>
            </select>
        </div>
        <div class="col-6">
            <select id="padding_right_<?php echo $service['id'] ?>" class="form-control custom-select" name="padding_right">
                <option value="0"><?php esc_html_e( 'OFF', 'bookly' ) ?></option>
                <?php for ( $j = $time_interval; $j <= 1440; $j += $time_interval ) : ?><?php if ( $service['padding_right'] > 0 && $service['padding_right'] / 60 > $j - $time_interval && $service['padding_right'] / 60 < $j ) : ?><option value="<?php echo esc_attr( $service['padding_right'] ) ?>" selected><?php echo DateTime::secondsToInterval( $service['padding_right'] ) ?></option><?php endif ?><option value="<?php echo $j * 60 ?>" <?php selected( $service['padding_right'], $j * 60 ) ?>><?php echo DateTime::secondsToInterval( $j * 60 ) ?></option><?php endfor ?>
            </select>
        </div>
    </div>
    <small class="form-text text-muted"><?php esc_html_e( 'Set padding time before and/or after an appointment. For example, if you require 15 minutes to prepare for the next appointment then you should set "padding before" to 15 min. If there is an appointment from 8:00 to 9:00 then the next available time slot will be 9:15 rather than 9:00.', 'bookly' ) ?></small>
</div>