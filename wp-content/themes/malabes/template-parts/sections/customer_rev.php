<?php 
$lango = pll_current_language();
?>

<section class="flat-spacing bg-white-smoke mb-4">
    <div class="container">
        <div class="sect-title text-center wow fadeInUp animated"
            style="visibility: visible; animation-name: fadeInUp;">
            <h1 class="s-title mb-8"><?= $lango == 'ar' ? 'أراء العملاء' : 'Customer Reviews' ?></h1>
            
        </div>
        <div class="tf-btn-swiper-main pst-2">
            <div dir="<?= is_rtl() ? 'rtl' : 'ltr' ?>" class="swiper tf-swiper swiper-initialized swiper-horizontal swiper-backface-hidden"
                data-preview="3" data-tablet="2" data-mobile-sm="1" data-mobile="1" data-space-lg="48"
                data-space-md="32" data-space="12" data-pagination="1" data-pagination-sm="1" data-pagination-md="2"
                data-pagination-lg="3">
                <div class="swiper-wrapper" id="swiper-wrapper-6cc67bad87e6e7e7" aria-live="polite">

                    <!-- item 1 -->
                    <?php 
            if( have_rows('customer_reviews', 'option') ):
                while ( have_rows('customer_reviews', 'option') ) : the_row();
                    $customer_title = get_sub_field('title_en');
                    $customer_title_ar = get_sub_field('title_ar');
                    $customer_review = get_sub_field('message_en');
                    $customer_review_ar = get_sub_field('message_ar');
                    $cust_name = get_sub_field('name_en');
                    $cust_name_ar = get_sub_field('name_ar');
                    ?>
                    <div class="swiper-slide swiper-slide-active" role="group" aria-label="1 / 4"
                        style="width: 448px; margin-right: 48px;">
                        <div class="testimonial-V01 border-0 wow fadeInLeft animated"
                            style="visibility: visible; animation-name: fadeInLeft;">
                            <div class="">
                                <h4 class="tes_title"><?= $lango == 'ar' ? $customer_title_ar : $customer_title ?></h4>
                                <p class="tes_text h4">
                                    “<?= $lango == 'ar' ? $customer_review_ar : $customer_review ?>“
                                </p>
                                <div class="tes_author">
                                    <p class="author-name h4"><?= $lango == 'ar' ? $cust_name_ar : $cust_name ?></p>
                                    <i class="author-verified icon-check-circle fs-24"></i>
                                </div>
                                <div class="rate_wrap">
                                    <i class="icon-star text-star"></i>
                                    <i class="icon-star text-star"></i>
                                    <i class="icon-star text-star"></i>
                                    <i class="icon-star text-star"></i>
                                    <i class="icon-star text-star"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                endwhile;
            endif;
                    ?>
                    

                </div>
                <div
                    class="sw-dot-default tf-sw-pagination swiper-pagination-clickable swiper-pagination-bullets swiper-pagination-horizontal">
                    <span class="swiper-pagination-bullet swiper-pagination-bullet-active" tabindex="0" role="button"
                        aria-label="Go to slide 1" aria-current="true"></span><span class="swiper-pagination-bullet"
                        tabindex="0" role="button" aria-label="Go to slide 2"></span></div>
                <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
            </div>
            <div class="tf-sw-nav nav-prev-swiper swiper-button-disabled" tabindex="-1" role="button"
                aria-label="Previous slide" aria-controls="swiper-wrapper-6cc67bad87e6e7e7" aria-disabled="true">
                <i class="icon icon-caret-left"></i>
            </div>
            <div class="tf-sw-nav nav-next-swiper" tabindex="0" role="button" aria-label="Next slide"
                aria-controls="swiper-wrapper-6cc67bad87e6e7e7" aria-disabled="false">
                <i class="icon icon-caret-right"></i>
            </div>
        </div>
    </div>
</section>