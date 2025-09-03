<?php
$lango = pll_current_language();
?>

<section class="flat-spacing">
    <div class="container">
        <div class="sect-title type-3 type-2 wow fadeInUp">
            <h2 class="s-title type-semibold text-nowrap"><?php echo $lango == 'ar' ? 'المقالات' : 'Blog'; ?></h2>
            <a href="<?php echo get_post_type_archive_link('post'); ?>" class="d-none tf-btn-icon h6 fw-medium text-nowrap">
                <?php echo $lango == 'ar' ? 'عرض جميع المقالات' : 'View All Blog'; ?>
                <i class="icon icon-caret-circle-right"></i>
            </a>
        </div>
        <div dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" class="swiper tf-swiper" data-preview="3" data-tablet="3" data-mobile-sm="2" data-mobile="1"
            data-space-lg="48" data-space-md="32" data-space="12" data-pagination="1" data-pagination-sm="2"
            data-pagination-md="3" data-pagination-lg="3">
            <div class="swiper-wrapper">
                <?php
                $args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 4,
                );
                $query = new WP_Query($args);
                if ($query->have_posts()):
                    while ($query->have_posts()):
                        $query->the_post();

                        $image_id = get_post_thumbnail_id();
                        $image_url = wp_get_attachment_image_url($image_id, 'full');
                        ?>
                        <!-- item 1 -->
                        <div class="swiper-slide">
                            <div class="article-blog type-space-2 hover-img4 wow fadeInLeft">
                                <a href="<?php the_permalink(); ?>" class="entry_image img-style4">
                                    <img src="<?php echo $image_url; ?>" data-src="<?php echo $image_url; ?>" alt="Blog"
                                class="lazyload aspect-ratio-0">
                        </a>
                        <div class="entry_tag">
                            <a href="<?php the_permalink(); ?>" class="name-tag h6 link"><?php echo get_the_date(); ?></a>
                        </div>

                        <div class="blog-content">
                            <a href="<?php the_permalink(); ?>" class="entry_name link h4">
                                <?php the_title(); ?>
                            </a>
                            <p class="text h6">
                                <?php the_excerpt(); ?>
                            </p>
                            <a href="<?php the_permalink(); ?>" class="tf-btn-line">
                                <?php echo $lango == 'ar' ? 'اقرأ المزيد' : 'Read more'; ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>
            <div class="sw-dot-default tf-sw-pagination"></div>
        </div>
    </div>
</section>
