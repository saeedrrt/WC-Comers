<?php
$lang = pll_current_language('slug');
$is_rtl = ($lang === 'ar');
?>

<div class="tf-slideshow type-abs tf-btn-swiper-main hover-sw-nav">
  <div dir="<?= $is_rtl ? 'rtl' : 'ltr'; ?>" class="swiper tf-swiper33 sw-slide-show slider_effect_fade" data-auto="true" data-loop="true"
    data-effect="fade" data-delay="3000" data-rtl="<?= $is_rtl ? 'true' : 'false'; ?>">
    <div class="swiper-wrapper">

    <?php
      $args = array(
        'post_type' => 'product',
        'posts_per_page' => 3,
        'post__in' => wc_get_product_ids_on_sale(),
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_query' => array(
          'relation' => 'AND',
      
          // 2) شرط وجود الـ banner (ID > 0)
          array(
            'key' => 'product_banner',
            'value' => 0,
            'type' => 'NUMERIC',
            'compare' => '>',
          ),
        ),
      );

      $loop = new WP_Query($args);

      if ($loop->have_posts()):
        while ($loop->have_posts()):
          $loop->the_post();
          global $product;
          $regular_price = $product->get_regular_price();
          $sale_price = $product->get_sale_price();
          $banner_prod = get_field('product_banner', $product->get_id());

          // النصوص المترجمة
          $opportunity_text = ($lang === 'ar') ? 'لا تفوت الفرصة' : 'Don\'t miss the opportunity';
          $shop_now_text = ($lang === 'ar') ? 'تسوق الآن' : 'Shop now';
          ?>
      <!-- item 1 -->
      <div class="swiper-slide">
        <div class="slider-wrap style-2">
          <div class="sld_image">
            <img src="<?= esc_url($banner_prod); ?>" data-src="<?= esc_url($banner_prod); ?>"
              alt="<?= get_the_title(); ?>" class="lazyload scale-item" style="min-height: 690px;">
          </div>
          <div class="sld_content type-center text-sm-center">
            <div class="container">
              <div class="row">
                <div class="col-sm-8 col-10">
                  <div class="content-sld_wrap">
                    <p class="sub-title_sld h3 text-primary fade-item fade-item-1">
                      <?= $opportunity_text; ?>
                    </p>
                    <h1 class="title_sld text-display fade-item fade-item-2">
                      <?= $shop_now_text; ?>
                    </h1>
                    <p class="sub-text_sld h5 text-black fade-item fade-item-3">
                      <?= $shop_now_text; ?>
                    </p>
                    <div class="fade-item fade-item-4">
                      <a href="<?= get_the_permalink(); ?>" class="tf-btn animate-btn fw-normal">
                        <?= $shop_now_text; ?>
                        <i class="icon icon-arrow-right"></i>
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
  <div class="tf-sw-nav nav-prev-swiper">
    <i class="icon icon-caret-left"></i>
  </div>
  <div class="tf-sw-nav nav-next-swiper">
    <i class="icon icon-caret-right"></i>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
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