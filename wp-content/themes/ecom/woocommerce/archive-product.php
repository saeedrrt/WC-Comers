<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

get_header();

do_action('woocommerce_before_main_content');

// // معلومات البحث
$search_query = get_search_query();
$selected_category = isset($_GET['product_cat']) ? sanitize_text_field($_GET['product_cat']) : '';
$min_price = isset($_GET['min_price']) ? intval($_GET['min_price']) : '';
$max_price = isset($_GET['max_price']) ? intval($_GET['max_price']) : '';
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'relevance';

// إحصائيات البحث
global $wp_query;
$total_results = $wp_query->found_posts;
$current_page = max(1, get_query_var('paged'));
$per_page = get_option('posts_per_page');
$showing_start = (($current_page - 1) * $per_page) + 1;
$showing_end = min($current_page * $per_page, $total_results);

?>


<?php
// حدّد إحنا فين: صفحة الشوب ولا أرشيف تصنيف
$display_terms = array();

if (function_exists('is_shop') && is_shop()) {
    // صفحة الشوب => هات التصنيفات الرئيسية فقط
    $display_terms = get_terms(array(
        'taxonomy' => 'product_cat',
        'parent' => 0,
        'hide_empty' => true,
        'orderby' => 'menu_order',
        'order' => 'ASC',
    ));

} elseif (is_tax('product_cat')) {
    // صفحة أرشيف تصنيف
    $current_term = get_queried_object();

    // هات أولاد التصنيف الحالي
    $children = get_terms(array(
        'taxonomy' => 'product_cat',
        'parent' => $current_term->term_id,
        'hide_empty' => true,
        'orderby' => 'menu_order',
        'order' => 'ASC',
    ));

    if (!is_wp_error($children) && !empty($children)) {
        // لو فيه أولاد => اعرض الأولاد
        $display_terms = $children;
    } else {
        // لو مفيش أولاد => اعرض نفس التصنيف الحالي
        $display_terms = array($current_term);
    }

} else {
    // في أي صفحة تانية (احتياطي): هات الرئيسية
    $display_terms = get_terms(array(
        'taxonomy' => 'product_cat',
        'parent' => 0,
        'hide_empty' => true,
        'orderby' => 'menu_order',
        'order' => 'ASC',
    ));
}

$lango = pll_current_language();
?>

<div class="flat-spacing pb-0">
    <div class="container">
        <div dir="ltr" class="swiper tf-swiper" data-preview="5" data-tablet="4" data-mobile-sm="3" data-mobile="2"
            data-space-lg="40" data-space-md="24" data-space="12" data-pagination="2" data-pagination-sm="3"
            data-pagination-md="4" data-pagination-lg="5">

            <div class="swiper-wrapper">
                <?php if (!empty($display_terms) && !is_wp_error($display_terms)): ?>
                    <?php foreach ($display_terms as $category):
                        // صورة التصنيف (لو مفيش نحط افتراضية)
                        $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                        $image_url = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : get_template_directory_uri() . '/images/category/default.jpg';
                        ?>
                        <div class="swiper-slide">
                            <div class="box-image_category style-2 hover-img">
                                <a href="<?php echo esc_url(get_term_link($category)); ?>" class="box-image_image img-style">
                                    <img class="lazyload" src="<?php echo esc_url($image_url); ?>"
                                        data-src="<?php echo esc_url($image_url); ?>"
                                        alt="<?php echo esc_attr($category->name); ?>">
                                </a>
                                <div class="box-image_content">
                                    <a href="<?php echo esc_url(get_term_link($category)); ?>"
                                        class="tf-btn btn-white animate-btn animate-dark">
                                        <span class="h5 fw-medium"><?php echo esc_html($category->name); ?></span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="sw-dot-default tf-sw-pagination"></div>
        </div>
    </div>
</div>

<!-- /Category -->
<div class="flat-spacing-3 pb-0">
    <div class="container">
        <div class="row">
            <div class="col-xl-3">
                <div class="canvas-sidebar sidebar-filter canvas-filter left">
                    <div class="canvas-wrapper">
                        <div class="canvas-header d-xl-none">
                            <span class="title h3 fw-medium"><?php echo $lango == 'ar' ? 'تصفية' : 'Filter'; ?></span>
                            <span class="icon-close link icon-close-popup fs-24 close-filter"></span>
                        </div>
                        <div class="canvas-body">

                            <div id="category" class="collapse show">
    <ul class="collapse-body filter-group-check group-category">
        <?php
                                    // جلب جميع التصنيفات التي لها منتجات
                                    $categories = get_terms(array(
                                        'taxonomy' => 'product_cat',
                                        'hide_empty' => true,
                                    ));

                                    // الحصول على التصنيف الحالي بطريقة محسنة
                                    $current_cat_slug = get_current_category_slug();

                                    if (!empty($categories) && !is_wp_error($categories)): ?>
                                        <ul class="filter-list">
                                            <?php foreach ($categories as $cat):
                                                // حساب عدد المنتجات في التصنيف
                                                $count_query = new WP_Query(array(
                                                    'post_type' => 'product',
                                                    'post_status' => 'publish',
                                                    'posts_per_page' => -1,
                                                    'fields' => 'ids',
                                                    'tax_query' => array(
                                                        array(
                                                            'taxonomy' => 'product_cat',
                                                            'field' => 'slug',
                                                            'terms' => $cat->slug,
                                                        ),
                                                    ),
                                                ));
                                                $real_count = $count_query->found_posts;

                                                // إضافة كلاس active لو هو التصنيف الحالي
                                                $active_class = ($cat->slug === $current_cat_slug) ? 'active' : '';
                                                ?>
                                                <li class="list-item <?php echo esc_attr($active_class); ?>">
                                                    <a href="#" class="link h6 filter-cat <?php echo esc_attr($active_class); ?>"
                                                        data-cat="<?php echo esc_attr($cat->slug); ?>" data-cat-name="<?php echo esc_attr($cat->name); ?>"
                                                        data-cat-id="<?php echo esc_attr($cat->term_id); ?>">
                                                        <?php echo esc_html($cat->name); ?>
                                                        <span class="count33"><?php echo $real_count; ?></span>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            
                            <?php
                            // دالة محسنة للحصول على slug التصنيف الحالي
                            function get_current_category_slug()
                            {
                                // أولاً: تحقق من أرشيف التصنيف العادي
                                if (is_product_category()) {
                                    $category = get_queried_object();
                                    if ($category && isset($category->slug)) {
                                        return $category->slug;
                                    }
                                }

                                // ثانياً: تحقق من taxonomy عام
                                if (is_tax('product_cat')) {
                                    $current_term = get_queried_object();
                                    if ($current_term && isset($current_term->slug)) {
                                        return $current_term->slug;
                                    }
                                }

                                // ثالثاً: استخراج من الـ URL الحالي
                                $request_uri = $_SERVER['REQUEST_URI'];

                                // نمط للروابط العربية والإنجليزية
                                $patterns = [
                                    '/\/product-category\/([^\/\?]+)/',  // الحالة العادية
                                    '/\/ar\/product-category\/([^\/\?]+)/', // اللغة العربية
                                    '/\/en\/product-category\/([^\/\?]+)/', // اللغة الإنجليزية
                                    '/category\/([^\/\?]+)/' // حالات أخرى محتملة
                                ];

                                foreach ($patterns as $pattern) {
                                    if (preg_match($pattern, $request_uri, $matches)) {
                                        $category_identifier = urldecode($matches[1]);

                                        // حاول إيجاد التصنيف بالـ slug
                                        $term = get_term_by('slug', $category_identifier, 'product_cat');
                                        if ($term && !is_wp_error($term)) {
                                            return $term->slug;
                                        }

                                        // حاول إيجاد التصنيف بالاسم (للأسماء العربية)
                                        $term_by_name = get_term_by('name', $category_identifier, 'product_cat');
                                        if ($term_by_name && !is_wp_error($term_by_name)) {
                                            return $term_by_name->slug;
                                        }

                                        // إذا كنت تستخدم plugin للترجمة
                                        if (function_exists('pll_get_term') || function_exists('icl_get_languages')) {
                                            // محاولة البحث في اللغات المختلفة
                                            $all_cats = get_terms(array(
                                                'taxonomy' => 'product_cat',
                                                'hide_empty' => false,
                                            ));

                                            foreach ($all_cats as $cat) {
                                                // مقارنة بالاسم أو الـ slug
                                                if (
                                                    $cat->name === $category_identifier ||
                                                    $cat->slug === $category_identifier ||
                                                    urlencode($cat->name) === $category_identifier
                                                ) {
                                                    return $cat->slug;
                                                }
                                            }
                                        }
                                    }
                                }

                                // رابعاً: تحقق من query vars
                                $queried_cat = get_query_var('product_cat');
                                if ($queried_cat) {
                                    return $queried_cat;
                                }

                                return '';
                            }
                            ?>


                            <div class="widget-facet mt-4">
                                <div class="facet-title" data-bs-target="#brands" role="button"
                                    data-bs-toggle="collapse" aria-expanded="true" aria-controls="brands">
                                    <span class="h4 fw-semibold"><?php echo $lango == 'ar' ? 'العلامات التجارية' : 'Brands'; ?></span>
                                    <span class="icon icon-caret-down fs-20"></span>
                                </div>

                                <div id="brands" class="collapse show">
                                    <ul class="collapse-body filter-group-check group-category">

                                        <?php
                                        // جلب العلامات التجارية (taxonomy) التي لها منتجات     
                                        if (taxonomy_exists('product_brand')):
                                            // جلب العلامات التجارية
                                            $brands = get_terms(array(
                                                'taxonomy' => 'product_brand',
                                                'hide_empty' => false,
                                            ));
                                            ?>
                                            <ul class="filter-list">
                                                <?php
                                                $brands = get_terms(['taxonomy' => 'product_brand', 'hide_empty' => true]);

                                                foreach ($brands as $b):
                                                    $count_query3 = new WP_Query(array(
                                                        'post_type' => 'product',
                                                        'post_status' => 'publish',
                                                        'posts_per_page' => -1,
                                                        'fields' => 'ids',
                                                        'tax_query' => array(
                                                            array(
                                                                'taxonomy' => 'product_brand',
                                                                'field' => 'slug',
                                                                'terms' => $b->slug,
                                                            ),
                                                        ),
                                                    ));
                                                    $real_count2 = $count_query3->found_posts;
                                                    ?>
                                                    <li class="list-item">
                                                        <a href="#" class="link h6 filter-bar"
                                                            data-bar="<?php echo esc_attr($b->slug); ?>">
                                                            <?php echo esc_html($b->name); ?>
                                                            <span class="count33"><?= $real_count2; ?></span>
                                                        </a>
                                                    </li>

                                                <?php endforeach; ?>

                                            </ul>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>

                            <div class="widget-facet">
                                <div class="facet-title" data-bs-target="#price" role="button" data-bs-toggle="collapse"
                                    aria-expanded="true" aria-controls="price">
                                    <span class="h4 fw-semibold"><?php echo $lango == 'ar' ? 'السعر' : 'Price'; ?></span>
                                    <span class="icon icon-caret-down fs-20"></span>
                                </div>

                                <div id="price" class="collapse show">
                                    <div class="collapse-body widget-price filter-price">

                                        <div class="price-val-range noUi-target noUi-ltr noUi-horizontal"
                                            id="price-slider" data-min="0" data-max="500">
                                            <div class="noUi-base">
                                                <div class="noUi-connects">
                                                    <div class="noUi-connect"
                                                        style="transform: translate(0%, 0px) scale(1, 1);"></div>
                                                </div>
                                                <div class="noUi-origin"
                                                    style="transform: translate(-100%, 0px); z-index: 5;">
                                                    <div class="noUi-handle noUi-handle-lower" data-handle="0"
                                                        tabindex="0" role="slider" aria-orientation="horizontal"
                                                        aria-valuemin="0.0" aria-valuemax="500.0" aria-valuenow="0.0"
                                                        aria-valuetext="0"></div>
                                                </div>
                                                <div class="noUi-origin"
                                                    style="transform: translate(0%, 0px); z-index: 4;">
                                                    <div class="noUi-handle noUi-handle-upper" data-handle="1"
                                                        tabindex="0" role="slider" aria-orientation="horizontal"
                                                        aria-valuemin="0.0" aria-valuemax="500.0" aria-valuenow="500.0"
                                                        aria-valuetext="500"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="box-value-price">
                                            <span class="h6 text-main"><?php echo $lango == 'ar' ? 'السعر' : 'Price'; ?></span>

                                            <?php
                                            function get_min_max_product_price_raw()
                                            {
                                                global $wpdb;
                                                $min = $wpdb->get_var("
        SELECT MIN( CAST(meta_value AS DECIMAL(10,2)) )
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_price'
    ");
                                                $max = $wpdb->get_var("
        SELECT MAX( CAST(meta_value AS DECIMAL(10,2)) )
        FROM {$wpdb->postmeta}
        WHERE meta_key = '_price'
    ");
                                                return array(
                                                    'min' => $min,
                                                    'max' => $max,
                                                );
                                            }

                                            // في القالب أو الهيدر أو أي مكان:
                                            $prices = get_min_max_product_price_raw();

                                            ?>
                                            <div class="price-box">
                                                <div class="price-val" id="min-price" data-currency="">
                                                    SAR<?= number_format((float) $prices['min'], 2) ?></div>
                                                <span>-</span>
                                                <div class="price-val" id="max-price" data-currency="">
                                                    SAR<?= number_format((float) $prices['max'], 2) ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="widget-facet d-none">
                                <div class="facet-title" data-bs-target="#availability" role="button"
                                    data-bs-toggle="collapse" aria-expanded="true" aria-controls="availability">
                                    <span class="h4 fw-semibold"><?php echo $lango == 'ar' ? 'التوفر' : 'Availability'; ?></span>
                                    <span class="icon icon-caret-down fs-20"></span>
                                </div>

                                <!-- فلتر التوفر -->
                                <div id="availability" class="collapse show">
                                    <ul class="collapse-body filter-group-check current-scrollbar">
                                        <li class="list-item">
                                            <input type="radio" name="availability" class="tf-check" id="inStock"
                                                value="inStock">
                                            <label for="inStock" class="label">
                                                <span><?php echo $lango == 'ar' ? 'التوفر' : 'Availability'; ?></span>
                                                <span class="count"></span>
                                            </label>
                                        </li>
                                        <li class="list-item">
                                            <input type="radio" name="availability" class="tf-check" id="outStock"
                                                value="outStock">
                                            <label for="outStock" class="label">
                                                <span><?php echo $lango == 'ar' ? 'غير المتاح' : 'Out of Stock'; ?></span>
                                                <span class="count"></span>
                                            </label>
                                        </li>

                                    </ul>
                                </div>
                            </div>

                            <div class="widget-facet d-none">
                                <div class="facet-title" data-bs-target="#size" role="button" data-bs-toggle="collapse"
                                    aria-expanded="true" aria-controls="size">
                                    <span class="h4 fw-semibold"><?php echo $lango == 'ar' ? 'الحجم' : 'Size'; ?></span>
                                    <span class="icon icon-caret-down fs-20"></span>
                                </div>
                                <div id="size" class="collapse show">
                                    <div class="collapse-body filter-size-box flat-check-list">
                                        <?php
                                        if (have_rows('product_size2')):
                                            while (have_rows('product_size2')):
                                                the_row();
                                                ?>
                                                <div class="check-item size-item size-check">
                                                    <span class="size h6"><?php echo get_sub_field('size_name'); ?></span>
                                                </div>
                                                <?php
                                            endwhile;
                                        endif;

                                        // طريقة مخصصة - للأحجام
                                        $sizes = get_unique_product_attributes(
                                            'product_size2',
                                            array('size_name', 'size_code'),
                                            'size_name'
                                        );

                                        foreach ($sizes as $seer):
                                            ?>

                                            <div class="check-item size-item size-check">
                                                <span class="size h6"><?php echo $seer['size_name']; ?></span>
                                            </div>

                                        <?php endforeach; ?>

                                    </div>
                                </div>
                            </div>

                            <div class="widget-facet">
                                <div class="facet-title" data-bs-target="#color" role="button" data-bs-toggle="collapse"
                                    aria-expanded="true" aria-controls="size">
                                    <span class="h4 fw-semibold"><?php echo $lango == 'ar' ? 'اللون' : 'Color'; ?></span>
                                    <span class="icon icon-caret-down fs-20"></span>
                                </div>
                                <div id="color" class="collapse show">
                                    <div class="collapse-body filter-color-box flat-check-list">

                                        <?php
                                        $unique_colors = get_unique_product_colors();
                                        foreach ($unique_colors as $color):

                                            ?>
                                            <div class="check-item color-filter-item color-item color-check"
                                                data-color="<?php echo esc_attr(sanitize_title($color['name'])); ?>"
                                                data-color-name="<?php echo esc_attr($color['name']); ?>"
                                                data-product-ids="<?php echo esc_attr(implode(',', $color['product_ids'])); ?>">
                                                <span class="color" style="background-color: <?= $color['code']; ?>"></span>
                                                <span
                                                    class="color-text"><?= $color['name']; ?>[<?php echo $wp_query->found_posts; ?>]</span>
                                            </div>

                                        <?php endforeach; ?>

                                    </div>
                                </div>
                            </div>

                            <?php
                            echo do_shortcode('[banners]');
                            ?>

                        </div>
                        <div class="canvas-bottom d-xl-none">
                            <button id="reset-filter" class="tf-btn btn-reset"><?php echo $lango == 'ar' ? 'إعادة التصفية' : 'Reset Filters'; ?></button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9">
                <div class="tf-shop-control">
                    <div class="shop-sale-text d-none d-xl-flex">
                        <input type="checkbox" name="sale" class="tf-check" id="sale">
                        <label for="sale" class="label"><?php echo $lango == 'ar' ? 'إظهار المنتجات فقط في الخصم' : 'Show only products on sale'; ?></label>
                    </div>
                    <div class="tf-control-filter d-xl-none">
                        <button type="button" id="filterShop" class="tf-btn-filter">
                            <span class="icon icon-filter"></span><span class="text"><?php echo $lango == 'ar' ? 'تصفية' : 'Filter'; ?></span>
                        </button>
                    </div>

                    <?php

                    $orderby = isset($_GET['orderby'])
                        ? sanitize_text_field($_GET['orderby'])
                        : 'best-selling';


                    $sort_options = [
                        'best-selling' => $lango == 'ar' ? 'الأكثر شعبية' : 'Best Selling',
                        'date' => $lango == 'ar' ? 'الأحدث' : 'Newest',
                        'a-z' => $lango == 'ar' ? 'أبجدية، A-Z' : 'Alphabetically, A-Z',
                        'z-a' => $lango == 'ar' ? 'أبجدية، Z-A' : 'Alphabetically, Z-A',
                        'price-low-high' => $lango == 'ar' ? 'السعر، من الأدنى إلى الأعلى' : 'Price, low to high',
                        'price-high-low' => $lango == 'ar' ? 'السعر، من الأعلى إلى الأدنى' : 'Price, high to low',
                    ];


                    $args = [
                        'post_type' => 'product',
                        'posts_per_page' => 12,
                    ];


                    switch ($orderby) {
                        case 'date':
                            $args['orderby'] = 'date';
                            $args['order'] = 'DESC';
                            break;

                        case 'a-z':
                            $args['orderby'] = 'title';
                            $args['order'] = 'ASC';
                            break;

                        case 'z-a':
                            $args['orderby'] = 'title';
                            $args['order'] = 'DESC';
                            break;

                        case 'price-low-high':
                            $args['orderby'] = 'meta_value_num';
                            $args['meta_key'] = '_price';
                            $args['order'] = 'ASC';
                            break;

                        case 'price-high-low':
                            $args['orderby'] = 'meta_value_num';
                            $args['meta_key'] = '_price';
                            $args['order'] = 'DESC';
                            break;

                        case 'best-selling':
                        default:

                            $args['orderby'] = 'meta_value_num';
                            $args['meta_key'] = 'total_sales';
                            $args['order'] = 'DESC';
                            break;
                    }

                    $loop = new WP_Query($args);
                    ?>


                    <div class="tf-dropdown-sort dropdown">
                        <div class="btn-select" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="text-sort-value">
                                <?php echo esc_html($sort_options[$orderby]); ?>
                            </span>
                            <span class="icon icon-caret-down"></span>
                        </div>
                        <div class="dropdown-menu">
                            <?php foreach ($sort_options as $key => $label): ?>
                                <?php
                                // نحتفظ بكل الـ GET الحالية ونغيّر فقط orderby
                                $url = add_query_arg('orderby', $key);
                                $active = ($orderby === $key) ? ' active' : '';
                                ?>
                                <a href="<?php echo esc_url($url); ?>"
                                    class="dropdown-item select-item<?php echo $active; ?>">
                                    <span class="text-value-item"><?php echo esc_html($label); ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
                <div class="wrapper-control-shop gridLayout-wrapper">
                    <div class="meta-filter-shop">
                        <div id="product-count-grid" class="count-text"></div>
                        <div id="product-count-list" class="count-text"></div>
                        <div id="applied-filters"></div>
                        <button id="remove-all" class="remove-all-filters" style="display: none;">
                            <i class="icon icon-close"></i>
                            Clear all</button>
                    </div>


                    <div class="wrapper-shop tf-grid-layout tf-col-3" id="gridLayout">



                        <!-- Pagination -->
                        <!-- <div class="wd-full wg-pagination m-0 justify-content-center">
                            <a href="#" class="pagination-item h6 direct"><i class="icon icon-caret-left"></i></a>
                            <a href="#" class="pagination-item h6">1</a>
                            <span class="pagination-item h6 active">2</span>
                            <a href="#" class="pagination-item h6">3</a>
                            <a href="#" class="pagination-item h6">4</a>
                            <a href="#" class="pagination-item h6">5</a>
                            <a href="#" class="pagination-item h6 direct"><i class="icon icon-caret-right"></i></a>
                        </div> -->

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /Section Product -->


<!-- Box Icon -->
<div class="flat-spacing">
    <div class="container">
        <div dir="ltr" class="swiper tf-swiper" data-preview="4" data-tablet="3" data-mobile-sm="2" data-mobile="1"
            data-space-lg="97" data-space-md="33" data-space="13" data-pagination="1" data-pagination-sm="2"
            data-pagination-md="3" data-pagination-lg="4">
            <div class="swiper-wrapper">
                <!-- item 1 -->
                <div class="swiper-slide">
                    <div class="box-icon_V01">
                        <span class="icon">
                            <i class="icon-package"></i>
                        </span>
                        <div class="content">
                            <h4 class="title fw-normal">30 days return</h4>
                            <p class="text">30 day money back guarantee</p>
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
                            <h4 class="title fw-normal">3 year warranty</h4>
                            <p class="text">Manufacturer's defect</p>
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
                            <h4 class="title fw-normal">Free shipping</h4>
                            <p class="text">Free Shipping for orders over $150</p>
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
                            <h4 class="title fw-normal">Online support</h4>
                            <p class="text">24 hours a day, 7 days a week</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="sw-dot-default tf-sw-pagination"></div>
        </div>
    </div>
</div>
<!-- /Box Icon -->

<?php
do_action('woocommerce_after_main_content');
get_footer();
?>