<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Lib as BooklyLib;
use BooklyPro\Lib\Config;
?>
<div class="fb-login-button" id="bookly-facebook-login-button" data-max-rows="1" data-size="large" data-button-type="login_with" data-show-faces="false" data-auto-logout-link="false" data-use-continue-as="false" data-scope="public_profile,email"<?php if ( ! Config::showFacebookLoginButton() ) : ?> style="display:none"<?php endif ?>></div>
<?php if ( Config::getFacebookAppId() != '' ) : ?>
    <div id="fb-root"></div>
    <script>
        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) return;
            js = d.createElement(s); js.id = id;
            js.src = <?php echo json_encode( sprintf( 'https://connect.facebook.net/%s/sdk.js#xfbml=1&version=v2.12&appId=%s', BooklyLib\Config::getLocale(), Config::getFacebookAppId() ) ) ?>;
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>
<?php endif ?>