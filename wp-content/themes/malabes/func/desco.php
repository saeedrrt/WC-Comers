<?php

/**
 * رجّع IDs لمنتجات قاعدة الخصم.
 *
 * @param int   $rule_id  بوست ID للقاعدة (CPT: wc_discount_rule)
 * @param array $opts     خيارات:
 *   'limit'          => 0 (بدون حد) | رقم
 *   'orderby'        => 'date' | 'title' | 'menu_order' | 'rand' | ...
 *   'order'          => 'DESC' | 'ASC'
 *   'in_stock_only'  => false
 *   'on_sale_only'   => false
 *   'include_hidden' => false
 * @return int[]                IDs لمنتجات منشورة
 */
function wcd_get_rule_product_ids($rule_id, array $opts = [])
{
    $defaults = [
        'limit' => 0,
        'orderby' => 'date',
        'order' => 'DESC',
        'in_stock_only' => false,
        'on_sale_only' => false,
        'include_hidden' => false,
    ];
    $o = array_merge($defaults, $opts);

    $rule_id = (int) $rule_id;
    if ($rule_id <= 0)
        return [];

    $type = get_post_meta($rule_id, '_wcd_type', true) ?: 'category';
    $cats = (array) get_post_meta($rule_id, '_wcd_categories', true);
    $prods = (array) get_post_meta($rule_id, '_wcd_products', true);

    $args = [
        'post_type' => 'product',
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => $o['limit'] ? (int) $o['limit'] : -1,
        'orderby' => $o['orderby'],
        'order' => $o['order'],
        'tax_query' => [],
        'meta_query' => [],
        'no_found_rows' => true,
    ];

    // استبعاد المنتجات المخفية من الكاتالوج
    if (!$o['include_hidden']) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_visibility',
            'field' => 'name',
            'terms' => ['exclude-from-catalog'],
            'operator' => 'NOT IN',
        ];
    }

    if ($type === 'category') {
        $cats = array_filter(array_map('intval', $cats));
        if (empty($cats))
            return [];
        $args['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => $cats,
            'operator' => 'IN',
        ];
    } else {
        $ids = array_filter(array_map('intval', $prods));
        if (empty($ids))
            return [];
        $norm = [];
        foreach ($ids as $id) {
            if (get_post_type($id) === 'product_variation') {
                $p = wp_get_post_parent_id($id);
                if ($p)
                    $norm[] = (int) $p;
            } else {
                $norm[] = (int) $id;
            }
        }
        $ids = array_values(array_unique($norm));
        if (empty($ids))
            return [];
        $args['post__in'] = $ids;
    }

    if ($o['in_stock_only']) {
        $args['meta_query'][] = [
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '=',
        ];
    }

    if ($o['on_sale_only']) {
        $sale_ids = wc_get_product_ids_on_sale();
        if (empty($sale_ids))
            return [];
        if (!empty($args['post__in'])) {
            $args['post__in'] = array_values(array_intersect($args['post__in'], $sale_ids));
            if (empty($args['post__in']))
                return [];
        } else {
            $args['post__in'] = $sale_ids;
        }
    }

    if (count($args['tax_query']) > 1)
        $args['tax_query']['relation'] = 'AND';
    if (count($args['meta_query']) > 1)
        $args['meta_query']['relation'] = 'AND';

    $q = new WP_Query($args);
    return $q->posts ? array_map('intval', $q->posts) : [];
}

/**
 * رجّع كائنات المنتجات (WC_Product) لقاعدة الخصم.
 */
function wcd_get_rule_products($rule_id, array $opts = [])
{
    $ids = wcd_get_rule_product_ids($rule_id, $opts);
    if (empty($ids))
        return [];

    $map = [];
    foreach ($ids as $id) {
        $p = wc_get_product($id);
        if ($p && $p->get_status() === 'publish')
            $map[$id] = $p;
    }
    return array_values(array_intersect_key($map, array_flip($ids)));
}

// ==============================================
// دعم Polylang للصفحات المخصصة
// ==============================================

/**
 * إضافة قواعد URL للغتين
 */
function wcd_add_rewrite_rules()
{
    // التحقق من وجود Polylang
    if (!function_exists('pll_languages_list')) {
        // fallback بدون polylang
        add_rewrite_rule(
            '^discount-rule/([^/]*)/?',
            'index.php?discount_rule_id=$matches[1]',
            'top'
        );
        return;
    }

    $languages = pll_languages_list();

    foreach ($languages as $lang) {
        if ($lang == 'ar') {
            // الرابط العربي
            add_rewrite_rule(
                '^ar/خصم-المنتجات/([^/]*)/?',
                'index.php?discount_rule_id=$matches[1]&lang=ar',
                'top'
            );
        } elseif ($lang == 'en') {
            // الرابط الإنجليزي
            add_rewrite_rule(
                '^en/discount-products/([^/]*)/?',
                'index.php?discount_rule_id=$matches[1]&lang=en',
                'top'
            );
        }
    }

    // رابط افتراضي بدون بادئة لغة
    add_rewrite_rule(
        '^discount-rule/([^/]*)/?',
        'index.php?discount_rule_id=$matches[1]',
        'top'
    );
}
add_action('init', 'wcd_add_rewrite_rules');

/**
 * إضافة query variables
 */
function wcd_add_query_vars($vars)
{
    $vars[] = 'discount_rule_id';
    return $vars;
}
add_filter('query_vars', 'wcd_add_query_vars');

/**
 * إنشاء رابط صفحة الخصم حسب اللغة
 */
function wcd_get_discount_rule_url($rule_id, $lang = null)
{
    if (!$lang && function_exists('pll_current_language')) {
        $lang = pll_current_language();
    }

    if (!$lang) {
        $lang = 'en'; // افتراضي
    }

    if ($lang == 'ar') {
        return home_url('/ar/خصم-المنتجات/' . $rule_id . '/');
    } elseif ($lang == 'en') {
        return home_url('/discount-rule/' . $rule_id . '/');
    }

    // fallback
    return home_url('/discount-rule/' . $rule_id . '/');
}

/**
 * معالجة الصفحة المخصصة مع دعم اللغات
 */
function wcd_template_redirect()
{
    $rule_id = get_query_var('discount_rule_id');

    if ($rule_id) {
        // التحقق من وجود القاعدة
        $rule = get_post($rule_id);
        if (!$rule || $rule->post_type !== 'wc_discount_rule' || $rule->post_status !== 'publish') {
            global $wp_query;
            $wp_query->set_404();
            status_header(404);
            return;
        }

        // تحديد اللغة
        $current_lang = 'en'; // افتراضي

        if (function_exists('pll_current_language')) {
            $detected_lang = pll_current_language();
            if ($detected_lang) {
                $current_lang = $detected_lang;
            }
        }

        // تحديد اللغة من URL إذا لم يتم اكتشافها
        if (isset($_GET['lang'])) {
            $current_lang = sanitize_text_field($_GET['lang']);
        }

        // تحميل ملف القالب المناسب
        $template_file = '';
        if ($current_lang == 'ar') {
            $template_file = get_template_directory() . '/wcd-discount-page-ar.php';
            if (!file_exists($template_file)) {
                $template_file = get_template_directory() . '/wcd-discount-page.php';
            }
        } else {
            $template_file = get_template_directory() . '/wcd-discount-page-en.php';
            if (!file_exists($template_file)) {
                $template_file = get_template_directory() . '/wcd-discount-page.php';
            }
        }

        // تمرير متغيرات للقالب
        global $wcd_current_rule_id, $wcd_current_lang;
        $wcd_current_rule_id = $rule_id;
        $wcd_current_lang = $current_lang;

        if (file_exists($template_file)) {
            include($template_file);
        } else {
            // قالب افتراضي مدمج
            wcd_default_discount_template($rule_id, $current_lang);
        }
        exit;
    }
}
add_action('template_redirect', 'wcd_template_redirect');

/**
 * قالب افتراضي للصفحة
 */
function wcd_default_discount_template($rule_id, $lang = 'en')
{
    get_header();

    // النصوص حسب اللغة
    $texts = wcd_get_texts($lang);

    // بيانات القاعدة
    $title = get_the_title($rule_id);
    $percent = (float) get_post_meta($rule_id, '_wcd_percent', true);
    $banner_text = get_post_meta($rule_id, '_wcd_banner', true);
    $thumb = has_post_thumbnail($rule_id) ? get_the_post_thumbnail_url($rule_id, 'large') : '';

    $banner_msg = $banner_text
        ? (strpos($banner_text, '%s') !== false ? sprintf($banner_text, $percent) : $banner_text)
        : sprintf($texts['sale_message'], $percent);

    ?>
    <div class="container">
        <!-- البنر -->
        <div class="wcd-sale-hero"
            style="display:flex;align-items:center;gap:16px;margin:2rem 0;padding:20px;border-radius:16px;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);color:white;">
            <?php if ($thumb): ?>
                <img src="<?php echo esc_url($thumb); ?>" alt="" style="max-height:100px;border-radius:12px;" />
            <?php endif; ?>
            <div class="txt">
                <h1 style="margin:0;font-size:2rem;font-weight:800;"><?php echo esc_html($title); ?></h1>
                <p style="margin:10px 0 0 0;font-size:1.2rem;"><?php echo esc_html($banner_msg); ?></p>
            </div>

            <!-- أزرار تغيير اللغة -->
            <div class="lang-switcher" style="margin-left:auto;">
                <?php echo wcd_get_language_switcher($rule_id); ?>
            </div>
        </div>

        <!-- المنتجات -->
        <div class="wcd-products-grid">
            <?php echo do_shortcode('[wcd_rule_products rule_id="' . $rule_id . '" limit="12"]'); ?>
        </div>
    </div>
    <?php

    get_footer();
}

/**
 * النصوص المترجمة
 */
function wcd_get_texts($lang)
{
    $texts = [
        'en' => [
            'sale_message' => 'SALE up to %s%%',
            'no_products' => 'No products found.',
            'view_product' => 'View Product',
            'add_to_cart' => 'Add to Cart',
            'out_of_stock' => 'Out of Stock',
            'sale' => 'Sale',
            'page_title' => 'Special Offers',
        ],
        'ar' => [
            'sale_message' => 'تخفيضات تصل إلى %s%%',
            'no_products' => 'لا توجد منتجات.',
            'view_product' => 'عرض المنتج',
            'add_to_cart' => 'إضافة للسلة',
            'out_of_stock' => 'غير متوفر',
            'sale' => 'تخفيض',
            'page_title' => 'العروض الخاصة',
        ],
    ];

    return isset($texts[$lang]) ? $texts[$lang] : $texts['en'];
}

/**
 * مبدل اللغات للصفحة
 */
function wcd_get_language_switcher($rule_id)
{
    if (!function_exists('pll_languages_list')) {
        return '';
    }

    $languages = pll_languages_list(['fields' => 'slug']);
    $current_lang = pll_current_language();

    $output = '<div class="wcd-lang-switcher" style="display:flex;gap:10px;">';

    foreach ($languages as $lang) {
        $url = wcd_get_discount_rule_url($rule_id, $lang);
        $active = ($lang == $current_lang) ? 'active' : '';
        $lang_name = ($lang == 'ar') ? 'العربية' : 'English';

        $output .= sprintf(
            '<a href="%s" class="lang-btn %s" style="padding:8px 16px;background:%s;color:%s;text-decoration:none;border-radius:8px;font-weight:bold;">%s</a>',
            esc_url($url),
            $active,
            $active ? '#fff' : 'rgba(255,255,255,0.3)',
            $active ? '#333' : '#fff',
            $lang_name
        );
    }

    $output .= '</div>';
    return $output;
}

/**
 * شورتكود محدث مع دعم اللغات
 */
add_shortcode('wcd_rule_products', function ($atts) {
    $a = shortcode_atts([
        'rule_id' => 0,
        'limit' => 12,
    ], $atts);

    $rule_id = (int) ($a['rule_id'] ?: (isset($_GET['wcd_rule']) ? $_GET['wcd_rule'] : 0));
    if ($rule_id <= 0)
        return '<p>' . (pll_current_language() == 'ar' ? 'لم يتم اختيار قاعدة.' : 'No rule selected.') . '</p>';

    // تحديد اللغة الحالية
    $current_lang = function_exists('pll_current_language') ? pll_current_language() : 'en';
    $texts = wcd_get_texts($current_lang);

    // بيانات القاعدة
    $percent = (float) get_post_meta($rule_id, '_wcd_percent', true);
    $banner_text = get_post_meta($rule_id, '_wcd_banner', true);
    $title = get_the_title($rule_id) ?: $texts['sale'];
    $thumb = has_post_thumbnail($rule_id) ? get_the_post_thumbnail_url($rule_id, 'large') : '';

    $msg = $banner_text
        ? (strpos($banner_text, '%s') !== false ? sprintf($banner_text, $percent) : $banner_text)
        : sprintf($texts['sale_message'], $percent);

    // IDs المنتجات
    $ids = wcd_get_rule_product_ids($rule_id);
    if (!$ids)
        return '<p>' . $texts['no_products'] . '</p>';

    // إعداد الكويري
    $paged = max(1, (int) get_query_var('paged'));
    $ppp = (int) $a['limit'];

    $q = new WP_Query([
        'post_type' => 'product',
        'post_status' => 'publish',
        'post__in' => $ids,
        'orderby' => 'post__in',
        'posts_per_page' => $ppp,
        'paged' => $paged,
    ]);

    ob_start();

    // البنر
    echo '<div class="wcd-sale-hero" style="display:flex;align-items:center;gap:16px;margin:.75rem 0;padding:14px;border-radius:16px;background:#f3f4f6;' .
        ($current_lang == 'ar' ? 'direction:rtl;' : '') . '">';
    if ($thumb)
        echo '<img src="' . esc_url($thumb) . '" alt="" style="max-height:80px;border-radius:12px" />';
    echo '<div class="txt" style="font-weight:800;font-size:1.05rem">' . esc_html($msg) . '</div></div>';

    // المنتجات
    if ($q->have_posts()) {
        woocommerce_product_loop_start();
        while ($q->have_posts()) {
            $q->the_post();
            wc_get_template_part('content', 'product');
        }
        woocommerce_product_loop_end();

        // الباجينيشن
        woocommerce_pagination([
            'total' => $q->max_num_pages,
            'current' => $paged,
        ]);
    } else {
        wc_no_products_found();
    }
    wp_reset_postdata();

    return ob_get_clean();
});

/**
 * إضافة SEO meta مع دعم اللغات
 */
function wcd_add_meta_tags()
{
    $rule_id = get_query_var('discount_rule_id');

    if ($rule_id) {
        $rule = get_post($rule_id);
        if ($rule) {
            $current_lang = function_exists('pll_current_language') ? pll_current_language() : 'en';
            $texts = wcd_get_texts($current_lang);

            $title = get_the_title($rule_id);
            $percent = (float) get_post_meta($rule_id, '_wcd_percent', true);

            echo '<meta name="robots" content="index,follow">' . "\n";
            echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
            echo '<meta property="og:type" content="website">' . "\n";
            echo '<meta property="og:url" content="' . esc_url(wcd_get_discount_rule_url($rule_id, $current_lang)) . '">' . "\n";

            if ($percent) {
                $description = sprintf($texts['sale_message'], $percent);
                echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
                echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
            }

            // hreflang للـ SEO
            if (function_exists('pll_languages_list')) {
                $languages = pll_languages_list(['fields' => 'slug']);
                foreach ($languages as $lang) {
                    $url = wcd_get_discount_rule_url($rule_id, $lang);
                    echo '<link rel="alternate" hreflang="' . $lang . '" href="' . esc_url($url) . '">' . "\n";
                }
            }
        }
    }
}
add_action('wp_head', 'wcd_add_meta_tags');

/**
 * تفعيل القوانين عند التفعيل
 */
function wcd_flush_rewrite_rules()
{
    wcd_add_rewrite_rules();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wcd_flush_rewrite_rules');

/**
 * إعادة تحميل القوانين عند تحديث permalinks
 */
function wcd_flush_rules_on_permalink_save()
{
    if (isset($_POST['permalink_structure']) || isset($_POST['category_base'])) {
        wcd_add_rewrite_rules();
        flush_rewrite_rules();
    }
}
add_action('admin_init', 'wcd_flush_rules_on_permalink_save');

/**
 * ستايل إضافي للواجهة
 */
add_action('wp_footer', 'wcd_custom_styles');
function wcd_custom_styles()
{
    ?>
    <style>
        .wcd-sale-hero {
            transition: all 0.3s ease;
        }

        .wcd-sale-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .wcd-lang-switcher .lang-btn {
            transition: all 0.3s ease;
        }

        .wcd-lang-switcher .lang-btn:hover {
            transform: translateY(-1px);
        }

        /* للتصميم العربي */
        [dir="rtl"] .wcd-sale-hero {
            text-align: right;
        }
    </style>

    <script>
        jQuery(document).ready(function ($) {
            // تحسينات إضافية للواجهة
            $(".tinvwl-to-right button").attr("style", "background-color: #021a62 !important;border-radius: 5px !important;padding: 10px !important;color: #fff !important");
            $(".tinvwl-input-group-btn button").attr("style", "background-color: #021a62 !important;border-radius: 5px !important;padding: 10px !important;color: #fff !important");
        });
    </script>
    <?php
}

/**
 * رجّع بيانات المنتجات مع دعم الترجمة
 */
function wcd_get_rule_products_data($rule_id, array $opts = [])
{
    $ids = wcd_get_rule_product_ids($rule_id, $opts);
    if (empty($ids))
        return [];

    $current_lang = function_exists('pll_current_language') ? pll_current_language() : 'en';
    $out = [];

    foreach ($ids as $pid) {
        $p = wc_get_product($pid);
        if (!$p || $p->get_status() !== 'publish')
            continue;

        // الحصول على ترجمة المنتج إذا كانت متوفرة
        if (function_exists('pll_get_post') && $current_lang != pll_default_language()) {
            $translated_id = pll_get_post($pid, $current_lang);
            if ($translated_id) {
                $p = wc_get_product($translated_id);
                $pid = $translated_id;
            }
        }

        // باقي البيانات كما هي...
        $thumb_id = $p->get_image_id();
        $thumb_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'large') : wc_placeholder_img_src();

        $gallery_ids = $p->get_gallery_image_ids();
        $second_url = !empty($gallery_ids) ? wp_get_attachment_image_url($gallery_ids[0], 'large') : '';

        $is_variable = $p->is_type('variable');
        if ($is_variable) {
            $reg = (float) $p->get_variation_regular_price('min', true);
            $sale = (float) $p->get_variation_sale_price('min', true);
        } else {
            $reg = (float) $p->get_regular_price();
            $sale = (float) $p->get_sale_price();
        }
        $on_sale = $p->is_on_sale();
        $discount_percent = ($on_sale && $reg > 0 && $sale > 0 && $sale < $reg)
            ? round((($reg - $sale) / $reg) * 100)
            : 0;

        $stock_status = $p->get_stock_status();
        $stock_qty = $p->managing_stock() ? (int) $p->get_stock_quantity() : null;

        $cat_names = wp_get_post_terms($pid, 'product_cat', ['fields' => 'names']);
        $brand = taxonomy_exists('product_brand') ? wp_get_post_terms($pid, 'product_brand', ['fields' => 'names']) : [];

        $rating_avg = (float) $p->get_average_rating();
        $rating_count = (int) $p->get_review_count();

        $out[] = [
            'id' => $pid,
            'type' => $p->get_type(),
            'sku' => $p->get_sku(),
            'name' => $p->get_name(),
            'permalink' => get_permalink($pid),
            'price_html' => $p->get_price_html(),
            'price' => (float) $p->get_price(),
            'regular_price' => $reg,
            'sale_price' => $sale,
            'on_sale' => $on_sale,
            'discount_percent' => $discount_percent,
            'stock_status' => $stock_status,
            'stock_quantity' => $stock_qty,
            'thumbnail' => $thumb_url,
            'second_image' => $second_url,
            'gallery_ids' => $gallery_ids,
            'categories' => $cat_names,
            'brands' => $brand,
            'rating_average' => $rating_avg,
            'rating_count' => $rating_count,
            'language' => $current_lang,
        ];
    }
    return $out;
}

?>