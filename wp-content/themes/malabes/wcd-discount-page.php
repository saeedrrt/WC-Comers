<?php
/**
 * Template Name: WCD Discount Rule Products
 * Template for displaying discount rule products
 */

get_header();

$rule_id = get_query_var('discount_rule_id');
$rule = get_post($rule_id);

// echo'<pre>';
// print_r($rule_id);
// echo'</pre>';

if (!$rule) {
    get_template_part('404');
    get_footer();
    return;
}

// جلب بيانات القاعدة
$rule_title = get_the_title($rule_id);
$percent = (float) get_post_meta($rule_id, '_wcd_percent', true);
$bannerTxt = (string) get_post_meta($rule_id, '_wcd_banner', true);

// تحضير نص العرض
$sale_text = $bannerTxt
    ? ((strpos($bannerTxt, '%s') !== false) ? sprintf($bannerTxt, $percent) : $bannerTxt)
    : sprintf('SALE upto %s%%', $percent);

// جلب IDs المنتجات المرتبطة بالقاعدة
$product_ids = wcd_get_rule_product_ids($rule_id);
?>


<section class="s-page-title mb-3">
    <div class="container">
        <div class="content">
            <h1 class="title-page"><?php echo esc_html($rule_title); ?></h1>
            <ul class="breadcrumbs-page">
                <li><a href="<?= home_url(); ?>" class="h6 link">Home</a></li>
                <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                <li>
                    <h6 class="current-page fw-normal"><?php echo esc_html($rule_title); ?></h6>
                </li>
            </ul>
        </div>
    </div>
</section>

<div class="container">
    <div class="row">
        <div class="col-12">
            <!-- عنوان الصفحة -->

            <?php if (empty($product_ids)): ?>
                    <div class="alert alert-info text-center">
                        <p>No products found for this offer.</p>
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="btn btn-primary">
                            Continue Shopping
                        </a>
                    </div>
            <?php else: ?>
                    <!-- المنتجات -->
                    <div class="woocommerce">
                        <div class="grid-layout" data-grid="grid-4" data-col-tablet="3" data-col-mobile="2">
                            <?php
                            // جلب المنتجات
                            $args = array(
                                'post_type' => 'product',
                                'post_status' => 'publish',
                                'post__in' => $product_ids,
                                'posts_per_page' => -1,
                                'orderby' => 'post__in'
                            );

                            $products_query = new WP_Query($args);

                            if ($products_query->have_posts()) {
                                while ($products_query->have_posts()) {
                                    $products_query->the_post();

                                    // إعداد بيانات المنتج
                                    $product = wc_get_product(get_the_ID());
                                    $product_id = $product->get_id();
                                    $product_title = $product->get_name();
                                    $product_permalink = $product->get_permalink();
                                    $product_price = $product->get_price();
                                    $product_regular_price = $product->get_regular_price();
                                    $product_sale_price = $product->get_sale_price();
                                    $product_stock_status = $product->get_stock_status();

                                    // صور المنتج
                                    $product_image_id = $product->get_image_id();
                                    $product_image = $product_image_id ? [wp_get_attachment_image_url($product_image_id, 'medium')] : [];

                                    // صورة التمرير
                                    $gallery_ids = $product->get_gallery_image_ids();
                                    $hover_image = !empty($gallery_ids) ? [wp_get_attachment_image_url($gallery_ids[0], 'medium')] : null;

                                    // الماركات (Brand)
                                    $product_brands = get_the_terms($product_id, 'product_brand'); // أو أي taxonomy للبراند
                                    ?>

                                            <div class="card-product grid">
                                                <div class="card-product_wrapper">
                                                    <a href="<?php echo $product_permalink; ?>" class="product-img"
                                                        style="position: relative; display: block; overflow: hidden; height: 100%;">
                                                        <?php if ($product_image): ?>
                                                                <img class="lazyload img-product main-product-image-<?php echo $product_id; ?>"
                                                                    src="<?php echo $product_image[0]; ?>" alt="<?php echo $product_title; ?>"
                                                                    style="width: 100%; height: 100%; display: block;">
                                                        <?php endif; ?>

                                                        <?php if ($hover_image): ?>
                                                                <img class="lazyload img-hover hover-product-image-<?php echo $product_id; ?>"
                                                                    src="<?php echo $hover_image[0]; ?>" alt="<?php echo $product_title; ?>"
                                                                    style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.3s ease;">
                                                        <?php endif; ?>

                                                        <!-- صورة اللون المحدد -->
                                                        <img class="lazyload color-product-image color-image-<?php echo $product_id; ?>" src="" alt=""
                                                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.3s ease; z-index: 2;">
                                                    </a>

                                                    <?php
                                                    // التحقق من وجود مقاسات المنتج
                                                    if (have_rows('product_size2', $product_id)): ?>
                                                            <div class="variant-box">
                                                                <ul class="product-size_list" data-product-id="<?php echo $product_id; ?>">
                                                                    <?php
                                                                    $size_index = 0;
                                                                    while (have_rows('product_size2', $product_id)):
                                                                        the_row();
                                                                        $size_name = get_sub_field('size_name');
                                                                        $size_code = get_sub_field('size_code');
                                                                        ?>
                                                                            <li class="size-item h6" data-product-id="<?php echo $product_id; ?>"
                                                                                data-size-name="<?php echo esc_attr($size_name); ?>" data-size-index="<?php echo $size_code; ?>">
                                                                                <?php echo $size_name; ?>
                                                                            </li>
                                                                            <?php
                                                                            $size_index++;
                                                                    endwhile; ?>
                                                                </ul>
                                                            </div>
                                                    <?php endif; ?>

                                                    <ul class="product-action_list">
                                                        <li>
                                                            <a href="?add-to-cart=<?php echo $product->get_id(); ?>"
                                                                class="add_to_cart_button ajax_add_to_cart product_type_simple box-icon hover-tooltip tooltip-left"
                                                                data-quantity="1" data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                                                                data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                                                                aria-label="<?php echo esc_attr(sprintf(__('Add "%s" to your cart', 'woocommerce'), $product->get_name())); ?>">
                                                                <span class="icon icon-shopping-cart-simple"></span>
                                                                <span class="tooltip"><?php _e('Add to cart', 'textdomain'); ?></span>
                                                            </a>
                                                        </li>
                                                        <li class="wishlist">
                                                            <?= do_shortcode('[ti_wishlists_addtowishlist loop=yes]'); ?>
                                                        </li>
                                                    </ul>

                                                    <?php if ($product_stock_status === 'instock'): ?>
                                                            <ul class="product-badge_list">
                                                                <li class="product-badge_item h6 hot">متاح</li>
                                                            </ul>
                                                    <?php endif; ?>

                                                    <?php if ($product_sale_price): ?>
                                                            <ul class="product-badge_list">
                                                                <li class="product-badge_item h6 sale">تخفيض</li>
                                                            </ul>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="card-product_info">
                                                    <a href="<?php echo $product_permalink; ?>" class="name-product h4 link"><?php echo $product_title; ?></a>

                                                    <div class="price-wrap">
                                                        <?php if ($product_sale_price): ?>
                                                                <span class="price-old h6 fw-normal"><?php echo wc_price($product_regular_price); ?></span>
                                                                <span class="price-new h6"><?php echo wc_price($product_sale_price); ?></span>
                                                        <?php else: ?>
                                                                <span class="price-new h6"><?php echo wc_price($product_price); ?></span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php
                                                    // التحقق من وجود ألوان المنتج
                                                    if (have_rows('product_attributes3', $product_id)): ?>
                                                            <ul class="product-color_list" data-product-id="<?php echo $product_id; ?>">
                                                                <?php
                                                                $color_index = 0;
                                                                while (have_rows('product_attributes3', $product_id)):
                                                                    the_row();
                                                                    $color_name = get_sub_field('color_name');
                                                                    $color_code = get_sub_field('color_code'); // كود اللون (هيكس)
                                                                    $color_image = get_sub_field('color_image'); // صورة اللون
                                                
                                                                    // الحصول على رابط الصورة
                                                                    $color_image_url = '';
                                                                    if ($color_image) {
                                                                        if (is_array($color_image)) {
                                                                            $color_image_url = $color_image['url'];
                                                                        } else {
                                                                            $attachment_url = wp_get_attachment_image_src($color_image, 'medium');
                                                                            $color_image_url = $attachment_url ? $attachment_url[0] : '';
                                                                        }
                                                                    }

                                                                    $is_active = $color_index === 0 ? 'active' : '';
                                                                    $display_color = $color_code ? $color_code : $color_name;
                                                                    ?>
                                                                        <li class="product-color-item color-swatch hover-tooltip tooltip-bot <?php //echo $is_active; ?>"
                                                                            style="background-color: <?php echo esc_attr($display_color); ?>;"
                                                                            data-product-id="<?php echo $product_id; ?>" data-color-image="<?php echo esc_url($color_image_url); ?>"
                                                                            data-color-name="<?php echo esc_attr($color_name); ?>" data-color-index="<?php echo $color_index; ?>">
                                                                            <span class="tooltip color-filter"><?php echo esc_html($color_name); ?></span>
                                                                            <span class="swatch-value"></span>
                                                                        </li>
                                                                        <?php
                                                                        $color_index++;
                                                                endwhile; ?>
                                                            </ul>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <?php
                                }
                                wp_reset_postdata();
                            }
                            ?>
                        </div>
                    
                        <!-- رابط العودة للمتجر -->
                        <div class="text-center my-5">
                            <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" 
                               class="tf-btn btn-outline-primary">
                                ← Back to Shop
                            </a>
                        </div>
                        
                    </div>
            <?php endif; ?>
        </div>
    </div>
</div>

 <script>
        jQuery(document).ready(function ($) {
            // عند الضغط على لون
            $('.product-color-item').on('click', function (e) {
                e.preventDefault();
                changeProductImage($(this), true);
            });

            // عند hover على لون
            $('.product-color-item').on('mouseenter', function () {
                showColorImage($(this));
            });

            // عند ترك hover، العودة للون النشط أو للصورة الأصلية
            $('.product-color-item').on('mouseleave', function () {
                var productId = $(this).data('product-id');
                var activeColor = $(this).closest('.product-color_list').find('.product-color-item.active');

                if (activeColor.length && !activeColor.is($(this))) {
                    // إذا كان هناك لون نشط وليس هو المُحدد، أظهر صورة اللون النشط
                    showColorImage(activeColor);
                } else {
                    // العودة للصورة الأصلية
                    returnToOriginalImage(productId);
                }
            });

            // عند ترك منطقة الألوان بالكامل
            $('.product-color_list').on('mouseleave', function () {
                var productId = $(this).data('product-id');
                var activeColor = $(this).find('.product-color-item.active');

                if (activeColor.length) {
                    // إذا كان هناك لون نشط، أظهر صورته
                    showColorImage(activeColor);
                } else {
                    // العودة للصورة الأصلية
                    returnToOriginalImage(productId);
                }
            });

            function showColorImage($colorItem) {
                var productId = $colorItem.data('product-id');
                var colorImage = $colorItem.data('color-image');
                var colorName = $colorItem.data('color-name');

                // التحقق من وجود رابط الصورة
                if (colorImage && colorImage !== '' && colorImage !== '#') {
                    var $colorImageEl = $('.color-image-' + productId);

                    // إخفاء صورة hover العادية
                    $('.hover-product-image-' + productId).css('opacity', '0');

                    // إظهار صورة اللون
                    $colorImageEl.attr('src', colorImage)
                        .attr('alt', colorName)
                        .css('opacity', '1');

                    // console.log('عرض صورة اللون:', colorImage, 'للمنتج:', productId);
                } else {
                    // إذا لم تكن هناك صورة للون، اخف صورة اللون وأظهر صورة hover العادية
                    hideColorImage(productId);
                    $('.hover-product-image-' + productId).css('opacity', '1');

                    // console.log('لا توجد صورة لهذا اللون');
                }
            }

            function hideColorImage(productId) {
                $('.color-image-' + productId).css('opacity', '0');
                // console.log('إخفاء صورة اللون للمنتج:', productId);
            }

            function returnToOriginalImage(productId) {
                // إخفاء صورة اللون وصورة hover
                $('.color-image-' + productId).css('opacity', '0');
                $('.hover-product-image-' + productId).css('opacity', '0');

                // التأكد من ظهور الصورة الأصلية
                $('.main-product-image-' + productId).css('opacity', '1');

                // console.log('العودة للصورة الأصلية للمنتج:', productId);
            }

            function changeProductImage($colorItem, isClick) {
                var productId = $colorItem.data('product-id');

                if (isClick) {
                    // إضافة/إزالة class النشط
                    $colorItem.siblings('.product-color-item').removeClass('active');
                    $colorItem.addClass('active');

                    // console.log('تم تحديد اللون:', $colorItem.data('color-name'));
                }

                showColorImage($colorItem);
            }

            // تفعيل hover العادي للمنتجات
            $('.card-product').each(function () {
                var $card = $(this);
                var productId = $card.find('.product-color_list').data('product-id');

                if (productId) {
                    var $productImg = $card.find('.product-img');

                    $productImg.on('mouseenter', function () {
                        var isColorImageVisible = $('.color-image-' + productId).css('opacity') == '1';
                        var $colorList = $card.find('.product-color_list');

                        // إذا لم تكن صورة لون ظاهرة وليس هناك hover على الألوان، أظهر صورة hover العادية
                        if (!isColorImageVisible && !$colorList.is(':hover')) {
                            $('.hover-product-image-' + productId).css('opacity', '1');
                        }
                    });

                    $productImg.on('mouseleave', function () {
                        var isColorImageVisible = $('.color-image-' + productId).css('opacity') == '1';
                        var $colorList = $card.find('.product-color_list');

                        // إذا لم تكن صورة لون ظاهرة وليس هناك hover على الألوان، اخف صورة hover العادية
                        if (!isColorImageVisible && !$colorList.is(':hover')) {
                            $('.hover-product-image-' + productId).css('opacity', '0');
                        }
                    });
                }
            });
        });
    </script>
<?php
get_footer();
?>