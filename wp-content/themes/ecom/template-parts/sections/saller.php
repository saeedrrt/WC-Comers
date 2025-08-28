<?php
$lango = pll_current_language();
// التاج المطلوب
$tag_id = 240;

// استعلام منتجات بتاج محدد (ID)
$args = array(
    'post_type' => 'product',
    'posts_per_page' => 8,
    'post_status' => 'publish',
    'tax_query' => array(
        array(
            'taxonomy' => 'product_tag', // مش product-cat
            'field' => 'term_id',
            'terms' => array((int) $tag_id),
            'operator' => 'IN',
        ),
    ),
);

$featured_products = new WP_Query($args);

// لينك صفحة المتجر مع بارامتر التاج (شغال مع كود pre_get_posts اللي فلترناه قبل كده)
$shop_url = wc_get_page_permalink('shop');
$shop_with_tag_link = add_query_arg('product-tag', $tag_id, $shop_url);

if ($featured_products->have_posts()) : ?>

<section class="flat-spacing">
    <div class="container">
        <div class="sect-title type-3 type-2 wow fadeInUp animated"
            style="visibility: visible; animation-name: fadeInUp;">
            <h2 class="s-title type-semibold text-nowrap"><?php echo $lango == 'ar' ? 'المنتجات الأكثر شعبية' : 'Product Best Sellers'; ?></h2>
            
            <!-- الرابط يوجه إلى صفحة المتجر مع parameter التاج -->
            <a href="<?php echo esc_url($shop_with_tag_link); ?>" class="tf-btn-icon h6 fw-medium text-nowrap">
                <?php echo $lango == 'ar' ? 'عرض جميع المنتجات' : 'View All Product'; ?>
                <i class="icon icon-caret-circle-right"></i>
            </a>
        </div>
        
        <div dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>"
            class="swiper tf-swiper wow fadeInUp swiper-initialized swiper-horizontal swiper-backface-hidden animated"
            data-preview="4" data-tablet="3" data-mobile-sm="2" data-mobile="2" data-space-lg="48" data-space-md="24"
            data-space="12" data-pagination="2" data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="4"
            style="visibility: visible; animation-name: fadeInUp;">
            <div class="swiper-wrapper" id="swiper-wrapper-637fcc62c358bb2e" aria-live="polite">
                
                <?php 
                $slide_count = 0;
                while ($featured_products->have_posts()) : $featured_products->the_post();
                    global $product;
                    $slide_count++;
                    
                    // الحصول على معلومات المنتج
                    $product_id = get_the_ID();
                    $product_title = get_the_title();
                    $product_permalink = get_permalink();
                    $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                    $product_gallery = $product->get_gallery_image_ids();
                    $product_price = $product->get_price_html();
                    $regular_price = $product->get_regular_price();
                    $sale_price = $product->get_sale_price();
                    $rating = $product->get_average_rating();
                    $rating_count = $product->get_rating_count();
                    
                    $banner_prod = get_field('product_banner', $product->get_id());
                    
                    // حساب نسبة الخصم
                    $discount_percentage = 0;
                    if ($sale_price && $regular_price) {
                        $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
                    }
                    
                    // الحصول على تصنيف المنتج
                    $product_categories = wp_get_post_terms($product_id, 'product_cat');
                    $category_name = !empty($product_categories) ? $product_categories[0]->name : '';
                    
                    // صورة hover (الصورة الثانية من الجاليري)
                    $hover_image = '';
                    if (!empty($product_gallery)) {
                        $hover_image = wp_get_attachment_image_src($product_gallery[0], 'full');
                    }
                ?>
                
                <!-- Product <?php echo $slide_count; ?> -->
                <div class="swiper-slide <?php echo ($slide_count == 1) ? 'swiper-slide-active' : ''; ?>" role="group" aria-label="<?php echo $slide_count; ?> / <?php echo $featured_products->found_posts; ?>"
                    style="width: 324px; margin-right: 48px;">
                    <div class="card-product style-5">
                        <div class="card-product_wrapper aspect-ratio-0 d-flex">
                            <a href="<?php echo $product_permalink; ?>" class="product-img">
                                <?php if ($product_image) : ?>
                                <img class="img-product ls-is-cached lazyloaded"
                                    src="<?php echo $product_image[0]; ?>"
                                    data-src="<?php echo $product_image[0]; ?>" alt="<?php echo $product_title; ?>">
                                <?php endif; ?>
                                
                                <?php if ($hover_image) : ?>
                                <img class="img-hover ls-is-cached lazyloaded"
                                    src="<?php echo $hover_image[0]; ?>"
                                    data-src="<?php echo $hover_image[0]; ?>" alt="<?php echo $product_title; ?>">
                                <?php endif; ?>
                            </a>
                            <ul class="product-action_list">
                                <li>
                                    <a href="?add-to-cart=<?php echo $product->get_id(); ?>" class="add_to_cart_button ajax_add_to_cart product_type_simple
          box-icon hover-tooltip tooltip-left" data-quantity="1"
                                        data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                                        data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                                        aria-label="<?php echo esc_attr(sprintf(__('Add "%s" to your cart', 'woocommerce'), $product->get_name())); ?>">
                                        <span class="icon icon-shopping-cart-simple"></span>
                                        <span class="tooltip"><?php _e('Add to cart', 'textdomain'); ?></span>
                                    </a>
                                </li>
                                <li class="wishlist">
                                    <?= do_shortcode("[ti_wishlists_addtowishlist loop=yes]");?>
                                </li>
                                <li class="compare">
                                    <a href="#compare" data-bs-toggle="offcanvas"
                                        data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
                                        class="compare-btn hover-tooltip tooltip-left box-icon">
                                        <span class="icon icon-compare"></span>
                                        <span class="tooltip"><?php echo $lango == 'ar' ? 'مقارنة' : 'Compare'; ?></span>
                                    </a>
                                </li>
                            </ul>
                            
                            <?php if ($discount_percentage > 0 || !empty($product_tags)) : ?>
                            <ul class="product-badge_list">
                                <?php if ($discount_percentage > 0) : ?>
                                <li class="product-badge_item h6 sale">-<?php echo $discount_percentage; ?>%</li>
                                <?php endif; ?>

                                <?php
                                // الحصول على التاجات الخاصة بالمنتج
                                $product_tags = wp_get_post_terms(get_the_ID(), 'product_tag');

                                if (!empty($product_tags) && !is_wp_error($product_tags)): ?>
                                    <?php foreach ($product_tags as $tag):
                                        // تحديد الكلاس والخلفية حسب اسم التاج
                                        $tag_class = '';
                                        $tag_style = '';

                                        switch (strtolower($tag->name)) {
                                            case 'flash sale':
                                                $tag_class = 'flash-sale';
                                                $tag_style = 'background-color: #000000; color: #ffffff;';
                                                break;
                                            case 'new':
                                            case 'new arrival':
                                                $tag_class = 'new-arrival';
                                                $tag_style = 'background-color: #28a745; color: #ffffff;';
                                                break;
                                            case 'limited':
                                            case 'محدود':
                                                $tag_class = 'limited-edition';
                                                $tag_style = 'background-color: #dc3545; color: #ffffff;';
                                                break;
                                            case 'exclusive':
                                            case 'حصري':
                                                $tag_class = 'exclusive';
                                                $tag_style = 'background-color: #6f42c1; color: #ffffff;';
                                                break;
                                            case 'bestseller':
                                            case 'hot':
                                                $tag_class = 'hot';
                                                $tag_style = 'background-color: #fd7e14; color: #ffffff;';
                                                break;
                                            default:
                                                $tag_class = 'default-tag';
                                                $tag_style = 'background-color: #6c757d; color: #ffffff;';
                                                break;
                                        }
                                        ?>
                                        <li class="product-badge_item h6 <?php echo esc_attr($tag_class); ?>" style="<?php echo esc_attr($tag_style); ?>">
                                            <?php echo esc_html($tag->name); ?>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                            <?php endif; ?>
                        </div>
                        <div class="card-product_info d-grid">
                            <?php if ($category_name) : ?>
                            <p class="tag-product text-small"><?php echo $category_name; ?></p>
                            <?php endif; ?>
                            
                            <h6 class="name-product">
                                <a href="<?php echo $product_permalink; ?>" class="link"><?php echo $product_title; ?></a>
                            </h6>
                            
                            <?php if ($rating > 0) : ?>
                            <div class="rate_wrap w-100">
                                <?php
                                $full_stars = floor($rating);
                                $half_star = ($rating - $full_stars) >= 0.5;
                                $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                                
                                // النجوم الممتلئة
                                for ($i = 0; $i < $full_stars; $i++) {
                                    echo '<i class="icon-star text-star"></i>';
                                }
                                
                                // النجمة النصف ممتلئة
                                if ($half_star) {
                                    echo '<i class="icon-star-half text-star"></i>';
                                }
                                
                                // النجوم الفارغة
                                for ($i = 0; $i < $empty_stars; $i++) {
                                    echo '<i class="icon-star text-muted"></i>';
                                }
                                ?>
                            </div>
                            <?php endif; ?>
                            
                            <div class="price-wrap mb-0">
                                <?php if ($sale_price) : ?>
                                    <h4 class="price-new"><?php echo wc_price($sale_price); ?></h4>
                                    <span class="price-old h6"><?php echo wc_price($regular_price); ?></span>
                                <?php else : ?>
                                    <h4 class="price-new"><?php echo wc_price($regular_price); ?></h4>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php endwhile; ?>

            </div>
            <div class="sw-dot-default tf-sw-pagination d-xl-none swiper-pagination-clickable swiper-pagination-bullets swiper-pagination-horizontal">
                <?php for ($i = 1; $i <= $featured_products->found_posts; $i++) : ?>
                <span class="swiper-pagination-bullet <?php echo ($i == 1) ? 'swiper-pagination-bullet-active' : ''; ?>" 
                      tabindex="0" role="button" aria-label="Go to slide <?php echo $i; ?>" 
                      <?php echo ($i == 1) ? 'aria-current="true"' : ''; ?>></span>
                <?php endfor; ?>
            </div>
            <span class="swiper-notification" aria-live="assertive" aria-atomic="true"></span>
        </div>
    </div>
</section>

<?php endif; ?>

<?php wp_reset_postdata(); ?>