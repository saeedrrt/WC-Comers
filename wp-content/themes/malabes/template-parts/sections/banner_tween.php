<?php
$lango = pll_current_language();
?>


<div class="s-collection">
    <div dir="<?= $lango == 'ar' ? 'rtl' : 'ltr' ?>" class="swiper tf-swiper" data-preview="2" data-tablet="2" data-mobile-sm="2" data-mobile="1"
        data-pagination="1" data-space-lg="24" data-space-md="15" data-space="10" data-pagination-sm="1"
        data-pagination-md="2" data-pagination-lg="2">
        <div class="swiper-wrapper">

         <?php
            $args = array(
                'post_type' => 'home_banner',
                'posts_per_page' => -1,
                'tax_query' => array(
                    array(
                        'taxonomy' => 'banners_cat',
                        'field' => 'slug',
                        'terms' => 'home-one'
                    )
                )
            );
            $the_query = new WP_Query($args);
            if ($the_query->have_posts()):
                while ($the_query->have_posts()):
                    $the_query->the_post();
                    $image_id = get_post_thumbnail_id();
                    $image_url = wp_get_attachment_image_url($image_id, 'full');
                    ?>
            <!-- item 1 -->
            <div class="swiper-slide">
                <div class="wg-cls-2 type-space-2 d-flex hover-img">
                    <a href="<?= site_url('shop'); ?>" class="image img-style">
                        <img class="lazyload" src="<?= $image_url ?>" data-src="<?= $image_url ?>"
                            alt="Slider">
                    </a>
                    <div class="cls-content_wrap">
                        <div class="cls-content">
                            <a href="<?= site_url('shop'); ?>" class="tag_cls h2 type-semibold link"><?= pll_current_language() == 'ar' ? the_field('arabic_title') : the_title(); ?></a>
                            <span class="br-line type-vertical"></span>
                            <a href="<?= site_url('shop'); ?>" class="tf-btn-line text-nowrap">
                                <?= pll_current_language() == 'ar' ? 'تسوق الآن' : 'Shop now'; ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        <?php endwhile;
                wp_reset_postdata(); endif; ?>
        </div>
        <div class="sw-dot-default tf-sw-pagination"></div>
    </div>
</div>
