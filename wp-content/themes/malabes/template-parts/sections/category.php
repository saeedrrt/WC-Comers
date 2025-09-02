<?php
// Get product categories
$terms = get_terms(array(
  'taxonomy' => 'product_cat',
  'hide_empty' => false,
  'orderby' => 'name',
  'parent' => 0,
  'pad_counts' => true,
));

// Calculate dynamic preview count
$terms_count = !empty($terms) && !is_wp_error($terms) ? count($terms) : 0;

// Set responsive breakpoints based on categories count
$preview_desktop = min($terms_count, 6); // Max 6 on desktop
$preview_tablet = min($terms_count, 4);  // Max 4 on tablet
$preview_mobile = min($terms_count, 1);  // Max 2 on mobile

// Determine if we need navigation arrows
$show_navigation = $terms_count > 6;

$lango = pll_current_language();
?>

<section class="flat-spacing">
  <div class="container">
    <h1 class="sect-title text-center title wow fadeInUp"><?= $lango == 'ar' ? 'التصنيفات' : 'Product Category'; ?></h1>

    <?php if (!empty($terms) && !is_wp_error($terms)): ?>

      <div dir="ltr" class="swiper tf-swiper wow fadeInUp" data-preview="<?= $preview_desktop; ?>"
        data-tablet="<?= $preview_tablet; ?>" data-mobile="<?= $preview_mobile; ?>" data-space="48"
        data-loop="<?= $show_navigation ? 'true' : 'false'; ?>" data-auto="false" <?= $show_navigation ? 'data-arrows="true"' : ''; ?>>

        <div class="swiper-wrapper">
          <?php foreach ($terms as $index => $term):
            $term_link = get_term_link($term);
            $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
            $image_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, '') : wc_placeholder_img_src();
            $count = $term->count;
            ?>

            <div class="swiper-slide" style="margin-inline-end: 48px;" role="group"
              aria-label="<?= ($index + 1) . ' / ' . $terms_count; ?>">
              <a href="<?= esc_url($term_link); ?>" class="widget-collection style-circle hover-img"
                style="display: grid; justify-content: center;">
                <div class="collection_image img-style"
                  style="height: 200px; width: 200px; overflow: hidden; border-radius: 50%;">
                  <img class="ls-is-cached lazyloaded"
                    style="max-height: 200px; width: 100%; height: 100%; object-fit: cover;"
                    src="<?= esc_url($image_url); ?>" data-src="<?= esc_url($image_url); ?>"
                    alt="<?= esc_attr($term->name); ?>" loading="lazy">
                </div>
                <p class="collection_name h4 link text-center mt-3">
                  <?= esc_html($term->name); ?>
                  <span class="count33 text-main-2">(<?= $count; ?>)</span>
                </p>
              </a>
            </div>

          <?php endforeach; ?>
        </div>

        <!-- Pagination dots -->
        <?php if ($terms_count > $preview_desktop): ?>
          <div class="sw-dot-default tf-sw-pagination"></div>
        <?php endif; ?>


      </div>

    <?php else: ?>
      <div class="no-categories text-center">
        <p>No product categories found.</p>
      </div>
    <?php endif; ?>

  </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Initialize swiper with dynamic settings
    const swiperElement = document.querySelector('.tf-swiper');
    if (swiperElement) {
      const preview = parseInt(swiperElement.dataset.preview) || 1;
      const tablet = parseInt(swiperElement.dataset.tablet) || 1;
      const mobile = parseInt(swiperElement.dataset.mobile) || 1;
      const space = parseInt(swiperElement.dataset.space) || 0;
      const loop = swiperElement.dataset.loop === 'true';
      const arrows = swiperElement.dataset.arrows === 'true';

      new Swiper(swiperElement, {
        slidesPerView: mobile,
        spaceBetween: space,
        loop: loop,
        autoplay: false,
        breakpoints: {
          576: {
            slidesPerView: mobile,
          },
          768: {
            slidesPerView: tablet,
          },
          992: {
            slidesPerView: Math.min(preview, 4),
          },
          1200: {
            slidesPerView: preview,
          }
        },
        pagination: {
          el: '.tf-sw-pagination',
          clickable: true,
          type: 'bullets',
        },
        navigation: false,
      });
    }
  });
</script>