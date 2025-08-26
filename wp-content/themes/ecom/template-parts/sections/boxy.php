<!-- Box Image -->
<div class="flat-spacing pt-0">
    <div class="container">
        <div dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" class="swiper tf-swiper" data-preview="3" data-tablet="2" data-mobile-sm="1" data-mobile="1"
            data-space-lg="48" data-space-md="32" data-space="12" data-pagination="1" data-pagination-sm="1"
            data-pagination-md="2" data-pagination-lg="3">
            <div class="swiper-wrapper">
    <?php
$rules_q = new WP_Query([
    'post_type' => 'wc_discount_rule',
    'post_status' => 'publish',
    'posts_per_page' => 3,
    'orderby' => 'date',
    'order' => 'DESC',
]);

if ($rules_q->have_posts()):
    while ($rules_q->have_posts()):
        $rules_q->the_post();
        $rule_id = get_the_ID();
        $title = get_the_title() ?: 'Sale';
        $discount_url = wcd_get_discount_rule_url($rule_id);

        $percent = (float) get_post_meta($rule_id, '_wcd_percent', true);
        $bannerTxt = (string) get_post_meta($rule_id, '_wcd_banner', true);

        $sale_text = $bannerTxt
            ? ((strpos($bannerTxt, '%s') !== false) ? sprintf($bannerTxt, $percent) : $bannerTxt)
            : sprintf('SALE upto %s%%', $percent);

        if (has_post_thumbnail($rule_id)) {
            $image_url = get_the_post_thumbnail_url($rule_id, 'large');
        } else {
            $image_url = wc_placeholder_img_src();
        }
        ?>
        <div class="swiper-slide">
            <div class="box-image_V05 type-space-2 hover-img wow fadeInLeft">
                <a href="<?php echo esc_url($discount_url); ?>" class="box-image_image img-style" style="position: relative;">
                    <img src="<?php echo esc_url($image_url); ?>" data-src="<?php echo esc_url($image_url); ?>"
                        alt="<?php echo esc_attr($title); ?>" class="lazyload" />
        
                    <?php if ($percent): ?>
                        <span class="sale-badge"><?php echo esc_html($percent); ?>%</span>
                    <?php endif; ?>
                </a>
                <div class="box-image_content">
                    <p class="sub-title text-primary h6 fw-semibold"><?php echo esc_html($sale_text); ?></p>
                    <h4 class="title">
                        <a href="<?php echo esc_url($discount_url); ?>" class="link">
                            <?php echo esc_html($title); ?>
                        </a>
                    </h4>
                    <a href="<?php echo esc_url($discount_url); ?>" class="tf-btn-line fw-bold letter-space-0"><?php echo is_rtl() ? 'تسوق الآن' : 'Shop now'; ?></a>
                </div>
            </div>
        </div>
        <?php
    endwhile;
    wp_reset_postdata();
endif;
?>


            </div>
            <div class="sw-dot-default tf-sw-pagination"></div>
        </div>
    </div>
</div>
<!-- /Box Image -->

