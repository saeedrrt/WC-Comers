<?php
$lang = pll_current_language('slug');
$is_rtl = ($lang === 'ar');
?>
<!-- Banner Slider -->
<div class="tf-slideshow tf-btn-swiper-main <?= $is_rtl ? 'slider-rtl' : 'slider-ltr'; ?>">
  <div dir="<?= $is_rtl ? 'rtl' : 'ltr'; ?>" class="swiper tf-swiper33 sw-slide-show slider_effect_fade" 
       data-auto="true" 
       data-loop="true"
       data-effect="fade" 
       data-delay="3000"
       data-rtl="<?= $is_rtl ? 'true' : 'false'; ?>">
    <div class="swiper-wrapper">

<?php
$args = array(
    'post_type'      => 'product',
    'posts_per_page' => 3,
    'post__in'       => wc_get_product_ids_on_sale(),
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => array(
        'relation' => 'AND',

        // // 1) شرط sale date
        // array(
        //     'relation' => 'OR',
        //     array(
        //         'key'     => '_sale_price_dates_to',
        //         'value'   => '',
        //         'compare' => '='
        //     ),
        //     array(
        //         'key'     => '_sale_price_dates_to',
        //         'compare' => 'NOT EXISTS'
        //     ),
        // ),

        // 2) شرط وجود الـ banner (ID > 0)
        array(
            'key'     => 'product_banner',
            'value'   => 0,
            'type'    => 'NUMERIC',
            'compare' => '>',
        ),
    ),
);

$loop = new WP_Query( $args );

if ( $loop->have_posts() ) :
    while ( $loop->have_posts() ) : $loop->the_post();
        global $product;
        $regular_price = $product->get_regular_price();
        $sale_price    = $product->get_sale_price();
        $banner_prod   = get_field('product_banner', $product->get_id() );
        
        // النصوص المترجمة
        $opportunity_text = ($lang === 'ar') ? 'لا تفوت الفرصة' : 'Don\'t miss the opportunity';
        $shop_now_text = ($lang === 'ar') ? 'تسوق الآن' : 'Shop now';
?>
      <!-- item -->
      <div class="swiper-slide">
        <div class="slider-wrap style-2">
          <div class="sld_image">
            <img src="<?= esc_url($banner_prod); ?>"
              data-src="<?= esc_url($banner_prod); ?>" alt="<?= get_the_title(); ?>"
              class="lazyload scale-item w-30" style="min-height: 920px;">
          </div>
          <div class="sld_content">
            <div class="container">
              <div class="row">
                <div class="col-11">
                  <div class="content-sld_wrap <?= $is_rtl ? 'text-right' : 'text-left'; ?>">
                    <h4 class="sub-title_sld has-icon text-primary fade-item fade-item-1">
                      <span class="icon d-flex">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path
                            d="M9.001 11.949C9 6 11 2 15 0C15.5 6 21 8 21 15C21 17.3869 20.0518 19.6761 18.364 21.364C16.6761 23.0518 14.3869 24 12 24C9.61305 24 7.32387 23.0518 5.63604 21.364C3.94821 19.6761 3 17.3869 3 15C3 11.5 4 10 6 8C6 11 9.001 11.949 9.001 11.949Z"
                            fill="url(#paint0_linear_1733_16972)" />
                          <path
                            d="M9.001 11.949C9 6 11 2 15 0C15.5 6 21 8 21 15C21 17.3869 20.0518 19.6761 18.364 21.364C16.6761 23.0518 14.3869 24 12 24C9.61305 24 7.32387 23.0518 5.63604 21.364C3.94821 19.6761 3 17.3869 3 15C3 11.5 4 10 6 8C6 11 9.001 11.949 9.001 11.949Z"
                            fill="#EB423F" />
                          <path
                            d="M11.9998 23.75C7.97382 23.75 4.56482 21.134 3.36182 17.512C4.44982 21.259 7.90182 24 11.9998 24C16.0978 24 19.5498 21.259 20.6378 17.512C19.4348 21.134 16.0258 23.75 11.9998 23.75Z"
                            fill="#010101" fill-opacity="0.0627451" />
                          <path
                            d="M16.5 17.5C16.5 18.6935 16.0259 19.8381 15.182 20.682C14.3381 21.5259 13.1935 22 12 22C10.8065 22 9.66193 21.5259 8.81802 20.682C7.97411 19.8381 7.5 18.6935 7.5 17.5C7.5 15.823 8 14.958 9 14C9 16 10.5 16.5 10.5 16.5C10.5 13.65 11.5 11.958 13.5 11C13.75 13.875 16.5 14.813 16.5 17.5Z"
                            fill="#FFD33A" />
                          <path opacity="0.2"
                            d="M10.5 16.75C10.5 13.9 11.5 12.208 13.5 11.25C13.745 14.068 16.386 15.029 16.49 17.597C16.491 17.564 16.5 17.533 16.5 17.5C16.5 14.812 13.75 13.875 13.5 11C11.5 11.958 10.5 13.65 10.5 16.5C10.5 16.5 9 16 9 14C8 14.958 7.5 15.823 7.5 17.5C7.5 17.524 7.507 17.546 7.507 17.57C7.544 16.015 8.037 15.172 9 14.25C9 16.25 10.5 16.75 10.5 16.75Z"
                            fill="white" />
                          <defs>
                            <linearGradient id="paint0_linear_1733_16972" x1="12" y1="0" x2="12" y2="24" gradientUnits="userSpaceOnUse">
                              <stop stop-color="#FF6B35"/>
                              <stop offset="1" stop-color="#F7931E"/>
                            </linearGradient>
                          </defs>
                        </svg>
                      </span>
                      <?= $opportunity_text; ?>
                    </h4>
                    <h1 class="title_sld text-display fade-item fade-item-2" style="max-width: 600px;">
                      <a href="<?php the_permalink(); ?>" class="link fw-normal">
                        <?php the_title(); ?>
                      </a>
                    </h1>
                    <div class="price-wrap price_sld fade-item fade-item-3">
                      <span class="h1 fw-medium price-new text-primary">
                        <?= number_format($sale_price, 2); ?>
                        <small style="font-size:17px;">SAR</small>
                      </span>
                      <span class="price-old h3">
                        <?= number_format($regular_price, 2); ?>
                        <small>SAR</small>
                      </span>
                    </div>
                    <div class="fade-item fade-item-4">
                      <a href="<?php the_permalink(); ?>" class="tf-btn animate-btn fw-semibold">
                        <?= $shop_now_text; ?>
                        <i class="icon icon-arrow-<?= $is_rtl ? 'left' : 'right'; ?>"></i>
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

<?php 
    endwhile;
else :
    $no_products_text = ($lang === 'ar') ? 'لا توجد منتجات مخفضة حاليًا.' : 'No products on sale currently.';
    echo '<div class="swiper-slide"><div class="slider-wrap"><div class="container"><p>' . $no_products_text . '</p></div></div></div>';
endif;

wp_reset_postdata();
?>

    </div>
    <div class="sw-dot-default tf-sw-pagination"></div>
  </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // تأكد من تحميل Swiper
    if (typeof Swiper !== 'undefined') {
        const swiperElement = document.querySelector('.tf-slideshow .swiper');
        if (swiperElement) {
            const isRtl = swiperElement.getAttribute('data-rtl') === 'true';
            
            const swiper = new Swiper(swiperElement, {
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                autoplay: {
                    delay: 3000,
                    disableOnInteraction: false,
                },
                loop: true,
                direction: 'horizontal',
                rtl: isRtl,
                pagination: {
                    el: '.tf-sw-pagination',
                    clickable: true,
                },
                on: {
                    init: function () {
                        // console.log('Slider initialized');
                    },
                    slideChange: function () {
                        // console.log('Slide changed');
                    }
                }
            });
        }
    }
});
</script>

<!-- /Banner Slider -->