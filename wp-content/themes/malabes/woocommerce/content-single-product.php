<?php
/**
 * Single Product Template
 * 
 * This template can be copied to yourtheme/woocommerce/single-product.php
 * 
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

get_header();

$lango = pll_current_language();

// Get the current product or highest discount product
$current_product = wc_get_product(get_the_ID());
$product = $current_product ? $current_product : get_highest_discount_product_with_timer();

if (!$product) {
    return;
}

$product_id = $product->get_id();
// إضافة دالة للتحقق من تصنيف المنتج
function is_electronics_category($product_id) {
    // نحصل على تصنيفات المنتج
    $product_categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
    
    if (empty($product_categories)) {
        return false;
    }
    
    // نحصل على تصنيف الإلكترونيات والتصنيفات الفرعية
    $electronics_category = get_term_by('slug', 'electronic', 'product_cat');
    if (empty($electronics_category)) {
        $electronics_category = get_term_by('slug', 'electronic_ar', 'product_cat');
    }
    if (empty($electronics_category)) {
        $electronics_category = get_term_by('slug', 'electronic', 'product_cat');
    }
    if (empty($electronics_category)) {
        // إذا لم يوجد تصنيف بالـ slug هذا، نبحث بالاسم
        $electronics_category = get_term_by('name', 'electronic_ar', 'product_cat');
        if (empty($electronics_category)) {
            $electronics_category = get_term_by('name', 'Electronic', 'product_cat');
        }
    }
    
    if (empty($electronics_category)) {
        return false;
    }
    
    // نتحقق إذا كان المنتج في تصنيف الإلكترونيات أو أي تصنيف فرعي منه
    $electronics_and_children = get_terms(array(
        'taxonomy' => 'product_cat',
        'child_of' => $electronics_category->term_id,
        'hide_empty' => false,
        'fields' => 'ids'
    ));
    
    // إضافة تصنيف الإلكترونيات نفسه للمصفوفة
    $electronics_and_children[] = $electronics_category->term_id;
    
    // نتحقق إذا كان أي تصنيف من تصنيفات المنتج موجود في تصنيفات الإلكترونيات
    return !empty(array_intersect($product_categories, $electronics_and_children));
}

// Function to get product images
function get_product_images($product)
{
    $attachment_ids = $product->get_gallery_image_ids();
    $main_image_id = $product->get_image_id();

    if ($main_image_id) {
        array_unshift($attachment_ids, $main_image_id);
    }

    return $attachment_ids;
}

// Function to display product rating
function display_product_rating($product)
{
    $rating = $product->get_average_rating();
    $review_count = $product->get_review_count();

    $output = '<div class="rating"><div class="d-flex gap-4">';

    for ($i = 1; $i <= 5; $i++) {
        $fill = ($i <= $rating) ? '#EF9122' : '#E0E0E0';
        $output .= '<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">';
        $output .= '<path d="M14 5.4091L8.913 5.07466L6.99721 0.261719L5.08143 5.07466L0 5.4091L3.89741 8.7184L2.61849 13.7384L6.99721 10.9707L11.376 13.7384L10.097 8.7184L14 5.4091Z" fill="' . $fill . '" />';
        $output .= '</svg>';
    }

    $output .= '</div>';
    $output .= '<div class="reviews text-main">(' . $review_count . ' review' . ($review_count != 1 ? 's' : '') . ')</div>';
    $output .= '</div>';

    return $output;
}

// عرض السعر مع الخصم – يدعم العملة SAR
function display_product_price(WC_Product $product)
{
    // الـ HTML المرجَّع
    $output = '<div class="tf-product-heading"><div class="product-info-price price-wrap">';

    // الأسعار
    $regular_price = (float) $product->get_regular_price(); // السعر قبل الخصم
    $sale_price = (float) $product->get_sale_price();    // سعر الخصم (لو موجود)

    // لو في خصم فعلاً
    if ($sale_price && $sale_price < $regular_price) {

        // نسبة الخصم
        $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);

        // السعر بعد الخصم
        $output .= '<span class="price-new h2 fw-4">' .
            wc_price($sale_price, [
                'currency' => 'SAR',
                'price_format' => '%2$s SAR',
            ]) .
            '</span>';

        // السعر الأصلي
        $output .= '<span class="price-old h6">' .
            wc_price($regular_price, [
                'currency' => 'SAR',
                'price_format' => '%2$s SAR',
            ]) .
            '</span>';

        // شارة الخصم
        $output .= '<p class="badges-on-sale h6 fw-semibold"><span class="number-sale">-' .
            $discount_percentage .
            '%</span></p>';

    } else {
        // مفيش خصم
        $output .= '<span class="price-new h2 fw-4">' . $product->get_price_html() . '</span>';
    }

    // إغلاق الـ div‑ات
    $output .= '</div></div>';

    return $output;
}

// Function to display countdown timer
function display_countdown_timer($product)
{
    if (!$product->is_on_sale()) {
        return '';
    }

    $product_id = $product->get_id();
    $sale_end_timestamp = get_post_meta($product_id, '_sale_price_dates_to', true);

    if (!$sale_end_timestamp) {
        return '';
    }

    $now = current_time('timestamp');
    $seconds_remaining = $sale_end_timestamp - $now;

    if ($seconds_remaining <= 0) {
        return '';
    }

    $lango = pll_current_language();

    $output = '<div class="tf-product-info-countdown">';
    $output .= '<div class="countdown-title">';
    $output .= $lango == 'ar' ? '<h5>أسرع</h5>' : '<h5>Hurry up</h5>';
    $output .= $lango == 'ar' ? '<p class="text-main">العرض ينتهي في:</p>' : '<p class="text-main">offer ends in:</p>';
    $output .= '</div>';
    $output .= '<div class="tf-countdown style-1">';
    $output .= '<div class="js-countdown" data-timer="' . esc_attr($seconds_remaining) . '" data-labels="Days,Hours,Mins,Secs"></div>';
    $output .= '</div>';
    $output .= '</div>';

    return $output;
}

// Function to display product attributes
function display_product_attributes($product)
{
    $attributes = $product->get_attributes();

    if (empty($attributes)) {
        return '';
    }

    $output = '<div class="list-infor tf-grid-layout md-col-1 xl-col-12">';

    foreach ($attributes as $attribute) {
        $label = wc_attribute_label($attribute->get_name());

        if ($attribute->is_taxonomy()) {
            $options = wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']);
        } else {
            $options = $attribute->get_options();
        }

        if (!empty($options)) {
            $output .= '<div class="infor-item">';
            $output .= '<div class="h4 heading">' . esc_html($label) . '</div>';
            $output .= '<ul>';

            foreach ($options as $opt) {
                $output .= '<li>';
                $output .= '<h6 class="fw-6 text-black title">' . esc_html($opt) . ':</h6>';
                $output .= '<div class="h6">' . esc_html($opt) . '</div>';
                $output .= '</li>';
            }

            $output .= '</ul>';
            $output .= '</div>';
        }
    }

    $output .= '</div>';

    return $output;
}

// Function to get related products
function get_related_products($product, $limit = 8)
{
    $product_id = $product->get_id();
    $category_ids = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);

    if (empty($category_ids)) {
        return [];
    }

    $args = [
        'post_type' => 'product',
        'posts_per_page' => $limit,
        'post__not_in' => [$product_id],
        'post_status' => 'publish',
        'tax_query' => [
            [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category_ids,
                'operator' => 'IN'
            ]
        ],
        'meta_query' => [
            [
                'key' => '_stock_status',
                'value' => 'instock'
            ]
        ]
    ];

    $related_query = new WP_Query($args);
    return $related_query->posts;
}

// Function to display related products
function display_related_products($product)
{
    $lango = pll_current_language();
    $related_products = get_related_products($product);

    if (empty($related_products)) {
        return '';
    }

    $output = '<section class="flat-spacing-3 pt-0 mt-5">';
    $output .= '<div class="container">';
    $output .= $lango == 'ar' ? '<h1 class="sect-title text-center">منتجات ذات صلة</h1>' : '<h1 class="sect-title text-center">Related Products</h1>';
    $output .= '<div dir="' . ($lango == 'ar' ? 'rtl' : 'ltr') . '" class="swiper tf-swiper wrap-sw-over" data-preview="4" data-tablet="3" data-mobile-sm="2" data-mobile="2" data-space-lg="48" data-space-md="30" data-space="12" data-pagination="2" data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="4">';
    $output .= '<div class="swiper-wrapper">';

    foreach ($related_products as $related_post) {
        $related_product = wc_get_product($related_post->ID);

        if (!$related_product) {
            continue;
        }

        $output .= '<div class="swiper-slide">';
        $output .= '<div class="card-product">';
        $output .= '<div class="card-product_wrapper">';

        // Product images
        $main_image = $related_product->get_image('woocommerce_thumbnail');
        $gallery_images = $related_product->get_gallery_image_ids();
        $hover_image = '';

        if (!empty($gallery_images)) {
            $hover_image = wp_get_attachment_image($gallery_images[0], 'woocommerce_thumbnail', false, ['class' => 'lazyload img-hover']);
        }

        $output .= '<a href="' . get_permalink($related_post->ID) . '" class="product-img">';
        $output .= $main_image;
        if ($hover_image) {
            $output .= $hover_image;
        }
        $output .= '</a>';

        // Product actions
        // بعد
        $output .= '<ul class="product-action_list">';

        // زرّ إضافة إلى السلة عبر AJAX
        $output .= '<li>';
        $output .= sprintf(
            '<a href="%1$s" class="add_to_cart_button ajax_add_to_cart product_type_simple box-icon hover-tooltip tooltip-left" data-quantity="1" data-product_id="%2$d" data-product_sku="%3$s" aria-label="%4$s">',
            esc_url('?add-to-cart=' . $related_product->get_id()),
            esc_attr($related_product->get_id()),
            esc_attr($related_product->get_sku()),
            esc_attr(sprintf(__('Add “%s” to your cart', 'woocommerce'), $related_product->get_name()))
        );
        $output .= '<span class="icon icon-shopping-cart-simple"></span>';
        $output .= '<span class="tooltip">' . esc_html__('Add to cart', 'textdomain') . '</span>';
        $output .= '</a>';
        $output .= '</li>';

        // باقي الأيقونات
        $output .= '<li class="wishlist">';
        $output .= '<a href="javascript:void(0);" class="hover-tooltip tooltip-left box-icon wishlist-btn"'
            . ' data-product-id="' . esc_attr($related_product->get_id()) . '">';
        $output .= '<span class="icon icon-heart"></span>';
        $output .= '<span class="tooltip">' . esc_html__('Add to Wishlist', 'textdomain') . '</span>';
        $output .= '</a>';
        $output .= '</li>';

        $output .= '</ul>';

        // Sale badge
        if ($related_product->is_on_sale()) {
            $regular_price = (float) $related_product->get_regular_price();
            $sale_price = (float) $related_product->get_sale_price();
            $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);

            $output .= '<ul class="product-badge_list">';
            $output .= '<li class="product-badge_item h6 sale">' . $discount_percentage . '% OFF</li>';
            $output .= '</ul>';

            // Countdown timer
            $sale_end_timestamp = get_post_meta($related_post->ID, '_sale_price_dates_to', true);
            if ($sale_end_timestamp) {
                $now = current_time('timestamp');
                $seconds_remaining = $sale_end_timestamp - $now;
                if ($seconds_remaining > 0) {
                    $output .= '<div class="product-countdown">';
                    $output .= '<div class="js-countdown cd-has-zero" data-timer="' . $seconds_remaining . '" data-labels="d : ,h : ,m : ,s"></div>';
                    $output .= '</div>';
                }
            }
        }

        $output .= '</div>';

        // Product info
        $output .= '<div class="card-product_info">';
        $output .= '<a href="' . get_permalink($related_post->ID) . '" class="name-product h4 link">' . $related_product->get_name() . '</a>';

        // Price
        $output .= '<div class="price-wrap">';
        if ($related_product->is_on_sale()) {
            $output .= '<span class="price-old h6 fw-normal">' . wc_price($related_product->get_regular_price()) . '</span>';
            $output .= '<span class="price-new h6">' . wc_price($related_product->get_sale_price()) . '</span>';
        } else {
            $output .= '<span class="price-new h6">' . $related_product->get_price_html() . '</span>';
        }
        $output .= '</div>';

        // Color variations
        $color_attribute = $related_product->get_attribute('pa_color');
        if ($color_attribute) {
            $colors = explode(', ', $color_attribute);
            $output .= '<ul class="product-color_list">';

            foreach ($colors as $index => $color) {
                $active_class = $index === 0 ? ' active' : '';
                $output .= '<li class="product-color-item color-swatch hover-tooltip tooltip-bot' . $active_class . '">';
                $output .= '<span class="tooltip color-filter">' . esc_html($color) . '</span>';
                $output .= '<span class="swatch-value" style="background-color: ' . esc_attr(strtolower($color)) . '"></span>';
                $output .= $related_product->get_image('woocommerce_thumbnail');
                $output .= '</li>';
            }

            $output .= '</ul>';
        }

        $output .= '</div>';
        $output .= '</div>';
        $output .= '</div>';
    }

    $output .= '</div>';
    $output .= '<div class="sw-dot-default tf-sw-pagination"></div>';
    $output .= '</div>';
    $output .= '</div>';
    $output .= '</section>';

    return $output;
}


?>

<div class="woocommerce-notices-wrapper">
    <?php wc_print_notices(); ?>
</div>
<?php
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>
<section class="flat-single-product flat-spacing-3">
    <div class="tf-main-product section-image-zoom">
        <div class="container">
            <div class="row">
                <!-- Product Images -->
                <div class="col-md-6">
                    <div class="tf-product-media-wrap sticky-top">
                        <div class="product-thumbs-slider">
                            <!-- Thumbnails -->
                            <div dir="ltr" class="swiper tf-product-media-thumbs other-image-zoom"
                                data-direction="vertical" data-preview="4.7">
                                <div class="swiper-wrapper stagger-wrap">
                                    <?php
                                    $attachment_ids = get_product_images($product);

                                    if ($attachment_ids) {
                                        foreach ($attachment_ids as $attachment_id) {
                                            $image_url = wp_get_attachment_image_url($attachment_id, 'woocommerce_single');
                                            ?>
                                            <div class="swiper-slide stagger-item">
                                                <div class="item">
                                                    <img class="lazyload" data-src="<?php echo esc_url($image_url); ?>"
                                                        src="<?php echo esc_url($image_url); ?>"
                                                        alt="<?php echo esc_attr($product->get_name()); ?>">
                                                </div>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Main Images -->
                            <div class="flat-wrap-media-product">
                                <div dir="ltr" class="swiper tf-product-media-main swiper-initialized swiper-horizontal"
                                    id="gallery-swiper-started">
                                    <div class="swiper-wrapper">
                                        <?php
                                        if ($attachment_ids) {
                                            foreach ($attachment_ids as $attachment_id) {
                                                $image_url = wp_get_attachment_image_url($attachment_id, 'woocommerce_single');
                                                $full_image_url = wp_get_attachment_image_url($attachment_id, 'full');

                                                // $hover_image = wp_get_attachment_image_src($attachment_id, 'medium');
                                                ?>
                                                <div class="swiper-slide">

                                                    <a href="<?php echo esc_url($full_image_url); ?>" target="_blank"
                                                        class="item">
                                                        <img class="tf-image-zoom img-product ls-is-cached lazyload"
                                                            data-zoom="<?php echo esc_url($full_image_url); ?>"
                                                            data-src="<?php echo esc_url($image_url); ?>"
                                                            src="<?php echo esc_url($image_url); ?>"
                                                            alt="<?php echo esc_attr($product->get_name()); ?>">
                                                    </a>

                                                </div>
                                                <?php
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-md-6">
                    <div class="tf-product-info-wrap position-relative">
                        <div class="tf-zoom-main sticky-top"></div>
                        <div class="tf-product-info-list other-image-zoom">
                            <!-- Product Title -->
                            <h2 class="product-info-name"><?php echo $product->get_name(); ?></h2>

                            <!-- Product Meta -->
                            <div class="product-info-meta">
                                <!-- Rating -->
                                <?php echo display_product_rating($product); ?>

                                <!-- Stock Status -->
                                <?php if ($product->is_in_stock()): ?>
                                    <div class="people-add text-primary">
                                        <i class="icon icon-shopping-cart-simple"></i>
                                        <span class="h6"><?= $lango == 'ar' ? 'متاح' : 'In Stock' ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Price -->
                            
    
                            <!-- Sale Countdown -->
                            <?php //echo $product->get_regular_price(); ?>
                        </div>


                        <!-- Live View Counter -->
                        <div class="tf-product-info-liveview d-none">
                            <div class="liveview-count">
                                <i class="icon icon-view"></i>
                                <span class="count fw-6 h6" id="live-viewer-count">0</span>
                            </div>
                            <p class="fw-6 h6">
                                <?= $lango == 'ar' ? 'عدد المشاهدين الآن' : 'People are viewing this right now' ?>
                            </p>
                        </div>

                        <div class="tf-product-variant mt-4">

                            <!-- echo variations -->
                            <?php

                            $variations = $product->get_available_variations();
                            foreach ($variations as $variation) {
                                $variation_id   = $variation['variation_id'];
                                $display_price  = $variation['display_price']; // السعر الحالي
                                $regular_price  = $variation['display_regular_price']; // السعر قبل الخصم
                                $attributes     = $variation['attributes']; // القيم المختارة زي اللون/المقاس

                                echo $display_price;
                            }

                            ?>

                            <div class="js-variation-price"></div>


                            <?php
                            /**
                             * Hook: woocommerce_single_product_summary.
                             *
                             * @hooked woocommerce_template_single_title - 5
                             * @hooked woocommerce_template_single_rating - 10
                             * @hooked woocommerce_template_single_price - 10
                             * @hooked woocommerce_template_single_excerpt - 20
                             * @hooked woocommerce_template_single_add_to_cart - 30
                             * @hooked woocommerce_template_single_meta - 40
                             * @hooked woocommerce_template_single_sharing - 50
                             * @hooked WC_Structured_Data::generate_product_data() - 60
                             */
                            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
                            remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
                            // remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
                            // remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50);
                            do_action('woocommerce_single_product_summary');
                            ?>
                            
                        </div>
                    

                        <!-- Extra Links -->
                        <div class="tf-product-extra-link">
                            <a href="#" class="product-extra-icon link" data-bs-toggle="modal"
                                data-bs-target="#shipAndDelivery">
                                <i class="icon icon-truck"></i><?php echo $lango == 'ar' ? 'الشحن' : 'Delivery'; ?>
                            </a>
                            <a href="#" class="btn-share product-extra-icon link share-product"
                                onclick="shareProduct(event)">
                                <i class="icon icon-share"></i><?php echo $lango == 'ar' ? 'مشاركة' : 'Share'; ?>
                            </a>
                        </div>

                        <!-- Delivery Information -->
                        <div class="tf-product-delivery-return">
                            <div class="product-delivery">
                                <div class="icon icon-clock-cd"></div>
                                <?php $notes = get_field('delivery_notes'); ?>
                                <?php if (empty($notes)): ?>
                                    <h6><?php echo $lango == 'ar' ? 'حقول فارغة' : 'Empty Field'; ?></h6>
                                <?php else: ?>
                                    <p class="h6"><?php echo esc_html($notes); ?></p>
                                <?php endif; ?>

                            </div>
                            <div class="product-delivery return">
                                <div class="icon icon-compare"></div>
                                <p class="h6"><?php echo $lango == 'ar' ? 'العودة' : 'Return'; ?> <span class="fw-7 text-black"><?php echo $lango == 'ar' ? '30 يوما' : '30 days'; ?></span> <?php echo $lango == 'ar' ? 'الشراء' : 'of purchase'; ?>.
                                    <?php echo $lango == 'ar' ? 'الضريبة' : 'Duties & taxes'; ?> <?php echo $lango == 'ar' ? 'لا ترد' : 'are non-refundable'; ?>.</p>
                            </div>
                        </div>

                        <!-- Trust Seals -->
                        <div class="tf-product-trust-seal">
                            <p class="h6 text-seal"><?php echo $lango == 'ar' ? 'ضمان الدفع الآمن:' : 'Guarantee Safe Checkout:'; ?></p>
                            <ul class="list-card">
                                <li class="card-item">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/payment/visa.png"
                                        alt="Visa">
                                </li>
                                <li class="card-item">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/payment/master-card.png"
                                        alt="MasterCard">
                                </li>
                                <li class="card-item">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/payment/amex.png"
                                        alt="American Express">
                                </li>
                                <li class="card-item">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/payment/discover.png"
                                        alt="Discover">
                                </li>
                                <li class="card-item">
                                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/payment/paypal.png"
                                        alt="PayPal">
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- Product Description -->
    <?php
    $product = wc_get_product(get_the_ID());
    $attributes = $product->get_attributes(); // Array of WC_Product_Attribute objects
    
    //if ($attributes): 
    ?>
    <section class="flat-spacing-3">
        <div class="container">
            <div class="flat-animate-tab tab-style-1">
                <ul class="menu-tab menu-tab-1" role="tablist">
                    <li class="nav-tab-item" role="presentation">
                        <a href="#descriptions" class="tab-link active" data-bs-toggle="tab"><?php echo $lango == 'ar' ? 'وصف المنتج' : 'Product Description'; ?></a>
                    </li>
                    <li class="nav-tab-item" role="presentation">
                        <a href="#policy" class="tab-link" data-bs-toggle="tab"><?php echo $lango == 'ar' ? 'سياسة الشحن والعودة والإرجاع' : 'Shipping, Return & Refund Policy'; ?></a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div class="tab-pane wd-product-descriptions active show" id="descriptions" role="tabpanel">
                        <div class="tab-descriptions mb-5">
                            <?php
                            echo $product->get_description();
                            ?>

                            <div class="list-infor tf-grid-layout d-none">
                                <?php foreach ($attributes as $attribute):

                                    // اسم الأتريبيوت (مثلاً Size أو Color)
                                    $label = wc_attribute_label($attribute->get_name());

                                    // نجيب القيم: لو taxonomy أو قيم عادية
                                    if ($attribute->is_taxonomy()) {
                                        $options = wc_get_product_terms($product->get_id(), $attribute->get_name(), ['fields' => 'names']);
                                    } else {
                                        $options = $attribute->get_options();
                                    }
                                    ?>
                                    <div class="infor-item">
                                        <div class="h4 heading"><?php echo esc_html($label); ?></div>
                                        <ul>
                                            <?php foreach ($options as $opt): ?>
                                                <li>
                                                    <h6 class="fw-6 text-black title"><?php echo esc_html($opt); ?>:</h6>
                                                    <div class="h6"><?php echo esc_html($opt); ?></div>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr class="table-primary">
                                    <th scope="col" class="fw-6"><?= $lango == 'ar' ? 'خصائص المنتج' : 'Products Attributes' ?></th>
                                    <th scope="col" class="fw-6"><?= $lango == 'ar' ? 'القيمة' : 'Value' ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (have_rows('product_size2', $product->get_id())):
                                    while (have_rows('product_size2', $product->get_id())):
                                        the_row();
                                        $size_name = get_sub_field('size_name');
                                        $size_code = get_sub_field('size_code');
                                        ?>


                                        <tr>
                                            <th scope="row"><?php echo $size_name; ?></th>
                                            <td><?php echo $size_code; ?></td>
                                        </tr>


                                    <?php endwhile; endif;
                                ?>
                                <?php
                                $color_index = 0;
                                if (have_rows('product_attributes3', $product->get_id())):
                                    while (have_rows('product_attributes3', $product->get_id())):
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
                                        <tr>
                                            <th scope="row"><?php echo $color_name; ?></th>
                                            <td>
                                                <p
                                                    style="background-color: <?php echo esc_attr($color_code); ?>; color: <?php echo esc_attr($color_code); ?>;width: 30px;height:30px;border-radius: 50%; ">
                                                </p>
                                            </td>
                                        </tr>
                                        <?php
                                        $color_index++;
                                    endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>

                    </div>

                    <div class="tab-pane wd-product-descriptions" id="policy" role="tabpanel">
                        <div class="tab-policy">
                            <?php
                            // لو ضفت ACF حقل نصي باسم shipping_policy
                            if (get_field('shipping_policy', get_the_ID())) {
                                echo wpautop(get_field('shipping_policy', get_the_ID()));
                            } else {
                                // محتوى افتراضي
                                echo '<p class="h6">لا توجد سياسة مضافة حالياً.</p>';
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <?php //endif; ?>

    <!-- /Product Description -->

    <!-- Box Icon -->
    <section class="mt-5">
        <div class="container">
            <div class="sect-border">
                <div class="s-head">
                    <h3 class=" s-title fw-normal"><?= $lango == 'ar' ? 'الملابس الحية' : 'The Fresh Clothes' ?></h3>
                </div>
                <div dir="ltr" class="swiper tf-swiper" data-preview="4" data-tablet="3" data-mobile-sm="2"
                    data-mobile="1" data-space-lg="97" data-space-md="33" data-space="13" data-pagination="1"
                    data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="4">
                    <div class="swiper-wrapper">
                        <!-- item 1 -->
                        <div class="swiper-slide">
                            <div class="box-icon_V01">
                                <span class="icon">
                                    <i class="icon-package"></i>
                                </span>
                                <div class="content">
                                    <h4 class="title fw-normal"><?= $lango == 'ar' ? '30 يوماً للعودة' : '30 days return' ?></h4>
                                    <p class="text"><?= $lango == 'ar' ? 'ضمان استرداد المال' : '30 day money back guarantee' ?></p>
                                </div>
                            </div>
                        </div>
                        <!-- item 2 -->
                        <div class="swiper-slide">

                            <div class="box-icon_V01">
                                <span class="icon">
                                    <i class="icon-calender"></i>
                                </span>
                                <div class="content">
                                    <h4 class="title fw-normal"><?= $lango == 'ar' ? 'ضمان 3 سنوات' : '3 year warranty' ?></h4>
                                    <p class="text"><?= $lango == 'ar' ? 'ضمان العيوب المصنع' : 'Manufacturers defect' ?></p>
                                </div>
                            </div>
                        </div>
                        <!-- item 3 -->
                        <div class="swiper-slide">

                            <div class="box-icon_V01">
                                <span class="icon">
                                    <i class="icon-boat"></i>
                                </span>
                                <div class="content">
                                    <h4 class="title fw-normal"><?= $lango == 'ar' ? 'شحن مجاني' : 'Free shipping' ?></h4>
                                    <p class="text"><?= $lango == 'ar' ? 'شحن مجاني' : 'Free Shipping for orders over $150' ?></p>
                                </div>
                            </div>
                        </div>
                        <!-- item 4 -->
                        <div class="swiper-slide">
                            <div class="box-icon_V01">
                                <span class="icon">
                                    <i class="icon-headset"></i>
                                </span>
                                <div class="content">
                                    <h4 class="title fw-normal"><?= $lango == 'ar' ? 'دعم اونلاين' : 'Online support' ?></h4>
                                    <p class="text"><?= $lango == 'ar' ? '24 ساعة في اليوم، 7 أيام في الأسبوع' : '24 hours a day, 7 days a week' ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sw-dot-default tf-sw-pagination"></div>
                </div>
            </div>
        </div>
    </section>
    <!-- /Box Icon -->

    <!-- Related Products -->
    <?php echo display_related_products($product); ?>

    <!-- Sticky Add to Cart -->
    <div class="tf-sticky-btn-atc">
        <div class="container">
            <div class="tf-height-observer w-100 d-flex align-items-center">
                <div class="tf-sticky-atc-product d-flex align-items-center">
                    <div class="tf-mini-cart-item align-items-start">
                        <div class="tf-mini-cart-image">
                            <?php echo $product->get_image('thumbnail'); ?>
                        </div>
                        <div class="tf-mini-cart-info">
                            <h6 class="title">
                                <a href="<?php echo $product->get_permalink(); ?>" class="link text-line-clamp-1">
                                    <?php echo $product->get_name(); ?>
                                </a>
                            </h6>

                            <div class="h6 fw-semibold">
                                <?php echo $product->get_price_html(); ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tf-sticky-atc-infos">
                    <form class="sticky-cart-form">

                        <div class="tf-sticky-atc-btns">


                            <a href="?add-to-cart=<?php echo $product->get_id(); ?>" class="btn btn-dark add_to_cart_button ajax_add_to_cart product_type_simple
box-icon hover-tooltip tooltip-left" data-quantity="1" data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                                data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Add “%s” to your cart', 'woocommerce'), $product->get_name())); ?>">
                                <span class="icon icon-shopping-cart-simple me-2"></span>
                                <span class="tooltip"><?php _e('Add to cart', 'textdomain'); ?></span>
                                <?php echo $lango == 'ar' ? 'إضافة إلى السلة' : 'Add to cart'; ?>
                            </a>


                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
</div>

<script>
    function shareProduct(event) {
        event.preventDefault();

        if (navigator.share) {
            navigator.share({
                title: document.title,
                text: 'Check out this product!',
                url: window.location.href,
            }).then(() => {
                console.log('Shared successfully');
            }).catch((err) => {
                console.log('Error sharing:', err);
            });
        } else {
            alert("المتصفح لا يدعم ميزة المشاركة المباشرة");
        }
    }
</script>

<?php do_action('woocommerce_after_single_product'); ?>
<?php
get_footer();
?>