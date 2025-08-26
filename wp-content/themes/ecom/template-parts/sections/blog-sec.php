<?php
$lango = pll_current_language();
?>

<section>
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
                                <a href="blog-detail.html" class="entry_image img-style4">
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

<div class="flat-spacing">
    <div class="container">
        <div dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" class="swiper tf-swiper swiper-initialized swiper-horizontal swiper-backface-hidden"
            data-preview="4" data-tablet="3" data-mobile-sm="2" data-mobile="1" data-space-lg="97" data-space-md="33"
            data-space="13" data-pagination="1" data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="4">
            <div class="swiper-wrapper" id="swiper-wrapper-1228626932f8a81b" aria-live="polite">
                <!-- item 1 -->
                <div class="swiper-slide swiper-slide-active" role="group" aria-label="1 / 4"
                    style="width: 287.25px; margin-right: 97px;">
                    <div class="box-icon_V01 wow fadeInLeft animated"
                        style="visibility: visible; animation-name: fadeInLeft;">
                        <span class="icon">
                            <i class="icon-package"></i>
                        </span>
                        <div class="content">
                            <h4 class="title fw-normal"><?php echo $lango == 'ar' ? '30 يوماً' : '30 days'; ?> </h4>
                            <p class="text"><?php echo $lango == 'ar' ? 'ضمان استرداد المال' : 'Money back guarantee'; ?></p>
                        </div>
                    </div>
                </div>
                <!-- item 2 -->
                <div class="swiper-slide swiper-slide-next" role="group" aria-label="2 / 4"
                    style="width: 287.25px; margin-right: 97px;">

                    <div class="box-icon_V01 wow fadeInLeft animated" data-wow-delay="0.1s"
                        style="visibility: visible; animation-delay: 0.1s; animation-name: fadeInLeft;">
                        <span class="icon">
                            <i class="icon-calender"></i>
                        </span>
                        <div class="content">
                            <h4 class="title fw-normal"><?php echo $lango == 'ar' ? '3 سنوات' : '3 year warranty'; ?></h4>
                            <p class="text"><?php echo $lango == 'ar' ? 'ضمان العيوب' : 'Manufacturer\'s defect'; ?></p>
                        </div>
                    </div>
                </div>
                <!-- item 3 -->
                <div class="swiper-slide" role="group" aria-label="3 / 4" style="width: 287.25px; margin-right: 97px;">

                    <div class="box-icon_V01 wow fadeInLeft animated" data-wow-delay="0.2s"
                        style="visibility: visible; animation-delay: 0.2s; animation-name: fadeInLeft;">
                        <span class="icon">
                            <i class="icon-boat"></i>
                        </span>
                        <div class="content">
                            <h4 class="title fw-normal"><?php echo $lango == 'ar' ? 'شحن مجاني' : 'Free shipping'; ?></h4>
                            <p class="text"><?php echo $lango == 'ar' ? 'شحن مجاني' : 'Free Shipping for orders over $150'; ?></p>
                        </div>
                    </div>
                </div>
                <!-- item 4 -->
                <div class="swiper-slide" role="group" aria-label="4 / 4" style="width: 287.25px; margin-right: 97px;">
                    <div class="box-icon_V01 wow fadeInLeft animated" data-wow-delay="0.3s"
                        style="visibility: visible; animation-delay: 0.3s; animation-name: fadeInLeft;">
                        <span class="icon">
                            <i class="icon-headset"></i>
                        </span>
                        <div class="content">
                            <h4 class="title fw-normal"><?php echo $lango == 'ar' ? 'دعم اونلاين' : 'Online support'; ?></h4>
                            <p class="text"><?php echo $lango == 'ar' ? '24 ساعة في اليوم، 7 أيام في الأسبوع' : '24 hours a day, 7 days a week'; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="sw-dot-default tf-sw-pagination swiper-pagination-clickable swiper-pagination-bullets swiper-pagination-horizontal swiper-pagination-lock">
                <span class="swiper-pagination-bullet swiper-pagination-bullet-active" tabindex="0" role="button"
                    aria-label="Go to slide 1" aria-current="true"></span></div>
            <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
        </div>
    </div>
</div>