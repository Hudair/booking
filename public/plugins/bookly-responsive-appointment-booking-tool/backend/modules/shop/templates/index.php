<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components;
use Bookly\Lib\Utils\Common;
use Bookly\Backend\Components\Support;
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Addons', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-auto">
                    <div class="form-group">
                        <select class="form-control bookly-js-select" id="bookly-shop-sort" data-placeholder="<?php echo esc_attr( __( 'Sort by', 'bookly' ) ) ?>">
                            <option></option>
                            <option value="sales"<?php selected( ! $has_new_items ) ?>><?php esc_html_e( 'Best Sellers', 'bookly' ) ?></option>
                            <option value="rating"><?php esc_html_e( 'Best Rated', 'bookly' ) ?></option>
                            <option value="date"<?php selected( $has_new_items ) ?>><?php esc_html_e( 'Newest Items', 'bookly' ) ?></option>
                            <option value="price_low"><?php esc_html_e( 'Price: low to high', 'bookly' ) ?></option>
                            <option value="price_high"><?php esc_html_e( 'Price: high to low', 'bookly' ) ?></option>
                        </select>
                    </div>
                </div>
            </div>
            <div id="bookly-shop" class="hidden"></div>
            <div id="bookly-shop-loading" class="bookly-loading"></div>
        </div>
    </div>
</div>
<div id="bookly-shop-template" class="hidden">
    <div class="{{plugin_class}} card p-3 mb-3">
        <div class="row">
            <div class="col-lg-10 col-md-9 col-xs-7">
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex">
                            <div class="mr-4 mb-4">
                                <a href="{{url}}" target="_blank">{{icon}}</a>
                            </div>
                            <div class="flex-fill">
                                <div class="h5"><a href="{{url}}" target="_blank">{{title}}</a> <span class="badge badge-danger">{{new}}</span></div>
                                <a class="" href="<?php echo Common::prepareUrlReferrers( 'https://codecanyon.net/user/ladela/portfolio?ref=ladela', 'shop' ) ?>" target="_blank">Ladela</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8 col-md-6">{{description}}</div>
                </div>
            </div>
            <div class="col-lg-2 col-md-3 col-xs-5">
                <div class="text-center">
                    <div class="h4 mb-0">{{price}}</div>
                    <div>{{sales}}</div>
                    <div class="text-warning {{rating_class}}">{{rating}}</div>
                    <div class="mb-2">{{reviews}}</div>
                    <div class="{{demo_url_class}}">
                        <a href="{{demo_url}}" class="btn btn-primary" target="_blank"><b><?php esc_html_e( 'Demo', 'bookly' ) ?></b></a>
                    </div>
                    <a href="{{url}}" class="btn {{url_class}}" target="_blank">{{url_text}}</a><br/>
                </div>
            </div>
        </div>
    </div>
</div>
