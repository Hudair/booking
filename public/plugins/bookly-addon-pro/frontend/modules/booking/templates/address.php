<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyPro\Frontend\Components;
use Bookly\Frontend\Modules\Booking\Proxy;
use Bookly\Lib as BooklyLib;

/** @var BooklyLib\UserBookingData $userData */
?>
<div id="bookly-js-address">

    <div class="bookly-box bookly-bold">
        <?php echo BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_info_address' ) ?>
    </div>

    <?php Proxy\GoogleMapsAddress::renderAutocompleter() ?>
    <?php Components\Fields\Address::render( $userData ) ?>

</div>