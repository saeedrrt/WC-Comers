<?php

if (class_exists('Woocommerce')) {

    /* Woocommerce support */
    function mtc_add_woocommerce_support()
    {
        add_theme_support('woocommerce');
    }
    add_action('after_setup_theme', 'mtc_add_woocommerce_support');

    //Remove Shop Title
    add_filter('woocommerce_show_page_title', '__return_false');
    add_filter('show_admin_bar', '__return_false');

    // Add Support
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}

add_action('init', function () {
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
});


// ===== CSS/JS لأزرار الكمية (+/-) عند صفحات المنتج =====
add_action('wp_enqueue_scripts', function () {
    if ( is_product() ) {
        // لو عندك ملف خارجي، استخدمه بدل الـ inline
        wp_add_inline_script('jquery-core', <<<JS
jQuery(function($){
  $('body').on('click', '.btn-quantity', function(){
    var \$btn = $(this);
    var \$qty = \$btn.closest('.wg-quantity').find('input.qty');
    var val  = parseFloat(\$qty.val()) || 0;
    var step = parseFloat(\$qty.attr('step')) || 1;
    var min  = parseFloat(\$qty.attr('min'))  || 1;
    var max  = parseFloat(\$qty.attr('max'))  || Infinity;

    if (\$btn.hasClass('btn-increase')) {
      val = Math.min(val + step, max);
    } else {
      val = Math.max(val - step, min);
    }
    \$qty.val(val).trigger('change');
  });
});
JS
        );
    }
});

add_action('wp_ajax_get_varch_by_price', 'get_varch_by_price');
add_action('wp_ajax_nopriv_get_varch_by_price', 'get_varch_by_price');

function get_varch_by_price()
{
    // (اختياري) تأمين نونس إن حبيت
    // if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'variation_nonce') ) {
    //     wp_send_json_error(['message' => 'Bad nonce'], 400);
    // }

    $variation_id = isset($_POST['price']) ? absint($_POST['price']) : 0;

    if (!$variation_id) {
        wp_send_json_error(['message' => 'Missing variation_id'], 400);
    }

    $variation = wc_get_product($variation_id);

    if (!$variation || 'variation' !== $variation->get_type()) {
        wp_send_json_error(['message' => 'Not a variation product'], 400);
    }

    // اسم الفاريشن (بيطلع "اسم المنتج - اللون، المقاس" غالباً)
    $variation_name = $variation->get_name();

    // لو عايز فورمات الخصائص فقط (Color: Blue, Size: L)
    $formatted_attrs = wc_get_formatted_variation($variation->get_attributes(), true);

    // السعر (رقم مع مراعاة ضريبة/عرض السعر حسب إعدادات المتجر)
    $price_number = wc_get_price_to_display($variation); // float
    // السعر HTML (يحترم سعر قبل/بعد الخصم)
    $price_html = $variation->get_price_html();

    wp_send_json_success([
        'variation_id' => $variation_id,
        'name' => $variation_name,     // مثال: "Product Name - Blue, L"
        'attributes' => $formatted_attrs,    // مثال: "Color: Blue, Size: L"
        'price' => $price_number,       // رقم صافي لعمليات حسابية
        'price_html' => $price_html,         // HTML جاهز للعرض
    ]);
    
}

// AJAX endpoints
add_action('wp_ajax_get_variation_price', 'my_get_variation_price');
add_action('wp_ajax_nopriv_get_variation_price', 'my_get_variation_price');

function my_get_variation_price() {
    // يفضّل استخدام نونس للحماية لو هتعمل enqueue/locaize
    // check_ajax_referer('var_price_nonce', 'nonce');

    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
    $product_id   = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $attributes   = isset($_POST['attributes']) && is_array($_POST['attributes']) ? array_map('wc_clean', $_POST['attributes']) : [];

    // لو variation_id مش مبعوت، حاول نطلعه من attributes
    if (!$variation_id && $product_id && !empty($attributes)) {
        $product = wc_get_product($product_id);
        if ($product && $product->is_type('variable')) {
            $data_store   = WC_Data_Store::load('product');
            $variation_id = $data_store->find_matching_product_variation($product, $attributes);
        }
    }

    if (!$variation_id) {
        wp_send_json_error(['message' => 'Variation not found'], 404);
    }

    $variation = wc_get_product($variation_id);
    if (!$variation || 'variation' !== $variation->get_type()) {
        wp_send_json_error(['message' => 'Invalid variation'], 400);
    }

    $price_html    = $variation->get_price_html();               // HTML جاهز
    $display_price = wc_get_price_to_display($variation);        // رقم صافي
    $regular_price = (float) $variation->get_regular_price();    // الرقم قبل الخصم (لو محتاجه)

    wp_send_json_success([
        'variation_id'  => $variation_id,
        'price_html'    => $price_html,
        'display_price' => $display_price,
        'regular_price' => $regular_price,
    ]);
}




// ====== مساعد عام يطبع واجهتك للكمية + زر الإضافة ======
function mytheme_render_qty_and_button( WC_Product $product, $is_variable = false ) {
    // للمتغيّر لازم نحافظ على hidden inputs:
    if ( $is_variable ) {
        echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( $product->get_id() ) . '">';
        echo '<input type="hidden" name="product_id"  value="' . esc_attr( $product->get_id() ) . '">';
        echo '<input type="hidden" name="variation_id" class="variation_id" value="">'; // يملأها سكربت Woo
    } else {
        // للـ simple: يكفي add-to-cart
        echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( $product->get_id() ) . '">';
    }

    // واجهتك:
    ?>
    <div class="tf-product-total-quantity">
      <div class="group-btn">

        <div class="wg-quantity">
          <button type="button" class="btn-quantity btn-decrease">
            <i class="icon icon-minus"></i>
          </button>

          <?php
          woocommerce_quantity_input([
            'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
            'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ) ?: '',
            'input_value' => isset($_POST['quantity']) ? wc_stock_amount( wp_unslash($_POST['quantity']) ) : $product->get_min_purchase_quantity(),
            'classes'     => ['input-text','qty','text','quantity-product'],
          ]);
          ?>

          <button type="button" class="btn-quantity btn-increase">
            <i class="icon icon-plus"></i>
          </button>
        </div>

        <button type="submit"
                class="tf-btn animate-btn btn-add-to-cart single_add_to_cart_button">
          <?php echo esc_html( $product->single_add_to_cart_text() ); ?>
          <i class="icon icon-shopping-cart-simple"></i>
        </button>

        <?php
        // وِش ليست – لو تستخدم TI Wishlist
        if ( function_exists('do_shortcode') ) {
            echo do_shortcode('[ti_wishlists_addtowishlist loop=yes]');
        }
        ?>

      </div>
    </div>
    <?php
}

// ====== (A) تخصيص المنتجات المتغيرة (variable) ======
// نشيل زر الإضافة الافتراضي (بيطبع الكمية + الزر + hidden inputs)
remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );

// نضيف طباعة الواجهة الخاصة بنا بدل الافتراضي
add_action( 'woocommerce_single_variation', function () {
    global $product;
    if ( ! $product instanceof WC_Product_Variable ) return;

    // Woo بيطبع داخل .single_variation_wrap: السعر/التوافر (hook: woocommerce_single_variation -10)
    // هنا نطبع الكمية + الزر + الحقول المخفية
    mytheme_render_qty_and_button( $product, true );
}, 20);

// ====== (B) تخصيص المنتجات البسيطة (simple) ======
// نشيل الافتراضي
remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );

// نضيف نسختنا
add_action( 'woocommerce_simple_add_to_cart', function() {
    global $product;
    if ( ! $product instanceof WC_Product_Simple ) return;

    echo '<form class="cart" action="' . esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ) . '" method="post" enctype="multipart/form-data">';
      mytheme_render_qty_and_button( $product, false );
    echo '</form>';
}, 30);



/* ---------- توليد مارك-أب أيقونة الكارت ---------- *//* 1. دالة بتبني المارك-أب بتاع أيقونة الكارت */
// إضافة أيقونة السلة في الهيدر
function my_header_cart_markup()
{
    $lango = pll_current_language();
    // اتأكد إن ووكوميرس مفعّل
    if (!function_exists('WC')) {
        return ''; // لو مش موجودة ووكوميرس ما تطبعش حاجة
    }

    $count = WC()->cart->get_cart_contents_count();
    $subtotal = WC()->cart->get_cart_subtotal();
    $cart_url = wc_get_cart_url();

    ob_start(); ?>

    <li id="header-cart">
        <a class="nav-icon-item-2 text-white link" href="<?php echo esc_url($cart_url); ?>" data-bs-toggle="offcanvas"
            data-bs-target="#shoppingCart">
    
            <div class="position-relative d-flex">
                <i class="icon icon-shopping-cart-simple"></i>
                <span class="count"><?php echo esc_html($count); ?></span>
            </div>
    
            <div class="nav-icon-item_sub d-none d-sm-grid">
                <span class="text-sub text-small-2"><?php echo $lango == 'ar' ? 'سلة التسوق' : 'Your cart'; ?></span>
                <span class="h6"><?php echo wp_kses_post($subtotal); ?></span>
            </div>
        </a>
    </li>
    <?php
    return ob_get_clean();
}

// إضافة fragments للهيدر
add_filter('woocommerce_add_to_cart_fragments', function ($fragments) {
    $fragments['li#header-cart'] = my_header_cart_markup();
    return $fragments;
});

// شورتكود احتياطي
add_shortcode('header_cart', 'my_header_cart_markup');

// تحسين تحديث الهيدر عند تغيير السلة
add_action('wp_footer', 'enhance_header_cart_updates');
function enhance_header_cart_updates()
{
    if (is_admin())
        return;
    ?>
    <script>
        jQuery(function ($) {
            // تحديث الهيدر عند تغيير السلة
            $(document.body).on('wc_fragments_loaded wc_fragments_refreshed', function () {
                // تم تحديث الـ fragments تلقائياً
            });

            // تحديث إضافي للأرقام في الهيدر
            function updateHeaderCart(cartData) {
                var $headerCart = $('#header-cart');
                if ($headerCart.length) {
                    $headerCart.find('.count').text(cartData.cart_count || 0);

                    // تحديث subtotal إذا كان موجود
                    if (cartData.cart_subtotal) {
                        $headerCart.find('.h6').html(cartData.cart_subtotal);
                    }
                }
            }

            // ربط التحديث مع الأحداث الموجودة
            $(document).on('cart_updated', function (event, data) {
                if (data) {
                    updateHeaderCart(data);
                }
            });

            // تحديث عند فتح الـ offcanvas
            $('#shoppingCart').on('show.bs.offcanvas', function () {
                // تحديث المحتوى عند فتح السايدبار
                setTimeout(function () {
                    if (window.refreshCartContents) {
                        window.refreshCartContents();
                    }
                }, 100);
            });
        });
    </script>
    <?php
}

/**
 *  ===== Wishlist Icon in Header =====
 */
if (!function_exists('my_header_wishlist_icon')) {
    function my_header_wishlist_icon()
    {

        // لو الإضافة مش مفعَّلة ما تطبعش حاجة
        if (!function_exists('YITH_WCWL')) {
            return '';
        }

        // عدد العناصر + لينك صفحة الـ Wishlist
        $count = YITH_WCWL()->count_products(); // بيرجع رقم المنتجات
        $url = YITH_WCWL()->get_wishlist_url(); // بيرجع لينك الصفحة :contentReference[oaicite:1]{index=1}

        ob_start(); ?>
        <li id="header-wishlist">
            <a class="nav-icon-item-2 text-white link" href="<?php echo esc_url($url); ?>">

                <div class="position-relative d-flex">
                    <i class="icon icon-heart"></i>

                    <!-- العنصر اللى الإضافة بتحدّثه بالـ JS -->
                    <span class="count">
                        <span class="yith-wcwl-items-count">
                            <?php echo esc_html($count); ?>
                        </span>
                    </span>
                </div>
            </a>
        </li>
        <?php
        return ob_get_clean();
    }
}

// إضافة jQuery BlockUI plugin مع WooCommerce scripts
add_action('wp_enqueue_scripts', function () {
    // تحميل jQuery BlockUI plugin
    wp_enqueue_script('jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', array('jquery'), '2.70', true);

    // تحميل WooCommerce add-to-cart script
    wp_enqueue_script('wc-add-to-cart');

    // تأكد من تحميل wc-cart-fragments
    wp_enqueue_script('wc-cart-fragments');
}, 20);

// إضافة AJAX actions
add_action('wp_ajax_remove_cart_item', 'remove_cart_item_ajax');
add_action('wp_ajax_nopriv_remove_cart_item', 'remove_cart_item_ajax');

add_action('wp_ajax_update_cart_quantity', 'update_cart_quantity_ajax');
add_action('wp_ajax_nopriv_update_cart_quantity', 'update_cart_quantity_ajax');

add_action('wp_ajax_refresh_cart_contents', 'refresh_cart_contents_ajax');
add_action('wp_ajax_nopriv_refresh_cart_contents', 'refresh_cart_contents_ajax');

// دالة حذف عنصر من السلة
function remove_cart_item_ajax()
{
    if (!wp_verify_nonce($_POST['nonce'], 'cart_nonce')) {
        wp_die('Security check failed');
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);

    $removed = WC()->cart->remove_cart_item($cart_item_key);

    if ($removed) {
        WC()->cart->calculate_totals();

        // إنشاء fragments للهيدر
        $fragments = array();

        // تحديث header cart إذا كانت الدالة موجودة
        if (function_exists('my_header_cart_markup')) {
            $fragments['li#header-cart'] = my_header_cart_markup();
        }

        // إضافة fragments إضافية لعداد السلة
        ob_start();
        ?>
        <span class="prd-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
        <?php
        $fragments['.prd-count'] = ob_get_clean();

        wp_send_json_success(array(
            'message' => 'تم حذف المنتج بنجاح',
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total(),
            'cart_subtotal' => wc_price(WC()->cart->get_cart_contents_total()),
            'is_empty' => WC()->cart->is_empty(),
            'fragments' => $fragments
        ));
    } else {
        wp_send_json_error(array(
            'message' => 'فشل في حذف المنتج'
        ));
    }
}

// دالة تعديل كمية المنتج
function update_cart_quantity_ajax()
{
    if (!wp_verify_nonce($_POST['nonce'], 'cart_nonce')) {
        wp_die('Security check failed');
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $quantity = intval($_POST['quantity']);

    if ($quantity <= 0) {
        $removed = WC()->cart->remove_cart_item($cart_item_key);
        $action = 'removed';
    } else {
        $updated = WC()->cart->set_quantity($cart_item_key, $quantity);
        $action = 'updated';
    }

    WC()->cart->calculate_totals();

    // حساب السعر للمنتج المحدد
    $item_price = '';
    if ($action === 'updated') {
        $cart_item = WC()->cart->get_cart_item($cart_item_key);
        if ($cart_item) {
            $product = $cart_item['data'];
            $item_price = wc_price($product->get_price() * $quantity);
        }
    }

    // إنشاء fragments للهيدر
    $fragments = array();

    // تحديث header cart إذا كانت الدالة موجودة
    if (function_exists('my_header_cart_markup')) {
        $fragments['li#header-cart'] = my_header_cart_markup();
    }

    // إضافة fragments إضافية لعداد السلة
    ob_start();
    ?>
    <span class="prd-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
    <?php
    $fragments['.prd-count'] = ob_get_clean();

    $data334 = get_cart_weighted_sale_percent_sum();

    wp_send_json_success(array(
        'action' => $action,
        'message' => $action == 'updated' ? 'تم تعديل الكمية بنجاح' : 'تم حذف المنتج بنجاح',
        'cart_count' => WC()->cart->get_cart_contents_count(),
        'cart_total' => WC()->cart->get_cart_total(),
        'cart_subtotal' => wc_price(WC()->cart->get_cart_contents_total()),
        'is_empty' => WC()->cart->is_empty(),
        'new_quantity' => $quantity,
        'item_price' => $item_price,
        'discount_percent' => $data334['sum_weighted_percent'],
        'fragments' => $fragments
    ));
}

// دالة حساب إجمالي نسبة الخصم
/**
 * ترجع تفاصيل الخصومات في السلة:
 * - original_unit: السعر الأصلي للوحدة (regular) بنفس منطق العرض (ضرائب حسب إعداداتك)
 * - current_unit: السعر الحالي المعروض (قد يكون سعر التخفيض)
 * - sale_discount: خصم التخفيض (Regular - Sale) * الكمية
 * - coupon_discount: خصم الكوبونات من WooCommerce (line_subtotal - line_total)
 * - total_line_discount: إجمالي الخصم على هذا السطر (التخفيض + الكوبون)
 * - original_line_total: إجمالي السعر الأصلي (قبل أي خصومات) = original_unit * qty
 * - percent_of_original: نسبة الخصم من السعر الأصلي للسطر
 * - totals: مجاميع عامة لكل الخصومات
 */

/**
 * تحسب "نسبة خصم الوحدة" × "عدد القطع" لكل سطر في السلة
 * وتُرجع مجموعهم + تفاصيل الأسطر.
 *
 * - النسبة محسوبة من regular → sale فقط.
 * - بتحترم طريقة عرض الأسعار (شامل/غير شامل ضريبة) باستخدام wc_get_price_to_display.
 * - لو مفيش regular أو مفيش sale حقيقي أقل من regular؛ النسبة بتبقى 0%.
 */
function get_cart_weighted_sale_percent_sum()
{
    if (!WC()->cart) {
        return [
            'items' => [],
            'sum_weighted_percent' => 0.0,
        ];
    }

    $items = [];
    $sum_weighted_percent = 0.0;

    foreach (WC()->cart->get_cart() as $cart_item) {
        /** @var WC_Product $product */
        $product = isset($cart_item['data']) ? $cart_item['data'] : null;
        $qty = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 0;

        if (!$product || $qty <= 0) {
            continue;
        }

        // أسعار المنتج
        $regular_raw = (float) $product->get_regular_price();
        $sale_raw = (float) $product->get_sale_price();

        // لو مفيش regular، خليه fallback للسعر الحالي عشان ما تحصلش قسمة على صفر
        if ($regular_raw <= 0) {
            $regular_raw = (float) $product->get_price();
        }

        // تحويل لنفس منطق عرض السعر (شامل/غير شامل ضريبة)
        $regular = (float) wc_get_price_to_display($product, ['price' => $regular_raw]);
        $sale = (float) wc_get_price_to_display($product, ['price' => $sale_raw]);

        // نسبة الخصم للوحدة (من regular → sale)
        $unit_percent = 0.0;
        if ($regular > 0 && $sale > 0 && $sale < $regular) {
            $unit_percent = (($regular - $sale) / $regular) * 100.0; // %
        }

        // النسبة × عدد القطع لسطر السلة الحالي
        $weighted_percent = $unit_percent * $qty;

        // خزّن التفاصيل
        $items[] = [
            'product_id' => $product->get_id(),
            'name' => $product->get_name(),
            'qty' => $qty,
            'regular_unit' => $regular,
            'sale_unit' => $sale,
            'unit_percent' => round($unit_percent, 2),      // نسبة خصم الوحدة
            'weighted_percent' => round($weighted_percent, 2),  // النسبة × الكمية
        ];

        // جمع الإجمالي
        $sum_weighted_percent += $weighted_percent;
    }

    return [
        'items' => $items,
        'sum_weighted_percent' => round($sum_weighted_percent, 2),
    ];
}


// function get_total_discount_percentage()
// {
//     if (!WC()->cart) {
//         return 0;
//     }

//     $include_tax = WC()->cart->display_prices_including_tax(); // نفس منطق العرض في السلة
//     $original_total = 0.0; // إجمالي قبل الخصومات (حسب وضع الضريبة المعروض)
//     $current_total = 0.0; // إجمالي بعد الخصومات (حسب وضع الضريبة المعروض)

//     foreach (WC()->cart->get_cart() as $cart_item) {
//         /** @var WC_Product $product */
//         $product = isset($cart_item['data']) ? $cart_item['data'] : null;
//         $qty = isset($cart_item['quantity']) ? (int) $cart_item['quantity'] : 0;

//         if (!$product || $qty <= 0) {
//             continue;
//         }

//         // السعر الأصلي للوحدة (regular). لو غير متاح، هنستخدم السعر الحالي كـ fallback
//         $regular = (float) $product->get_regular_price();
//         $sale = (float) $product->get_sale_price();
//         if ($regular <= 0) {
//             $regular = (float) $product->get_price();
//         }

//         // نحول السعر الأصلي للوحدة لنفس طريقة العرض (شامل/غير شامل الضريبة)
//         $original_unit_display = (float) wc_get_price_to_display($product, ['price' => $regular]);
//         $original_line_total = $original_unit_display * $qty;

//         // إجمالي السطر الحالي بعد الخصومات (line_total) + الضريبة لو العرض شامل
//         $line_total = isset($cart_item['line_total']) ? (float) $cart_item['line_total'] : 0.0;
//         $line_tax = isset($cart_item['line_tax']) ? (float) $cart_item['line_tax'] : 0.0;

//         $current_line_total = $include_tax ? ($line_total + $line_tax) : $line_total;

//         // تراكم الإجماليات
//         $original_total += $original_line_total;
//         $current_total += $current_line_total;

//         $disco = ($regular - $sale) / $regular * 100;
//         // echo $regular - $sale;
//         foreach ($cart_item as $item) {
//             if (isset($item['qty']) && $item['qty'] > 0) {
//                 $qty = $item['qty'];

//                 echo $disco * $qty;
//             }
//         }

//     }

//     if ($original_total <= 0) {
//         return 0;
//     }

//     $discount_total = max(0.0, $original_total - $current_total);
//     $percent = ($discount_total / $original_total) * 100;

//     return round($percent, precision: 2); // مثال: 25.00
// }

// function get_cart_discounts_details()
// {
//     if (!WC()->cart) {
//         return [
//             'items' => [],
//             'totals' => [
//                 'sale_discount_total' => 0.0,
//                 'coupon_discount_total' => 0.0,
//                 'total_discount' => 0.0,
//                 'original_total' => 0.0,
//                 'current_total' => 0.0,
//                 'discount_percent' => 0.0,
//             ],
//         ];
//     }

//     $items = [];
//     $totals = [
//         'sale_discount_total' => 0.0,
//         'coupon_discount_total' => 0.0,
//         'total_discount' => 0.0,
//         'original_total' => 0.0,
//         'current_total' => 0.0,
//         'discount_percent' => 0.0,
//     ];

//     foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
//         /** @var WC_Product $product */
//         $product = $cart_item['data'];
//         $qty = (int) $cart_item['quantity'];

//         if (!$product || $qty <= 0) {
//             continue;
//         }

//         // أسعار "موحّدة" مع طريقة عرض السعر (مع/بدون ضريبة حسب إعداد المتجر)
//         $regular_raw = (float) $product->get_regular_price(); // السعر الأصلي الخام
//         // لو مفيش regular (منتجات متغيرة أحيانًا)، اعتبر السعر الحالي كأصلي عشان ما نطلعش أرقام سالبة
//         if ($regular_raw <= 0) {
//             $regular_raw = (float) $product->get_price();
//         }

//         $original_unit = (float) wc_get_price_to_display($product, ['price' => $regular_raw]);
//         $current_unit = (float) wc_get_price_to_display($product, ['price' => (float) $product->get_price()]);

//         // خصم التخفيض (Sale) = الفرق بين الأصلي والحالي * الكمية (لو في فعلاً فرق)
//         $unit_sale_diff = max(0.0, $original_unit - $current_unit);
//         $sale_discount = $unit_sale_diff * $qty;

//         // خصم الكوبون من بيانات السطر في الكارت (pre-tax)
//         // line_subtotal = قبل أي كوبونات، line_total = بعد الكوبونات
//         $coupon_discount = 0.0;
//         if (isset($cart_item['line_subtotal'], $cart_item['line_total'])) {
//             $coupon_discount = max(0.0, (float) $cart_item['line_subtotal'] - (float) $cart_item['line_total']);
//         }

//         // الإجماليات
//         $original_line_total = $original_unit * $qty;
//         // السعر الحالي الإجمالي (قبل/بعد الكوبون؟ هنا بنحسبه من current_unit * qty)
//         $current_line_total = $current_unit * $qty;
//         $total_line_discount = $sale_discount + $coupon_discount;

//         // نسبة الخصم من السعر الأصلي (لو الأصلي > 0)
//         $percent_of_original = 0.0;
//         if ($original_line_total > 0) {
//             $percent_of_original = ($total_line_discount / $original_line_total) * 100.0;
//         }

//         // خزّن تفاصيل السطر
//         $items[] = [
//             'product_id' => $product->get_id(),
//             'name' => $product->get_name(),
//             'qty' => $qty,
//             'original_unit' => $original_unit,
//             'current_unit' => $current_unit,
//             'sale_discount' => $sale_discount,
//             'coupon_discount' => $coupon_discount,
//             'total_line_discount' => $total_line_discount,
//             'original_line_total' => $original_line_total,
//             'current_line_total' => $current_line_total,
//             'percent_of_original' => $percent_of_original,
//         ];

//         // جمع المجاميع
//         $totals['sale_discount_total'] += $sale_discount;
//         $totals['coupon_discount_total'] += $coupon_discount;
//         $totals['total_discount'] += $total_line_discount;
//         $totals['original_total'] += $original_line_total;
//         $totals['current_total'] += $current_line_total;
//     }

//     // نسبة الخصم على مستوى السلة كلها من إجمالي السعر الأصلي
//     if ($totals['original_total'] > 0) {
//         $totals['discount_percent'] = ($totals['total_discount'] / $totals['original_total']) * 100.0;
//     }

//     return [
//         'items' => $items,
//         'totals' => $totals,
//     ];
// }
// إضافة نسبة الخصم لـ fragments عند إضافة منتج للسلة
// add_filter('woocommerce_add_to_cart_fragments', 'add_discount_to_fragments');

// function add_discount_to_fragments($fragments)
// {
//     $discount_percent = get_total_discount_percentage();
//     $fragments['.total.h6'] = '<span class="total h6">' . $discount_percent . '%</span>';

//     // تحديث الإجمالي أيضاً
//     $fragments['.cart-total-mer'] = '<span class="total-mer cart-total-mer">' . WC()->cart->get_cart_total() . '</span>';
//     $fragments['.cart-subtotal-mer'] = '<span class="total-mer cart-subtotal-mer">' . WC()->cart->get_cart_subtotal() . '</span>';

//     return $fragments;
// }

// دالة تحديث محتويات السلة
function refresh_cart_contents_ajax()
{
    if (!wp_verify_nonce($_POST['nonce'], 'cart_nonce')) {
        wp_die('Security check failed');
    }
    $lango = pll_current_language();

    $cart = WC()->cart;

    ob_start();
    ?>
    <div class="tf-mini-cart-items <?php echo $cart->is_empty() ? 'list-empty' : ''; ?>">
        <?php if ($cart->is_empty()): ?>
            <div class="box-text_empty type-shop_cart">
                <div class="shop-empty_top">
                    <span class="icon"><i class="icon-shopping-cart-simple"></i></span>
                    <h3 class="text-emp fw-normal"><?php echo $lango == 'ar' ? 'سلة فارغة' : 'Your cart is empty'; ?></h3>
                    <p class="h6 text-main">
                        <?php echo $lango == 'ar' ? 'سلة فارغة. دعنا نساعدك في العثور على المنتج المناسب' : 'Your cart is currently empty. Let us assist you in finding the right product'; ?>
                    </p>
                </div>
                <div class="shop-empty_bot">
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"
                        class="tf-btn animate-btn"><?php echo $lango == 'ar' ? 'تسوق الآن' : 'Shopping now'; ?></a>
                    <a href="<?php echo esc_url(home_url('/')); ?>"
                        class="tf-btn style-line"><?php echo $lango == 'ar' ? 'الرئيسية' : 'Back to home'; ?></a>
                </div>
            </div>
        <?php else: ?>
            <?php
            foreach ($cart->get_cart() as $cart_item_key => $cart_item):
                $product = $cart_item['data'];
                if (!$product || !$product->exists() || $cart_item['quantity'] <= 0) {
                    continue;
                }
                $product_id = $product->get_id();
                $product_link = apply_filters('woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                ?>
                <div class="tf-mini-cart-item file-delete" data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>">
                    <div class="tf-mini-cart-image">
                        <?php
                        $thumbnail = $product->get_image('woocommerce_thumbnail');
                        if (!$product_link) {
                            echo $thumbnail;
                        } else {
                            printf('<a href="%s">%s</a>', esc_url($product_link), $thumbnail);
                        }
                        ?>
                    </div>

                    <div class="tf-mini-cart-info">
                        <?php echo wc_get_product_category_list($product_id, ', ', '<div class="text-small text-main-2 sub">', '</div>'); ?>

                        <h6 class="title">
                            <?php
                            if (!$product_link) {
                                echo wp_kses_post($product->get_name());
                            } else {
                                echo sprintf('<a href="%s" class="link text-line-clamp-1">%s</a>', esc_url($product_link), wp_kses_post($product->get_name()));
                            }
                            ?>
                        </h6>

                        <?php
                        if (!empty($cart_item['variation'])):
                            echo '<div class="size">';
                            foreach ($cart_item['variation'] as $attr => $value) {
                                printf('<div class="text-small text-main-2 sub">%s: %s</div>', wc_attribute_label($attr), esc_html($value));
                            }
                            echo '</div>';
                        endif;
                        ?>

                        <div class="d-flex justify-content-between align-items-center">
                            <div class="quantity-controls">
                                <button class="qty-btn minus" data-action="decrease">-</button>
                                <span class="quantity-display"><?php echo esc_html($cart_item['quantity']); ?></span>
                                <button class="qty-btn plus" data-action="increase">+</button>
                            </div>

                            <div class="h6 fw-semibold item-price">
                                <span
                                    class="price text-primary tf-mini-card-price"><?php echo wc_price($product->get_price() * $cart_item['quantity']); ?></span>
                            </div>

                            <a href="#" class="remove_from_cart_button icon link icon-close"
                                data-product_id="<?php echo esc_attr($product_id); ?>"
                                data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>"></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php
    $content = ob_get_clean();

    $data334 = get_cart_weighted_sale_percent_sum();

    wp_send_json_success(array(
        'content' => $content,
        'cart_count' => $cart->get_cart_contents_count(),
        'cart_subtotal' => wc_price($cart->get_cart_contents_total()),
        'discount_percent' => $data334['sum_weighted_percent'],
        'is_empty' => $cart->is_empty()
    ));
}

// في functions.php
add_action('wp_head', 'add_ajax_url');
function add_ajax_url()
{
    echo '<script>var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
}

/**
 * شورتكود [user_coupons] لعرض كوبونات العضو الحالي
 * الاستخدام: حط [user_coupons] بدل الـ <div class="group-discount"> الثابتة.
 */
add_shortcode( 'user_coupons', function () {

    // لازم يكون مسجّل دخول
    if ( ! is_user_logged_in() ) {
        return ''; // أو رجّع رسالة تقول "سجّل دخولك عشان تشوف الكوبونات"
    }

    $user_id = get_current_user_id();

    /**
     * WooCommerce عنده هيلبر جديد من 8.1: wc_get_customer_available_coupons()
     * لو مفيش، نعمل fallback بكويرى يدوي.
     */
    if ( function_exists( 'wc_get_customer_available_coupons' ) ) {
        $coupons = wc_get_customer_available_coupons( $user_id );
    } else {
        // fallback – هات كل كوبونات مربوط فيها الـ user ID
        $coupons = array();
        $posts   = get_posts( array(
            'post_type'      => 'shop_coupon',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            // 'meta_query'     => array(
            //     array(
            //         'key'     => 'customer_user',
            //         'value'   => '"' . $user_id . '"',
            //         'compare' => 'LIKE',
            //     ),
            // ),
        ) );

        foreach ( $posts as $post ) {
            $coupons[] = new WC_Coupon( $post->post_title );
        }
    }

    // لو مفيش ولا كوبون
    if ( empty( $coupons ) ) {
        return ''; // أو اطبع رسالة "مفيش كوبونات دلوقتي"
    }

    ob_start();

    foreach ( $coupons as $coupon ) :

        // تخطي الكوبون لو منتهي
        if ( $coupon->get_date_expires() && $coupon->get_date_expires()->getTimestamp() < time() ) {
            continue;
        }

        $code          = $coupon->get_code();
        $amount        = $coupon->get_amount();
        $discount_type = $coupon->get_discount_type(); // percent | fixed_cart | fixed_product
        $min_spend     = $coupon->get_minimum_amount();

        // نص الخصم
        $label = 'percent' === $discount_type
            ? $amount . '% OFF'
            : wc_price( $amount ) . ' OFF';
        ?>

        <div class="group-discount mb-xl-0">
            <div class="box-discount">
                <div class="discount-top">
                    <div class="discount-off">
                        <p class="h6"><?php _e( 'Discount', 'textdomain' ); ?></p>
                        <h6 class="sale-off h6 fw-bold"><?php echo esc_html( $label ); ?></h6>
                    </div>

                    <?php if ( $min_spend ) : ?>
                        <div class="discount-from">
                            <p class="h6">
                                <?php printf( __( 'For all orders from %s', 'textdomain' ), wc_price( $min_spend ) ); ?>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="discount-bot">
                    <h6><?php _e( 'Code:', 'textdomain' ); ?> <span class="coupon-code"><?php echo esc_html( $code ); ?></span></h6>

                    <!-- الزر بينسخ الكود ويحطه فى الإنبوت فوق -->
                    <button class="tf-btn coupon-copy-wrap h6" type="button" data-code="<?php echo esc_attr( $code ); ?>">
                        <?php _e( 'Apply Code', 'textdomain' ); ?>
                    </button>
                </div>
            </div>
        </div>

    <?php
    endforeach;

    return ob_get_clean();
} );

// Cart Discount

// AJAX handler لتحديث الكمية صفّ صفّ
add_action( 'wp_ajax_tf_update_cart_row_qty', 'tf_update_cart_row_qty' );
add_action( 'wp_ajax_nopriv_tf_update_cart_row_qty', 'tf_update_cart_row_qty' );

function tf_update_cart_row_qty() {
    // (تحقّق nonce لو حابب)
    $cart_key = sanitize_text_field( $_POST['cart_key'] ? $_POST['cart_key'] : '' );
    $qty      = intval( $_POST['quantity'] ? $_POST['quantity'] : 0 );

    if ( ! $cart_key || $qty < 1 ) {
        wp_send_json_error( 'بيانات غير صحيحة' );
    }

    $updated = WC()->cart->set_quantity( $cart_key, $qty, true );

    if ( ! $updated ) {
        wp_send_json_error( 'فشل في تحديث الكمية' );
    }

    WC()->cart->calculate_totals();

    $cart_item = WC()->cart->get_cart_item( $cart_key );
    $product   = $cart_item['data'];
    $row_total = wc_price( $cart_item['line_subtotal'] ); // سعر العنصر بعد التعديل

    // **هنا التعديل الأساسي**:
    $cart_subtotal = WC()->cart->get_cart_subtotal();
    $cart_total    = wc_price( WC()->cart->total );      // القيمة الإيجابية

    wp_send_json_success( [
        'row_total'     => $row_total,
        'cart_subtotal' => $cart_subtotal,
        'cart_total'    => $cart_total,
        'data' => $updated
    ] );
}

add_action( 'wp_enqueue_scripts', function() {
    if ( is_cart() ) {
        wp_enqueue_script(
            'tf-cart-row-update',
            get_stylesheet_directory_uri() . '/js/tf-cart.js',
            [ 'jquery', 'wc-cart-fragments' ],
            null,
            true
        );
        wp_localize_script( 'tf-cart-row-update', 'wc_row_ajax', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'tf_cart_row_nonce' ),
        ] );
    }
} );


function tf_get_cart_discounts_breakdown() {

    $cart = WC()->cart;

    // مفيش عربية أصلاً
    if ( ! $cart || $cart->is_empty() ) {
        return array();
    }

    $items_data      = array();
    $regular_total   = 0;
    $sale_total      = 0;

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {

        /** @var WC_Product $_product */
        $_product  = $cart_item['data'];

        $regular   = (float) $_product->get_regular_price();
        $sale      = (float) $_product->get_sale_price();
        $qty       = (int)   $cart_item['quantity'];

        // منتج مفيهوش خصم
        if ( ! $sale || $sale >= $regular ) {
            continue;
        }

        // نسبة الخصم لكل منتج
        $discount_percent = round( ( ( $regular - $sale ) / $regular ) * 100 );

        $items_data[] = array(
            'product_id'       => $_product->get_id(),
            'name'             => $_product->get_name(),
            'regular'          => $regular,
            'sale'             => $sale,
            'qty'              => $qty,
            'discount_percent' => $discount_percent,
        );

        $regular_total += $regular * $qty;
        $sale_total    += $sale    * $qty;
    }

    // إجمالى الخصم
    $discount_total      = $regular_total - $sale_total;
    $discount_percentage = $regular_total ? round( ( $discount_total / $regular_total ) * 100 ) : 0;

    return array(
        'items'  => $items_data,
        'totals' => array(
            'regular_total'       => $regular_total,
            'sale_total'          => $sale_total,
            'discount_total'      => $discount_total,
            'discount_percentage' => $discount_percentage,
        ),
    );
}

add_action('wp_footer', 'add_cart_ajax_functionality');
function add_cart_ajax_functionality()
{
    if (is_admin())
        return;
    $cart_nonce = wp_create_nonce('cart_nonce');
    ?>
    <style>
        /* تنسيق أزرار التحكم في الكمية */
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            border-radius: 6px;
            padding: 2px;
            border: 1px solid #e9ecef;
        }

        .qty-btn {
            width: 28px;
            height: 28px;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #495057;
            transition: all 0.2s ease;
        }

        .qty-btn:hover {
            background: #e9ecef;
            border-color: #adb5bd;
        }

        .qty-btn:active {
            transform: scale(0.95);
        }

        .quantity-display {
            min-width: 30px;
            text-align: center;
            font-weight: 600;
            color: #212529;
            font-size: 14px;
        }

        /* تأثيرات التحميل */
        .tf-mini-cart-item.loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .tf-mini-cart-item.loading .quantity-controls {
            position: relative;
        }

        .tf-mini-cart-item.loading .quantity-controls::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            border-top-color: #007bff;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* تأثير الحذف */
        .tf-mini-cart-item.removing {
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
        }

        .sser {
            display: none;
            transform: translateX(20px);
            transition: all 0.3s ease;
        }

        /* تحسين شكل أيقونة الحذف */
        .remove_from_cart_button {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: #f8f9fa;
            color: #6c757d;
            transition: all 0.2s ease;
        }

        .remove_from_cart_button:hover {
            background: #dc3545;
            color: #fff;
        }

        /* تحسين عرض السعر */
        .item-price {
            margin-left: 10px;
        }

        .tf-mini-card-price {
            font-weight: 600;
        }

        /* تحسين التصميم العام */
        .tf-mini-cart-item {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.2s ease;
        }

        .tf-mini-cart-item:hover {
            background: #f8f9fa;
        }

        .tf-mini-cart-item:last-child {
            border-bottom: none;
        }

        /* تحسين التصميم المتجاوب */
        @media (max-width: 576px) {
            .quantity-controls {
                gap: 6px;
            }

            .qty-btn {
                width: 24px;
                height: 24px;
                font-size: 12px;
            }

            .quantity-display {
                min-width: 25px;
                font-size: 12px;
            }
        }
    </style>
    <script>
        
        jQuery(function ($) {
            var cartNonce = '<?php echo $cart_nonce; ?>';

            // التأكد من وجود BlockUI قبل استخدامه
            if (typeof $.fn.block === 'undefined') {
                // إضافة dummy functions للـ block و unblock
                $.fn.block = function (options) {
                    return this.each(function () {
                        var $this = $(this);
                        $this.addClass('loading');
                        if (options && options.message) {
                            $this.append('<div class="blockUI" style="position:absolute;top:0;left:0;width:100%;height:100%;background:rgba(255,255,255,0.8);z-index:1000;display:flex;align-items:center;justify-content:center;">' + options.message + '</div>');
                        }
                    });
                };

                $.fn.unblock = function () {
                    return this.each(function () {
                        var $this = $(this);
                        $this.removeClass('loading');
                        $this.find('.blockUI').remove();
                    });
                };
            }

            // دالة تحديث الأرقام في الهيدر والسايدبار
            function updateCartDisplay(data) {
                // تحديث fragments إذا كانت موجودة
                if (data.fragments) {
                    $.each(data.fragments, function (key, value) {
                        $(key).replaceWith(value);
                    });
                }

                // تحديث عداد المنتجات في جميع الأماكن (backup)
                $('.prd-count, .cart-count, .count-number, .count').text(data.cart_count);

                // تحديث الإجمالي
                $('.tf-totals-total-value').html(data.cart_subtotal);
                $('.tf-totals-total-value2').html(data.cart_subtotal);
                $('.discon').html(data.discount_total);
                $('.total-mer').html(data.cart_subtotal);

                // تحديث الخصم
                if (data.discount_percent !== undefined) {
                    $('.total.h6').text(data.discount_percent + '%');
                }
                // console.log(data);

                // تحديث نص العدد في الـ subtotal
                var itemText = data.cart_count == 1 ? 'item' : 'items';
                $('.subtotal').html('Subtotal (<span class="prd-count">' + data.cart_count + '</span> ' + itemText + ')');

                // إظهار/إخفاء منطقة الأزرار
                if (data.is_empty) {
                    $('.tf-mini-cart-bottom').hide();
                } else {
                    $('.tf-mini-cart-bottom').show();
                }
            }

            // دالة تحديث محتويات السلة
            function refreshCartContents() {
                $.post(ajaxurl, {
                    action: 'refresh_cart_contents',
                    nonce: cartNonce
                }, function (response) {
                    if (response.success) {
                        $('.tf-mini-cart-items').replaceWith(response.data.content);
                        updateCartDisplay(response.data);
                    }
                });
            }

            // عند إضافة منتج للسلة
            $('body').on('added_to_cart', function (event, fragments, cart_hash, $button) {
                // تحديث عداد السلة
                if (fragments) {
                    $.each(fragments, function (key, value) {
                        $(key).replaceWith(value);
                    });
                }

                // فتح الـ sidebar
                const offcanvas = document.getElementById('shoppingCart');
                if (offcanvas && typeof bootstrap !== 'undefined') {
                    const bsOffcanvas = new bootstrap.Offcanvas(offcanvas);
                    bsOffcanvas.show();
                }

                // تأثير على الزر
                if ($button && $button.length) {
                    $button.addClass('added').delay(1000).queue(function (next) {
                        $(this).removeClass('added');
                        next();
                    });
                }

                // تحديث محتويات السلة
                refreshCartContents();
            });

            // حذف منتج من السلة
            $(document).on('click', '.remove_from_cart_button', function (e) {
                e.preventDefault();

                var $button = $(this);
                var $item = $button.closest('.tf-mini-cart-item');
                var $item2 = $button.closest('.tf-cart_item');
                var key = $button.data('cart_item_key');

                $item.addClass('loading');

                $.post(ajaxurl, {
                    action: 'remove_cart_item',
                    cart_item_key: key,
                    nonce: cartNonce
                }, function (response) {
                    if (response.success) {
                        $item.addClass('removing');
                        setTimeout(function () {
                            updateCartDisplay(response.data);
                            if (response.data.is_empty) {
                                refreshCartContents();
                            } else {
                                $item.remove();
                            }
                        }, 300);
                        $item2.addClass('sser');
                        setTimeout(function () {
                            updateCartDisplay(response.data);
                            if (response.data.is_empty) {
                                refreshCartContents();
                            } else {
                                $item2.remove();
                            }
                        }, 300);
                    } else {
                        toastr.error(response.data.message || 'حدث خطأ أثناء حذف المنتج');
                        $item.removeClass('loading');
                    }
                }).fail(function () {
                    toastr.error('حدث خطأ في الاتصال');
                    $item.removeClass('loading');
                });
            });

            // تعديل الكمية
            $(document).on('click', '.qty-btn', function (e) {
                e.preventDefault();

                var $btn = $(this);
                var $item = $btn.closest('.tf-mini-cart-item');
                var key = $item.data('cart_item_key');
                var $display = $item.find('.quantity-display');
                var currentQty = parseInt($display.text(), 10);
                var action = $btn.data('action');
                var newQty;

                if (action === 'increase') {
                    newQty = currentQty + 1;
                } else {
                    newQty = Math.max(currentQty - 1, 0);
                }

                if (newQty === currentQty) return;

                $item.addClass('loading');

                $.post(ajaxurl, {
                    action: 'update_cart_quantity',
                    cart_item_key: key,
                    quantity: newQty,
                    nonce: cartNonce
                }, function (response) {
                    if (response.success) {
                        if (response.data.action === 'removed') {
                            $item.addClass('removing');
                            setTimeout(function () {
                                updateCartDisplay(response.data);
                                if (response.data.is_empty) {
                                    refreshCartContents();
                                } else {
                                    $item.remove();
                                }
                            }, 300);
                        } else {
                            // تحديث الكمية والسعر
                            $display.text(response.data.new_quantity);
                            if (response.data.item_price) {
                                $item.find('.tf-mini-card-price').html(response.data.item_price);
                            }
                            $item.removeClass('loading');
                            updateCartDisplay(response.data);
                        }
                    } else {
                        toastr.error(response.data.message || 'حدث خطأ أثناء تحديث الكمية');
                        $item.removeClass('loading');
                    }
                }).fail(function () {
                    toastr.error('حدث خطأ في الاتصال');
                    $item.removeClass('loading');
                });
            });

            // تحديث السلة عند تحديث الصفحة
            $(document).ready(function () {
                // تحديث الأرقام عند تحميل الصفحة
                refreshCartContents();
            });

            // إضافة تأثير loading للصفحة عند إضافة منتج
            $(document).on('click', '.single_add_to_cart_button', function () {
                var $button = $(this);
                $button.addClass('loading');

                // إزالة الـ loading بعد 3 ثوانٍ كحد أقصى
                setTimeout(function () {
                    $button.removeClass('loading');
                }, 3000);
            });

            // إزالة loading عند اكتمال العملية
            $('body').on('added_to_cart', function () {
                $('.single_add_to_cart_button').removeClass('loading');
            });

        });
    </script>
    <?php
}

/* ============================================================================
 *  WooCommerce Custom Page-Title + Breadcrumb
 * ========================================================================== */

remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
add_action('woocommerce_before_main_content', 'tf_page_title_breadcrumb', 5);

function tf_page_title_breadcrumb()
{
    $delimiter = '<li class="d-flex"><i class="icon icon-caret-right"></i></li>';
    $lango = pll_current_language();
    $home_label = $lango == 'ar' ? 'الرئيسية' : 'Home';
    $home_url = home_url('/');
    $items = array();

    $items[] = sprintf(
        '<li><a href="%s" class="h6 link">%s</a></li>',
        esc_url($home_url),
        esc_html($home_label)
    );

    if (is_shop() && !is_product()) {
        $items[] = '<li><h6 class="current-page fw-normal">' . esc_html(woocommerce_page_title(false)) . '</h6></li>';
    } elseif (is_product_category() || is_product_tag()) {
        $term = get_queried_object();
        $parents = get_ancestors($term->term_id, $term->taxonomy);

        foreach (array_reverse($parents) as $parent_id) {
            $parent = get_term($parent_id, $term->taxonomy);
            $items[] = sprintf(
                '<li><a href="%s" class="h6 link">%s</a></li>',
                esc_url(get_term_link($parent)),
                esc_html($parent->name)
            );
        }
        $items[] = '<li><h6 class="current-page fw-normal">' . esc_html($term->name) . '</h6></li>';
    } elseif (is_product()) {
        $cats = wc_get_product_terms(get_the_ID(), 'product_cat', array(
            'orderby' => 'parent',
            'order' => 'ASC',
        ));

        if ($cats) {
            $cat = $cats[0];
            $parents = get_ancestors($cat->term_id, 'product_cat');

            foreach (array_reverse($parents) as $parent_id) {
                $parent = get_term($parent_id, 'product_cat');
                $items[] = sprintf(
                    '<li><a href="%s" class="h6 link">%s</a></li>',
                    esc_url(get_term_link($parent)),
                    esc_html($parent->name)
                );
            }
            $items[] = sprintf(
                '<li><a href="%s" class="h6 link">%s</a></li>',
                esc_url(get_term_link($cat)),
                esc_html($cat->name)
            );
        }
        
        $items[] = '<li><h6 class="current-page fw-normal">' . esc_html(get_the_title()) . '</h6></li>';
    } else {
        $items[] = '<li><h6 class="current-page fw-normal">' . esc_html(get_the_title()) . '</h6></li>';
    }

    echo '<section class="s-page-title style-2"><div class="container"><div class="content">';
    echo '<ul class="breadcrumbs-page">';
    echo implode($delimiter, $items);
    echo '</ul>';
    echo '</div></div></section>';
}

// إضافة CSS للـ loading states
add_action('wp_head', 'add_loading_styles');
function add_loading_styles()
{
    ?>
    <style>
        .single_add_to_cart_button.loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .single_add_to_cart_button.loading::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 0.8s linear infinite;
        }

        .single_add_to_cart_button.added {
            background-color: #28a745 !important;
            border-color: #28a745 !important;
        }
    </style>
    <?php
}


/* ============================================================================
 *  WooCommerce  ➜  Custom Page-Title + Breadcrumb
 *  يطبع نفس الـ Markup اللى فوق بكل الحالات
 * ========================================================================== */

/* 1) احذف الـ breadcrumb الأصلى */
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);


// Quick View
// إضافة أكشن للـ quick view
add_action('wp_ajax_product_quick_view', 'handle_product_quick_view');
add_action('wp_ajax_nopriv_product_quick_view', 'handle_product_quick_view');

function handle_product_quick_view()
{
    $product_id = intval($_POST['product_id']);

    if (!$product_id) {
        wp_die('Invalid product ID');
    }

    $product = wc_get_product($product_id);

    if (!$product) {
        wp_die('Product not found');
    }

    // جلب البيانات المطلوبة
    $product_data = array(
        'id' => $product->get_id(),
        'name' => $product->get_name(),
        'price' => $product->get_price(),
        'regular_price' => $product->get_regular_price(),
        'sale_price' => $product->get_sale_price(),
        'description' => $product->get_short_description(),
        'rating' => $product->get_average_rating(),
        'review_count' => $product->get_review_count(),
        'stock_quantity' => $product->get_stock_quantity(),
        'permalink' => $product->get_permalink(),
        'images' => array(),
        'attributes' => array(),
        'variations' => array()
    );

    // جلب الصور
    $attachment_ids = $product->get_gallery_image_ids();
    array_unshift($attachment_ids, $product->get_image_id());

    foreach ($attachment_ids as $attachment_id) {
        $product_data['images'][] = array(
            'src' => wp_get_attachment_image_url($attachment_id, 'full'),
            'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true)
        );
    }

    // جلب الخصائص (للمنتجات المتغيرة)
    if ($product->is_type('variable')) {
        $attributes = $product->get_variation_attributes();
        foreach ($attributes as $attribute_name => $terms) {
            $product_data['attributes'][$attribute_name] = $terms;
        }

        // جلب المتغيرات
        $variations = $product->get_available_variations();
        $product_data['variations'] = $variations;
    }

    // حساب الخصم
    if ($product->is_on_sale()) {
        $regular_price = floatval($product->get_regular_price());
        $sale_price = floatval($product->get_sale_price());
        $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
        $product_data['discount_percentage'] = $discount_percentage;
    }

    wp_send_json_success($product_data);
}

// إضافة زر Quick View في قائمة المنتجات
add_action('woocommerce_after_shop_loop_item', 'add_quick_view_button', 15);

function add_quick_view_button()
{
    global $product;

    echo '<button class="quick-view-btn" data-product-id="' . $product->get_id() . '" data-bs-toggle="modal" data-bs-target="#quickView">';
    echo '<i class="icon icon-eye"></i> Quick View';
    echo '</button>';
}

// إضافة الـ modal HTML في الـ footer
add_action('wp_footer', 'add_quick_view_modal');

function add_quick_view_modal()
{
    // if (is_shop() || is_product_category() || is_product_tag()) {
    ?>
    <div class="modal modalCentered fade modal-quick-view" id="quickView">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <i class="icon icon-close icon-close-popup" data-bs-dismiss="modal"></i>
                <div class="modal-loading">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div class="modal-body-content" style="display: none;">
                    <div class="tf-product-media-wrap tf-btn-swiper-item">
                        <div dir="ltr" class="swiper tf-single-slide">
                            <div class="swiper-wrapper" id="product-images">
                                <!-- الصور ستضاف هنا ديناميكياً -->
                            </div>
                        </div>
                    </div>
                    <div class="tf-product-info-wrap">
                        <div class="tf-product-info-inner tf-product-info-list">
                            <div class="tf-product-info-heading">
                                <a href="#" class="link product-info-name fw-medium h1" id="product-name">
                                    <!-- اسم المنتج -->
                                </a>
                                <div class="product-info-meta">
                                    <div class="rating" id="product-rating">
                                        <!-- التقييم -->
                                    </div>
                                    <div class="people-add text-primary" id="stock-info">
                                        <!-- معلومات المخزون -->
                                    </div>
                                </div>
                                <div class="product-info-price">
                                    <div class="price-wrap" id="product-price">
                                        <!-- السعر -->
                                    </div>
                                </div>
                                <p class="product-infor-sub text-main h6" id="product-description">
                                    <!-- الوصف -->
                                </p>
                            </div>
                            <div class="tf-product-variant w-100" id="product-variants">
                                <!-- الخصائص والمتغيرات -->
                            </div>
                            <div class="tf-product-total-quantity w-100">
                                <div class="group-btn">
                                    <div class="wg-quantity">
                                        <button class="btn-quantity btn-decrease">
                                            <i class="icon icon-minus"></i>
                                        </button>
                                        <input class="quantity-product" type="number" name="quantity" value="1" min="1">
                                        <button class="btn-quantity btn-increase">
                                            <i class="icon icon-plus"></i>
                                        </button>
                                    </div>
                                    <p class="h6 d-none d-sm-block" id="availability-info">
                                        <!-- معلومات التوفر -->
                                    </p>
                                </div>
                                <div class="group-btn flex-sm-nowrap">
                                    <button class="tf-btn animate-btn btn-add-to-cart" id="add-to-cart-btn">
                                        ADD TO CART
                                        <i class="icon icon-shopping-cart-simple"></i>
                                    </button>
                                    <button type="button" class="hover-tooltip box-icon btn-add-wishlist flex-sm-shrink-0">
                                        <span class="icon icon-heart"></span>
                                        <span class="tooltip">Add to Wishlist</span>
                                    </button>
                                </div>
                            </div>
                            <a href="#" class="tf-btn-line text-normal letter-space-0 fw-normal" id="view-details-link">
                                <span class="h5">View full details</span>
                                <i class="icon icon-arrow-top-right fs-24"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    // }
}

// إضافة الـ JavaScript والـ CSS
add_action('wp_enqueue_scripts', 'enqueue_quick_view_assets');

function enqueue_quick_view_assets()
{
    // if (is_shop() || is_product_category() || is_product_tag()) {
    wp_enqueue_script('quick-view-js', get_template_directory_uri() . '/js/quick-view.js', array('jquery'), '1.0', true);
    wp_enqueue_style('quick-view-css', get_template_directory_uri() . '/js/quick-view.css', array(), '1.0');

    // إضافة متغيرات JavaScript
    wp_localize_script('quick-view-js', 'quickview_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('quickview_nonce')
    ));
    // }
}

// إضافة أكشن لإضافة المنتج للسلة عبر AJAX
add_action('wp_ajax_add_to_cart_quick_view', 'handle_add_to_cart_quick_view');
add_action('wp_ajax_nopriv_add_to_cart_quick_view', 'handle_add_to_cart_quick_view');

function handle_add_to_cart_quick_view()
{
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    $variation_id = intval($_POST['variation_id']);

    if ($variation_id) {
        $result = WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
    } else {
        $result = WC()->cart->add_to_cart($product_id, $quantity);
    }

    if ($result) {
        wp_send_json_success(array(
            'message' => 'Product added to cart successfully',
            'cart_count' => WC()->cart->get_cart_contents_count()
        ));
    } else {
        wp_send_json_error('Failed to add product to cart');
    }
}

// Fav
/* =========================================================
 *  WooCommerce AJAX Search  ➜  Shortcode
 * ======================================================= */

// 1. تسجيل سكربت الـ AJAX وجعل رابط الـ admin-ajax متاح في الجافاسكربت

// إضافة AJAX actions
add_action('wp_ajax_wc_search_products', 'wc_ajax_search_products');
add_action('wp_ajax_nopriv_wc_search_products', 'wc_ajax_search_products');

function wc_ajax_search_products()
{
    $lango = pll_current_language();
    $term = sanitize_text_field($_POST['term']);
    $category = sanitize_text_field($_POST['category']);

    if (empty($term)) {
        wp_die();
    }

    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => 10,
        's' => $term,
        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            )
        )
    );

    // إضافة فلتر الفئة إذا تم تحديدها
    if (!empty($category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category
            )
        );
    }

    $products = new WP_Query($args);

    if ($products->have_posts()) {
        echo '<div class="search-results-container">';

        while ($products->have_posts()) {
            $products->the_post();
            global $product;

            $product_id = get_the_ID();
            $product_title = get_the_title();
            $product_url = get_permalink();
            $product_price = $product->get_price_html();
            $product_image = get_the_post_thumbnail_url($product_id, 'thumbnail');
            $product_categories = wp_get_post_terms($product_id, 'product_cat');

            echo '<div class="search-result-item" data-product-id="' . $product_id . '">';
            echo '<div class="product-image" style="width: 220px;">';
            if ($product_image) {
                echo '<img src="' . $product_image . '" alt="' . $product_title . '">';
            } else {
                echo '<div class="no-image">No Image</div>';
            }
            echo '</div>';
            echo '<div class="product-info">';
            echo '<h4 class="product-title">' . $product_title . '</h4>';
            echo '<div class="product-price">' . $product_price . '</div>';
            if (!empty($product_categories)) {
                echo '<div class="product-category">' . $product_categories[0]->name . '</div>';
            }
            echo '</div>';
            echo '<a href="' . $product_url . '" class="view-product">' . ($lango == 'ar' ? 'عرض المنتج' : 'View Product') . '</a>';
            echo '</div>';
        }

        // رابط لعرض جميع النتائج
        $search_url = home_url('/shop/') . '?s=' . urlencode($term);
        if (!empty($category)) {
            $search_url .= '&product_cat=' . $category;
        }

        echo '<div class="view-all-results">';
        echo '<a href="' . $search_url . '" class="btn-view-all">' . ($lango == 'ar' ? 'عرض جميع النتائج' : 'View All Results') . ' (' . $products->found_posts . ')</a>';
        echo '</div>';

        echo '</div>';
    } else {
        echo '<div class="no-results">' . ($lango == 'ar' ? 'لا توجد نتائج للبحث' : 'No results found for the search') . '</div>';
    }

    wp_reset_postdata();
    wp_die();
}

// إضافة الستايل والجافا سكريبت
add_action('wp_enqueue_scripts', 'wc_search_assets');
function wc_search_assets()
{
    wp_enqueue_script('wc-ajax-search', get_template_directory_uri() . '/js/wc-ajax-search.js', array('jquery'), '1.0', true);
    wp_localize_script('wc-ajax-search', 'wc_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wc_search_nonce')
    ));
}

// تعديل صفحة البحث لدعم الفئات
add_action('pre_get_posts', 'modify_search_query');
function modify_search_query($query)
{
    if (!is_admin() && $query->is_main_query() && is_shop() && isset($_GET['s'])) {
        $query->set('post_type', 'product');
        $query->set('s', sanitize_text_field($_GET['s']));

        if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
            $query->set('tax_query', array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => sanitize_text_field($_GET['product_cat'])
                )
            ));
        }
    }
}

// Filter
add_action('wp_enqueue_scripts', 'enqueue_ajax_filter_assets');
function enqueue_ajax_filter_assets()
{
    wp_enqueue_script(
        'ajax-filter',
        get_stylesheet_directory_uri() . '/js/ajax-filter.js',
        array('jquery'),
        '1.0',
        true
    );
    // إذا إحنا في صفحة أرشيف تصنيف المنتج، خُذ الـ slug
    $current_cat = '';
    if (is_tax('product_cat')) {
        $term = get_queried_object();
        if (isset($term->slug)) {
            $current_cat = $term->slug;
        }
    }
    wp_localize_script('ajax-filter', 'filter_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('filter_nonce'),
        'current_cat' => $current_cat
    ));
}

// دالة AJAX لتحديث العدادات ديناميكياً
add_action('wp_ajax_update_filter_counts', 'update_filter_counts');
add_action('wp_ajax_nopriv_update_filter_counts', 'update_filter_counts');

function update_filter_counts()
{
    check_ajax_referer('filter_nonce', 'nonce');

    // استقبال الفلاتر الحالية
    $cats = isset($_POST['categories']) ? array_map('sanitize_text_field', (array) $_POST['categories']) : [];
    $brands = isset($_POST['brands']) ? array_map('sanitize_text_field', (array) $_POST['brands']) : [];
    $sale_only = isset($_POST['sale_only']) ? sanitize_text_field($_POST['sale_only']) : false;
    $availability = isset($_POST['availability']) ? sanitize_text_field($_POST['availability']) : '';

    // بناء الـ base query
    $base_args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => [],
        'meta_query' => []
    ];

    // إضافة فلاتر الكاتيجوريز
    if (!empty($cats)) {
        $base_args['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $cats,
        ];
    }

    // إضافة فلاتر البراندز
    if (!empty($brands)) {
        $base_args['tax_query'][] = [
            'taxonomy' => 'product_brand',
            'field' => 'slug',
            'terms' => $brands,
        ];
    }

    // إضافة فلتر التخفيضات
    if ($sale_only === 'true') {
        $base_args['meta_query'][] = [
            'key' => '_sale_price',
            'value' => '',
            'compare' => '!='
        ];
    }

    // إضافة فلتر التوفر
    if ($availability === 'inStock') {
        $base_args['meta_query'][] = [
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '='
        ];
    } elseif ($availability === 'outStock') {
        $base_args['meta_query'][] = [
            'key' => '_stock_status',
            'value' => 'outofstock',
            'compare' => '='
        ];
    }

    // إضافة relations
    if (count($base_args['tax_query']) > 1) {
        $base_args['tax_query']['relation'] = 'AND';
    }
    if (count($base_args['meta_query']) > 1) {
        $base_args['meta_query']['relation'] = 'AND';
    }

    // حساب عدد المنتجات المتوفرة
    $stock_args = $base_args;
    $stock_args['meta_query'][] = [
        'key' => '_stock_status',
        'value' => 'instock',
        'compare' => '='
    ];
    if (count($stock_args['meta_query']) > 1) {
        $stock_args['meta_query']['relation'] = 'AND';
    }
    $instock_count = (new WP_Query($stock_args))->found_posts;

    // حساب عدد المنتجات غير المتوفرة
    $outstock_args = $base_args;
    $outstock_args['meta_query'][] = [
        'key' => '_stock_status',
        'value' => 'outofstock',
        'compare' => '='
    ];
    if (count($outstock_args['meta_query']) > 1) {
        $outstock_args['meta_query']['relation'] = 'AND';
    }
    $outstock_count = (new WP_Query($outstock_args))->found_posts;

    // حساب عدد المنتجات المخفضة
    $sale_args = $base_args;
    $sale_args['meta_query'][] = [
        'key' => '_sale_price',
        'value' => '',
        'compare' => '!='
    ];
    if (count($sale_args['meta_query']) > 1) {
        $sale_args['meta_query']['relation'] = 'AND';
    }
    $sale_count = (new WP_Query($sale_args))->found_posts;

    // إرجاع النتائج
    wp_send_json_success([
        'instock_count' => $instock_count,
        'outstock_count' => $outstock_count,
        'sale_count' => $sale_count
    ]);
}

function get_price_range()
{
    global $wpdb;
    $min = $wpdb->get_var(
        "SELECT MIN(meta_value+0) FROM {$wpdb->postmeta} WHERE meta_key = '_price'"
    );
    $max = $wpdb->get_var(
        "SELECT MAX(meta_value+0) FROM {$wpdb->postmeta} WHERE meta_key = '_price'"
    );

    // نقرب للأسفل وللأعلى، ثم ننسّقهم بمنزلتين عشريتين
    $min_formatted = number_format(floor((float) $min), 2, '.', '');
    $max_formatted = number_format(ceil((float) $max), 2, '.', '');

    return array(
        'min' => $min_formatted,
        'max' => $max_formatted,
    );
}
// غير رمز عملة SAR من 'ر.س' إلى 'SAR'
add_filter('woocommerce_currency_symbol', function ($symbol, $currency) {
    if ('SAR' === $currency) {
        return '<img src="' . get_template_directory_uri() . '/assets/images/sar.svg" width="15" height="15" alt="SAR">';
    }
    return $symbol;
}, 10, 2);

// 1.2. تسجيل السكريبتات وlocalize للـ AJAX URL والـ nonce
function custom_price_filter_scripts()
{
    // jQuery UI slider
    wp_enqueue_script('jquery-ui-slider');
    wp_enqueue_style(
        'jquery-ui-css',
        'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css'
    );

    // سكريبت الفلتر
    wp_enqueue_script(
        'custom-price-filter',
        get_stylesheet_directory_uri() . '/js/custom-price-filter.js',
        array('jquery', 'jquery-ui-slider'),
        '1.0',
        true
    );

    // بيانات نمررها للـ JS
    $range = get_price_range();
    wp_localize_script('custom-price-filter', 'price_filter_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('price_filter_nonce'),
        'currency_symbol' => html_entity_decode(get_woocommerce_currency_symbol()),
        'min_price' => $range['min'],
        'max_price' => $range['max'],
    ));
}
add_action('wp_enqueue_scripts', 'custom_price_filter_scripts');
// ربط الهاندلر للـ AJAX لمستخدمين مسجلين ولمسجلين

add_action('wp_ajax_filter_products_by_price', 'filter_products_by_price');
add_action('wp_ajax_nopriv_filter_products_by_price', 'filter_products_by_price');

function filter_products_by_price()
{
    // تأكد من الـ nonce
    check_ajax_referer('price_filter_nonce', 'nonce');

    // جلب القيم من الـ POST
    $min = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
    $max = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 0;

    // إعداد الكويري
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 12,
        'paged' => isset($_POST['page']) ? intval($_POST['page']) : 1,
        'meta_query' => array(
            array(
                'key' => '_price',
                'value' => array($min, $max),
                'compare' => 'BETWEEN',
                'type' => 'NUMERIC',
            ),
        ),
    );
    $q = new WP_Query($args);

    if ($q->have_posts()) {
        ob_start();
        while ($q->have_posts()) {
            $q->the_post();

            global $product;
            // احصل على معلومات المنتج
            $product_id = get_the_ID();
            $product_title = get_the_title();
            $product_price = $product->get_price();
            $product_regular_price = $product->get_regular_price();
            $product_sale_price = $product->get_sale_price();
            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
            $product_gallery = $product->get_gallery_image_ids();
            $product_stock_status = $product->get_stock_status();
            $product_brands = wp_get_post_terms($product_id, 'product_brand');
            $product_permalink = get_permalink($product_id);

            // صورة ثانية من المعرض
            $hover_image = '';
            if (!empty($product_gallery)) {
                $hover_image = wp_get_attachment_image_src($product_gallery[0], 'medium');
            } ?>

            <div class="card-product grid" data-availability="<?php echo $product_stock_status; ?>"
                data-brand="<?php echo !empty($product_brands) ? $product_brands[0]->slug : ''; ?>">
                <div class="card-product_wrapper">
                    <a href="<?php echo $product_permalink; ?>" class="product-img"
                        style="position: relative; display: block; overflow: hidden;">
                        <?php if ($product_image): ?>
                            <img class="lazyload img-product main-product-image-<?php echo $product_id; ?>"
                                src="<?php echo $product_image[0]; ?>" alt="<?php echo $product_title; ?>"
                                style="width: 100%; height: auto; display: block;">
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
                    // التحقق من وجود ألوان المنتج
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

                    // لا نحتاج لتهيئة أول لون عند تحميل الصفحة
                    // نترك الصورة الأصلية ظاهرة حتى يتم اختيار لون
                });
            </script>

            <?php
        }
        wp_reset_postdata();
        $html = ob_get_clean();
    } else {
        $html = '<p class="no-results">لا توجد منتجات ضمن هذا النطاق السعري.</p>';
    }

    wp_send_json_success(array('html' => $html));
}

// Stop Woo Style
add_action( 'wp_enqueue_scripts', 'dequeue_woocommerce_styles', 20 );
function dequeue_woocommerce_styles() {
    // تعطيل ستايل العام
    wp_dequeue_style( 'woocommerce-general' );
    // تعطيل ستايل الـ layout
    wp_dequeue_style( 'woocommerce-layout' );
    // تعطيل ستايل الـ smalldown
    wp_dequeue_style( 'woocommerce-smallscreen' );
}

// Woo Class
add_filter('woocommerce_checkout_fields', 'remove_woocommerce_default_checkout_classes');

function remove_woocommerce_default_checkout_classes($fields) {
    foreach ($fields as $fieldset => &$field_group) {
        foreach ($field_group as $key => &$field) {
            // امسح كل الكلاسات
            if (isset($field['class'])) {
                $field['class'] = array(); // مسح الكلاسات
            }

            if (isset($field['input_class'])) {
                $field['input_class'] = array(); // مسح الكلاسات الخاصة بالـ input نفسه
            }

            if (isset($field['label_class'])) {
                $field['label_class'] = array(); // مسح كلاسات الليبل
            }

            // اختياري: امسح الـ custom wrapper class
            if (isset($field['custom_attributes'])) {
                unset($field['custom_attributes']['class']);
            }
        }
    }
    return $fields;
}

// Color
function get_unique_product_colors()
{
    $unique_colors = array();
    $products = wc_get_products(array(
        'status' => 'publish',
        'limit' => -1,
    ));

    foreach ($products as $product) {
        $product_id = $product->get_id();

        if (have_rows('product_attributes3', $product_id)) {
            while (have_rows('product_attributes3', $product_id)) {
                the_row();
                $color_name = get_sub_field('color_name');
                $color_code = get_sub_field('color_code');
                $color_image = get_sub_field('color_image');

                if (!empty($color_name)) {
                    $color_key = sanitize_title($color_name);

                    if (!isset($unique_colors[$color_key])) {
                        $unique_colors[$color_key] = array(
                            'name' => $color_name,
                            'code' => $color_code,
                            'image' => $color_image,
                            'count' => 0,
                            'product_ids' => array()
                        );
                    }

                    if (!in_array($product_id, $unique_colors[$color_key]['product_ids'])) {
                        $unique_colors[$color_key]['count']++;
                        $unique_colors[$color_key]['product_ids'][] = $product_id;
                    }
                }

            }
        } 
        
    }

    return $unique_colors;
}

add_action('wp_ajax_filter_products_by_color', 'filter_products_by_color');
add_action('wp_ajax_nopriv_filter_products_by_color', 'filter_products_by_color');

function filter_products_by_color()
{
    $color_name = isset($_POST['color_name']) ? sanitize_text_field($_POST['color_name']) : '';
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', explode(',', $_POST['product_ids'])) : array();

    $args = array(
        'post_type' => 'product',
        'post__in' => $product_ids,
        'posts_per_page' => -1,
        'orderby' => 'post__in'
    );

    $products = new WP_Query($args);

    ob_start();

    if ($products->have_posts()) {
        while ($products->have_posts()) {
            $products->the_post();
            global $product;

            // احصل على معلومات المنتج
            $product_id = get_the_ID();
            $product_title = get_the_title();
            $product_price = $product->get_price();
            $product_regular_price = $product->get_regular_price();
            $product_sale_price = $product->get_sale_price();
            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
            $product_gallery = $product->get_gallery_image_ids();
            $product_stock_status = $product->get_stock_status();
            $product_brands = wp_get_post_terms($product_id, 'product_brand');
            $product_permalink = get_permalink($product_id);

            // صورة ثانية من المعرض
            $hover_image = '';
            if (!empty($product_gallery)) {
                $hover_image = wp_get_attachment_image_src($product_gallery[0], 'medium');
            }
            ?>

            <div class="card-product grid" data-availability="<?php echo $product_stock_status; ?>"
                data-brand="<?php echo !empty($product_brands) ? $product_brands[0]->slug : ''; ?>">
                <div class="card-product_wrapper">
                    <a href="<?php echo $product_permalink; ?>" class="product-img"
                        style="position: relative; display: block; overflow: hidden;">
                        <?php if ($product_image): ?>
                            <img class="lazyload img-product main-product-image-<?php echo $product_id; ?>"
                                src="<?php echo $product_image[0]; ?>" alt="<?php echo $product_title; ?>"
                                style="width: 100%; height: auto; display: block;">
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

                    // لا نحتاج لتهيئة أول لون عند تحميل الصفحة
                    // نترك الصورة الأصلية ظاهرة حتى يتم اختيار لون
                });
            </script>
            <?php
        }
        wp_reset_postdata();
    } else {
        echo '<p>No products found</p>';
    }

    $output = ob_get_clean();

    wp_send_json_success(array(
        'html' => $output,
        'count' => $products->found_posts
    ));
}

// Sale Rule

// Auth
// add_action( 'woocommerce_created_customer', function( $customer_id ) {

//     if ( isset( $_POST['first_name'] ) ) {
//         update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
//     }

//     if ( isset( $_POST['last_name'] ) ) {
//         update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
//     }

//     if ( isset( $_POST['billing_phone'] ) ) {
//         update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
//     }

//     if ( isset( $_POST['email'] ) && ! empty( $_POST['email'] ) ) {
//         wp_update_user( array(
//             'ID'         => $customer_id,
//             'user_email' => sanitize_email( $_POST['email'] ),
//         ) );
//     }
// });

// add_filter( 'woocommerce_registration_errors', function( $errors, $username, $email ) {
//     // لو الإيميل فاضي، ما نضيفش خطأ
//     if ( empty( $_POST['email'] ) ) {
//         $errors->remove( 'woocommerce_registration_error_email_required' );
//     }
//     return $errors;
// }, 10, 3 );

// // ما نطلبش الإيميل في الفاليديشن
// add_filter( 'woocommerce_process_registration_errors', function( $errors, $username, $password, $email ) {
//     return $errors;
// }, 10, 4 );

// // login
// // 1. تمرير المتغيرات JavaScript المطلوبة
// add_action('wp_enqueue_scripts', function () {
//     if (is_page_template('page-custom-auth.php')) { // غير اسم التمبلت حسب ملفك
//         wp_localize_script('jquery', 'WCAuth', array(
//             'ajax' => admin_url('admin-ajax.php'),
//             'loginNonce' => wp_create_nonce('wc_custom_login'),
//             'otpNonce' => wp_create_nonce('wc_custom_otp'),
//         ));
//     }
// });

// // 2. معالج تسجيل الدخول عبر كلمة المرور (AJAX)
// add_action('wp_ajax_wc_custom_login', 'handle_custom_login');
// add_action('wp_ajax_nopriv_wc_custom_login', 'handle_custom_login');

// function handle_custom_login()
// {
//     // التحقق من الـ nonce
//     if (!wp_verify_nonce($_POST['security'], 'wc_custom_login')) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'Security check failed')
//         )));
//     }

//     $username = sanitize_text_field($_POST['username']);
//     $password = $_POST['password'];
//     $remember = !empty($_POST['rememberme']);

//     // التحقق من صحة البيانات
//     if (empty($username) || empty($password)) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'Username and password are required')
//         )));
//     }

//     // محاولة تسجيل الدخول
//     $creds = array(
//         'user_login' => $username,
//         'user_password' => $password,
//         'remember' => $remember,
//     );

//     $user = wp_signon($creds, false);

//     if (is_wp_error($user)) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => $user->get_error_message())
//         )));
//     }

//     // تسجيل الدخول نجح
//     wp_set_current_user($user->ID);

//     // تحديد رابط التوجيه
//     $redirect_url = wc_get_page_permalink('my-account-2');
//     if (!$redirect_url) {
//         $redirect_url = home_url('/my-account-2/'); // fallback
//     }

//     wp_die(json_encode(array(
//         'success' => true,
//         'data' => array(
//             'message' => 'Login successful',
//             'redirect' => $redirect_url
//         )
//     )));
// }

// // 3. معالج إرسال OTP لتسجيل الدخول
// add_action('wp_ajax_wc_send_login_otp', 'handle_send_login_otp');
// add_action('wp_ajax_nopriv_wc_send_login_otp', 'handle_send_login_otp');

// function handle_send_login_otp()
// {
//     if (!wp_verify_nonce($_POST['security'], 'wc_custom_otp')) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'Security check failed')
//         )));
//     }

//     $phone = sanitize_text_field($_POST['phone']);

//     if (empty($phone)) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'Phone number is required')
//         )));
//     }

//     // البحث عن المستخدم بالموبايل
//     $users = get_users(array(
//         'meta_key' => 'billing_phone',
//         'meta_value' => $phone,
//         'number' => 1
//     ));

//     if (empty($users)) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'No account found with this phone number')
//         )));
//     }

//     // توليد OTP وحفظه
//     $otp_code = sprintf('%04d', rand(1000, 9999));
//     $user_id = $users[0]->ID;

//     // حفظ الكود مع انتهاء صالحية (5 دقائق)
//     update_user_meta($user_id, 'login_otp_code', $otp_code);
//     update_user_meta($user_id, 'login_otp_expires', time() + (5 * 60));

//     // هنا يجب إرسال الـ OTP عبر SMS
//     // للتجربة سنرجع الكود في الاستجابة

//     wp_die(json_encode(array(
//         'success' => true,
//         'data' => array(
//             'message' => 'OTP sent successfully',
//             'dev' => $otp_code // احذف هذا في الإنتاج
//         )
//     )));
// }

// // 4. معالج التحقق من OTP لتسجيل الدخول
// add_action('wp_ajax_wc_verify_login_otp', 'handle_verify_login_otp');
// add_action('wp_ajax_nopriv_wc_verify_login_otp', 'handle_verify_login_otp');

// function handle_verify_login_otp()
// {
//     if (!wp_verify_nonce($_POST['security'], 'wc_custom_otp')) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'Security check failed')
//         )));
//     }

//     $phone = sanitize_text_field($_POST['phone']);
//     $code = sanitize_text_field($_POST['code']);

//     if (empty($phone) || empty($code)) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'Phone and OTP code are required')
//         )));
//     }

//     // البحث عن المستخدم
//     $users = get_users(array(
//         'meta_key' => 'billing_phone',
//         'meta_value' => $phone,
//         'number' => 1
//     ));

//     if (empty($users)) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'Invalid phone number')
//         )));
//     }

//     $user = $users[0];
//     $saved_code = get_user_meta($user->ID, 'login_otp_code', true);
//     $expires = get_user_meta($user->ID, 'login_otp_expires', true);

//     // التحقق من الكود وانتهاء الصالحية
//     if (empty($saved_code) || $saved_code !== $code) {
//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'Invalid OTP code')
//         )));
//     }

//     if (time() > $expires) {
//         // حذف الكود المنتهي الصالحية
//         delete_user_meta($user->ID, 'login_otp_code');
//         delete_user_meta($user->ID, 'login_otp_expires');

//         wp_die(json_encode(array(
//             'success' => false,
//             'data' => array('message' => 'OTP code has expired')
//         )));
//     }

//     // تسجيل الدخول
//     wp_set_current_user($user->ID);
//     wp_set_auth_cookie($user->ID, true);

//     // حذف الكود بعد الاستخدام
//     delete_user_meta($user->ID, 'login_otp_code');
//     delete_user_meta($user->ID, 'login_otp_expires');

//     // تحديد رابط التوجيه
//     $redirect_url = wc_get_page_permalink('my-account-2');
//     if (!$redirect_url) {
//         $redirect_url = home_url('/my-account-2/');
//     }

//     wp_die(json_encode(array(
//         'success' => true,
//         'data' => array(
//             'message' => 'Login successful',
//             'redirect' => $redirect_url
//         )
//     )));
// }

// // 5. إنشاء صفحة my-account-2 إذا لم تكن موجودة
// add_action('init', function () {
//     // التحقق من وجود الصفحة
//     $page = get_page_by_path('my-account-2');

//     if (!$page) {
//         // إنشاء الصفحة
//         wp_insert_post(array(
//             'post_title' => 'My Account 2',
//             'post_name' => 'my-account-2',
//             'post_type' => 'page',
//             'post_status' => 'publish',
//             'post_content' => '[woocommerce_my_account]', // أو أي محتوى تريده
//         ));
//     }
// });

// Redirect logged-in users away from login/my-account page to Home
// add_action('template_redirect', function () {
//     // متعملش أي تحويل في AJAX/REST
//     if ( wp_doing_ajax() || ( defined('REST_REQUEST') && REST_REQUEST ) ) {
//         return;
//     }

//     // لو مش عامل لوجين.. ما نعملش حاجة
//     if ( ! is_user_logged_in() ) {
//         return;
//     }

//     // 1) صفحة مخصّصة /login (غيّر الـ slug لو مختلف)
//     if ( is_page('login') ) {
//         wp_safe_redirect( home_url('/') );
//         exit;
//     }

//     // 2) لو بتستخدم صفحة Woo "My Account" كصفحة الدخول
//     //    امنع الدخول لها وهي بدون أي endpoint (يعني شاشة اللوجين/الداشبورد)،
//     //    لكن اسمح بباقي الأقسام (orders/edit-account/edit-address/..)
//     if ( function_exists('is_account_page') && is_account_page() ) {
//         // لو صفحة "تسجيل الخروج" استثناء
//         if ( function_exists('is_wc_endpoint_url') && is_wc_endpoint_url('customer-logout') ) {
//             return;
//         }

//         // لو مفيش أي endpoint (يعني فتح /my-account فقط) → رجّعه للهوم
//         if ( function_exists('is_wc_endpoint_url') && ! is_wc_endpoint_url() ) {
//             wp_safe_redirect( home_url('/') );
//             exit;
//         }
//     }
// });

// Auth Registration Handler
add_action('woocommerce_created_customer', function($customer_id) {
    // حفظ البيانات الإضافية
    if (isset($_POST['first_name'])) {
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['first_name']));
    }

    if (isset($_POST['last_name'])) {
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['last_name']));
    }

    // دمج كود البلد مع رقم الهاتف
    if (isset($_POST['country_code']) && isset($_POST['billing_phone'])) {
        $country_code = sanitize_text_field($_POST['country_code']);
        $phone = sanitize_text_field($_POST['billing_phone']);
        $full_phone = $country_code . $phone;
        
        update_user_meta($customer_id, 'billing_phone', $full_phone);
    }

    // تحديث الإيميل إذا تم إدخاله
    if (isset($_POST['email']) && !empty($_POST['email'])) {
        wp_update_user(array(
            'ID' => $customer_id,
            'user_email' => sanitize_email($_POST['email']),
        ));
    }

    // تسجيل دخول تلقائي بعد التسجيل
    wp_set_current_user($customer_id);
    wp_set_auth_cookie($customer_id);
});

// إزالة متطلب الإيميل
add_filter('woocommerce_registration_errors', function($errors, $username, $email) {
    if (empty($_POST['email'])) {
        $errors->remove('woocommerce_registration_error_email_required');
    }
    return $errors;
}, 10, 3);

// تخصيص معالجة التسجيل
add_filter('woocommerce_process_registration_errors', function($errors, $username, $password, $email) {
    // التحقق من الاسم الأول
    if (empty($_POST['first_name'])) {
        $errors->add('first_name_required', 'First name is required');
    }
    
    // التحقق من الاسم الأخير
    if (empty($_POST['last_name'])) {
        $errors->add('last_name_required', 'Last name is required');
    }
    
    // التحقق من رقم الهاتف
    if (empty($_POST['billing_phone'])) {
        $errors->add('phone_required', 'Phone number is required');
    }

    return $errors;
}, 10, 4);

// تحديد اسم المستخدم من رقم الهاتف
// add_filter('woocommerce_new_customer_data', function($customer_data) {
//     if (isset($_POST['country_code']) && isset($_POST['billing_phone'])) {
//         $country_code = sanitize_text_field($_POST['country_code']);
//         $phone = sanitize_text_field($_POST['billing_phone']);
//         $full_phone = $country_code . $phone;
        
//         // استخدام رقم الهاتف كاسم مستخدم
//         $customer_data['user_login'] = $full_phone;
        
//         // إذا لم يتم إدخال إيميل، استخدم رقم الهاتف كإيميل مؤقت
//         if (empty($customer_data['user_email'])) {
//             $customer_data['user_email'] = $full_phone . '@temp.local';
//         }
//     }
    
//     return $customer_data;
// });

// إضافة redirect بعد التسجيل الناجح
add_action('woocommerce_registration_redirect', function($redirect_to) {
    return home_url('/my-account-2/');
});

// تمرير متغيرات JavaScript
add_action('wp_enqueue_scripts', function() {
    if (is_page_template('page-custom-auth.php')) {
        wp_localize_script('jquery', 'WCAuth', array(
            'ajax' => admin_url('admin-ajax.php'),
            'loginNonce' => wp_create_nonce('wc_custom_login'),
            'otpNonce' => wp_create_nonce('wc_custom_otp'),
        ));
    }
});

// معالج تسجيل الدخول عبر كلمة المرور (AJAX)
add_action('wp_ajax_wc_custom_login', 'handle_custom_login');
add_action('wp_ajax_nopriv_wc_custom_login', 'handle_custom_login');

function handle_custom_login() {
    if (!wp_verify_nonce($_POST['security'], 'wc_custom_login')) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'Security check failed')
        )));
    }

    $username = sanitize_text_field($_POST['username']);
    $password = $_POST['password'];
    $remember = !empty($_POST['rememberme']);

    if (empty($username) || empty($password)) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'Username and password are required')
        )));
    }

    // محاولة تسجيل الدخول
    $creds = array(
        'user_login' => $username,
        'user_password' => $password,
        'remember' => $remember,
    );

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'Invalid username or password')
        )));
    }

    // تسجيل الدخول نجح
    wp_set_current_user($user->ID);

    wp_die(json_encode(array(
        'success' => true,
        'data' => array(
            'message' => 'Login successful',
            'redirect' => home_url('/my-account-2/')
        )
    )));
}

// معالج إرسال OTP لتسجيل الدخول
add_action('wp_ajax_wc_send_login_otp', 'handle_send_login_otp');
add_action('wp_ajax_nopriv_wc_send_login_otp', 'handle_send_login_otp');

function handle_send_login_otp() {
    if (!wp_verify_nonce($_POST['security'], 'wc_custom_otp')) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'Security check failed')
        )));
    }

    $phone = sanitize_text_field($_POST['phone']);

    if (empty($phone)) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'Phone number is required')
        )));
    }

    // البحث عن المستخدم بالموبايل
    $users = get_users(array(
        'meta_key' => 'billing_phone',
        'meta_value' => $phone,
        'number' => 1
    ));

    if (empty($users)) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'No account found with this phone number')
        )));
    }

    // توليد OTP وحفظه
    $otp_code = sprintf('%04d', rand(1000, 9999));
    $user_id = $users[0]->ID;

    // حفظ الكود مع انتهاء صالحية (5 دقائق)
    update_user_meta($user_id, 'login_otp_code', $otp_code);
    update_user_meta($user_id, 'login_otp_expires', time() + (5 * 60));

    wp_die(json_encode(array(
        'success' => true,
        'data' => array(
            'message' => 'OTP sent successfully',
            'dev' => $otp_code // احذف هذا في الإنتاج
        )
    )));
}

// معالج التحقق من OTP لتسجيل الدخول
add_action('wp_ajax_wc_verify_login_otp', 'handle_verify_login_otp');
add_action('wp_ajax_nopriv_wc_verify_login_otp', 'handle_verify_login_otp');

function handle_verify_login_otp() {
    if (!wp_verify_nonce($_POST['security'], 'wc_custom_otp')) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'Security check failed')
        )));
    }

    $phone = sanitize_text_field($_POST['phone']);
    $code = sanitize_text_field($_POST['code']);

    if (empty($phone) || empty($code)) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'Phone and OTP code are required')
        )));
    }

    $users = get_users(array(
        'meta_key' => 'billing_phone',
        'meta_value' => $phone,
        'number' => 1
    ));

    if (empty($users)) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'Invalid phone number')
        )));
    }

    $user = $users[0];
    $saved_code = get_user_meta($user->ID, 'login_otp_code', true);
    $expires = get_user_meta($user->ID, 'login_otp_expires', true);

    if (empty($saved_code) || $saved_code !== $code) {
        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'Invalid OTP code')
        )));
    }

    if (time() > $expires) {
        delete_user_meta($user->ID, 'login_otp_code');
        delete_user_meta($user->ID, 'login_otp_expires');

        wp_die(json_encode(array(
            'success' => false,
            'data' => array('message' => 'OTP code has expired')
        )));
    }

    // تسجيل الدخول
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID, true);

    // حذف الكود بعد الاستخدام
    delete_user_meta($user->ID, 'login_otp_code');
    delete_user_meta($user->ID, 'login_otp_expires');

    wp_die(json_encode(array(
        'success' => true,
        'data' => array(
            'message' => 'Login successful',
            'redirect' => home_url('/my-account-2/')
        )
    )));
}

// إنشاء صفحة my-account-2 إذا لم تكن موجودة
add_action('init', function() {
    $page = get_page_by_path('my-account-2');
    
    if (!$page) {
        wp_insert_post(array(
            'post_title' => 'My Account 2',
            'post_name' => 'my-account-2',
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_content' => '[woocommerce_my_account]',
        ));
    }
});

// 6. إضافة زر تسجيل الدخول بـ OTP في HTML (اختياري)
// أضف هذا في الـ template بعد زر Login العادي:
/*
<button type="button" id="btnLoginOtp" class="tf-btn w-100 mt-2" style="background: #28a745;">
    Login with OTP
</button>
*/

// // Tag Test
// add_action('woocommerce_product_query', 'filter_shop_products_by_category_parameter');
// function filter_shop_products_by_category_parameter($q)
// {
//     // التأكد من أننا في صفحة المتجر وليس في admin panel
//     if (!is_admin() && is_shop() && $q->is_main_query()) {

//         // التحقق من وجود parameter "product_cat" في الرابط
//         if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
//             $cat_id = intval($_GET['product_cat']);

//             if ($cat_id > 0) {
//                 // إضافة tax_query لفلترة المنتجات حسب التصنيف
//                 $tax_query = $q->get('tax_query');
//                 if (!is_array($tax_query)) {
//                     $tax_query = array();
//                 }

//                 $tax_query[] = array(
//                     'taxonomy' => 'product_cat',
//                     'field' => 'term_id',
//                     'terms' => $cat_id,
//                 );

//                 $q->set('tax_query', $tax_query);
//             }
//         }
//     }
// }

// // إضافة عنوان مخصص لصفحة المتجر عند الفلترة بالتصنيف
// add_filter('woocommerce_page_title', 'custom_shop_page_title_with_category');
// function custom_shop_page_title_with_category($page_title)
// {
//     if (is_shop() && isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
//         $cat_id = intval($_GET['product_cat']);
//         $category_term = get_term($cat_id, 'product_cat');

//         if ($category_term && !is_wp_error($category_term)) {
//             return $category_term->name . ' Products';
//         }
//     }
//     return $page_title;
// }

// // إضافة وصف مخصص للصفحة عند الفلترة
// add_action('woocommerce_archive_description', 'custom_shop_description_with_tag');
// function custom_shop_description_with_tag()
// {
//     if (is_shop() && isset($_GET['tag']) && !empty($_GET['tag'])) {
//         $tag_id = intval($_GET['tag']);
//         $category_term = get_term($tag_id, 'product_cat');

//         if ($category_term && !is_wp_error($category_term)) {
//             echo '<div class="category-filter-notice">';
//             echo '<p>عرض المنتجات من تصنيف: <strong>' . esc_html($category_term->name) . '</strong></p>';
//             echo '<a href="' . esc_url(wc_get_page_permalink('shop')) . '" class="btn btn-sm btn-outline-primary">عرض جميع المنتجات</a>';
//             echo '</div>';
//         }
//     }
// }

// // تحسين SEO للصفحة المفلترة
// add_filter('document_title_parts', 'custom_shop_seo_title_with_tag');
// function custom_shop_seo_title_with_tag($title)
// {
//     if (is_shop() && isset($_GET['tag']) && !empty($_GET['tag'])) {
//         $tag_id = intval($_GET['tag']);
//         $category_term = get_term($tag_id, 'product_cat');

//         if ($category_term && !is_wp_error($category_term)) {
//             $title['title'] = $category_term->name . ' Products - ' . get_bloginfo('name');
//         }
//     }
//     return $title;
// }

// // إضافة كلاس CSS مخصص للصفحة المفلترة
// add_filter('body_class', 'add_shop_tag_filter_body_class');
// function add_shop_tag_filter_body_class($classes)
// {
//     if (is_shop() && isset($_GET['tag']) && !empty($_GET['tag'])) {
//         $classes[] = 'shop-filtered-by-tag';
//         $classes[] = 'tag-' . intval($_GET['tag']);
//     }
//     return $classes;
// }


// // 7. معالج تسجيل الدخول العادي عبر WooCommerce (للنموذج العادي)
// add_action('template_redirect', function () {
//     if (is_page_template('page-custom-auth.php') && isset($_POST['login'])) {
//         $username = sanitize_text_field($_POST['username']);
//         $password = $_POST['password'];
//         $remember = !empty($_POST['rememberme']);

//         if (empty($username) || empty($password)) {
//             wc_add_notice('Username and password are required', 'error');
//             return;
//         }

//         $creds = array(
//             'user_login' => $username,
//             'user_password' => $password,
//             'remember' => $remember,
//         );

//         $user = wp_signon($creds, false);

//         if (is_wp_error($user)) {
//             wc_add_notice($user->get_error_message(), 'error');
//             return;
//         }

//         // التوجيه بعد تسجيل الدخول الناجح
//         $redirect_url = wc_get_page_permalink('my-account-2');
//         if (!$redirect_url) {
//             $redirect_url = home_url('/my-account-2/');
//         }

//         wp_safe_redirect($redirect_url);
//         exit;
//     }
// });


// 2. أكشن AJAX
add_action('wp_ajax_filter_products', 'filter_products');
add_action('wp_ajax_nopriv_filter_products', 'filter_products');

function filter_products()
{
    check_ajax_referer('filter_nonce', 'nonce');

    // استقبل مصفوفات الـ checkboxes
    $cats = isset($_POST['categories']) ? array_map('sanitize_text_field', (array) $_POST['categories']) : [];
    $brands = isset($_POST['brands']) ? array_map('sanitize_text_field', (array) $_POST['brands']) : [];
    $sale_only = isset($_POST['sale_only']) ? sanitize_text_field($_POST['sale_only']) : false;
    $availability = isset($_POST['availability']) ? sanitize_text_field($_POST['availability']) : '';
    $tags = isset($_POST['tags']) ? array_map('sanitize_text_field', (array) $_POST['tags']) : [];

    $args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => []
    ]; 

    // إضافة فلتر للمنتجات المخفضة فقط
    if ($sale_only === 'true') {
        $args['meta_query'] = [
            [
                'key' => '_sale_price',
                'value' => '',
                'compare' => '!='
            ]
        ];
    }

    // إضافة فلتر للتوفر
    if ($availability === 'inStock') {
        $args['meta_query'][] = [
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '='
        ];
    } elseif ($availability === 'outStock') {
        $args['meta_query'][] = [
            'key' => '_stock_status',
            'value' => 'outofstock',
            'compare' => '='
        ];
    }

    // إضافة relation للـ meta_query إذا كان هناك أكثر من شرط
    if (isset($args['meta_query']) && count($args['meta_query']) > 1) {
        $args['meta_query']['relation'] = 'AND';
    }

    if (!empty($cats)) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_cat',
            'field' => 'slug',
            'terms' => $cats,
        ];
    }

    if (!empty($brands)) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_brand',
            'field' => 'slug',
            'terms' => $brands,
        ];
    }

    if (!empty($tags)) {
        $args['tax_query'][] = [
            'taxonomy' => 'product_tag',
            'field' => 'term_id',
            'terms' => $tags,
        ];
    }

    if (count($args['tax_query']) > 1) {
        $args['tax_query']['relation'] = 'AND';
    }
    $rule_id = isset($_GET['wcd_rule']) ? intval($_GET['wcd_rule']) : 0;

    // echo wcd_get_rule_products_data($rule_id);

    $q = new WP_Query($args);

    if ($q->have_posts()) {
        while ($q->have_posts()) {
            $q->the_post();
            global $product;

            // احصل على معلومات المنتج
            $product_id = get_the_ID();
            $product_title = get_the_title();
            $product_price = $product->get_price();
            $product_regular_price = $product->get_regular_price();
            $product_sale_price = $product->get_sale_price();
            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');
            $product_gallery = $product->get_gallery_image_ids();
            $product_stock_status = $product->get_stock_status();
            $product_brands = wp_get_post_terms($product_id, 'product_brand');
            $product_permalink = get_permalink($product_id);

            // صورة ثانية من المعرض
            $hover_image = '';
            if (!empty($product_gallery)) {
                $hover_image = wp_get_attachment_image_src($product_gallery[0], 'medium');
            }
            ?>

            <div class="card-product grid" data-availability="<?php echo $product_stock_status; ?>"
                data-brand="<?php echo !empty($product_brands) ? $product_brands[0]->slug : ''; ?>">
                <div class="card-product_wrapper">
                    <a href="<?php echo $product_permalink; ?>" class="product-img"
                        style="position: relative; display: block; overflow: hidden;">
                        <?php if ($product_image): ?>
                            <img class="lazyload img-product main-product-image-<?php echo $product_id; ?>"
                                src="<?php echo $product_image[0]; ?>" alt="<?php echo $product_title; ?>"
                                style="width: 100%; height: auto; display: block;">
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
                    // التحقق من وجود ألوان المنتج
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

                    <?php
                    // الحصول على التاجات الخاصة بالمنتج
                    $product_tags = wp_get_post_terms(get_the_ID(), 'product_tag');

                    if (!empty($product_tags) && !is_wp_error($product_tags)): ?>
                        <ul class="product-badge_list">
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
                                    case 'New arrival':
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
                                    case 'Hot':
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
                        </ul>
                    <?php endif; ?>
             
                    
                    <?php
                    if ( $product instanceof WC_Product ) {
                        $regular_price = (float) $product->get_regular_price();
                        $sale_price    = (float) $product->get_sale_price();
                    
                        // لو فيه خصم فعلاً
                        if ( $sale_price > 0 && $regular_price > 0 && $sale_price < $regular_price ) {
                            $discount_percent = round( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
                    
                            if ( $discount_percent > 25 ) {
                                // هنا نحط الماركي بتاعك
                                ?>
                                <div class="product-marquee_sale">
                                    <div class="marquee-wrapper">
                                        <div class="initial-child-container">
                                            <div class="marquee-child-item">
                                                <span class="icon">🔥</span>
                                            </div>
                                            <div class="marquee-child-item">
                                                <p class="text-small">TOP PRODUCT SALE OFF <?php echo esc_html( $discount_percent ); ?>%</p>
                                            </div>
                                            <div class="marquee-child-item">
                                                <span class="icon">🔥</span>
                                            </div>
                                            <div class="marquee-child-item">
                                                <p class="text-small">TOP PRODUCT SALE OFF <?php echo esc_html( $discount_percent ); ?>%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                    }
                    ?>
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
    } else {
        echo '<p class="text-center my-4">لا توجد منتجات مطابقة.</p>';
    }
    ?>
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

    wp_die();
}

// Single Product 
add_action('wp_ajax_submit_product_question', 'handle_product_question');
add_action('wp_ajax_nopriv_submit_product_question', 'handle_product_question');

function handle_product_question()
{
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'wc_add_to_cart_nonce')) {
        wp_die('Security check failed');
    }

    // Sanitize input
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $phone = sanitize_text_field($_POST['phone']);
    $message = sanitize_textarea_field($_POST['message']);
    $product_id = intval($_POST['product_id']);

    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        wp_send_json_error(array('message' => 'Please fill in all required fields.'));
    }

    // Validate email
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
    }

    // Get product details
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array('message' => 'Product not found.'));
    }

    // Prepare email content
    $subject = 'Product Question - ' . $product->get_name();
    $headers = array('Content-Type: text/html; charset=UTF-8');

    $email_content = "<h3>New Product Question</h3>";
    $email_content .= "<p><strong>Product:</strong> " . $product->get_name() . "</p>";
    $email_content .= "<p><strong>Name:</strong> " . $name . "</p>";
    $email_content .= "<p><strong>Email:</strong> " . $email . "</p>";
    $email_content .= "<p><strong>Phone:</strong> " . $phone . "</p>";
    $email_content .= "<p><strong>Message:</strong><br>" . nl2br($message) . "</p>";

    // Send email to admin
    $admin_email = get_option('admin_email');
    $email_sent = wp_mail($admin_email, $subject, $email_content, $headers);

    if ($email_sent) {
        wp_send_json_success(array('message' => 'Your question has been sent successfully!'));
    } else {
        wp_send_json_error(array('message' => 'Failed to send your question. Please try again.'));
    }
}

add_action('wp_ajax_get_order_details', 'tf_get_order_details_ajax');
// لو عايز تسمح للي مش لوجين (مش لازم هنا):
// add_action('wp_ajax_nopriv_get_order_details', 'tf_get_order_details_ajax');

function tf_get_order_details_ajax()
{
    // تحقق من النونس بطريقة وردبرس المختصرة
    // check_ajax_referer('get_order_details_nonce', 'nonce'); // لو غلط → يموت برسالة JSON

    $order_id = isset($_POST['order_id']) ? (int) $_POST['order_id'] : 0;
    $order = wc_get_order($order_id);

    if (!$order || $order->get_customer_id() !== get_current_user_id()) {
        wp_send_json_error(['message' => 'Order not found'], 404);
    }

    ob_start();
    // لازم تكون الدالة دي متعرفة هنا برضه (أو متضمَّنة require_once)
    tf_render_account_order_detail_block($order);
    $html = ob_get_clean();

    wp_send_json_success(['html' => $html]);
}
function tf_render_account_order_detail_block(WC_Order $order)
{
    $items = $order->get_items();
    $first_item = $items ? reset($items) : null;
    $product = $first_item ? $first_item->get_product() : null;

    $status = $order->get_status();
    $status_text = wc_get_order_status_name($status);
    $status_ar = [
        'pending' => 'قيد الانتظار',       
        'processing' => 'جاري المعالجة',      
        'on-hold' => 'معلق',               
        'completed' => 'مكتمل',              
        'cancelled' => 'ملغي',               
        'refunded' => 'مسترد',              
        'failed' => 'فشل الدفع',          
    ];
    $status_map = [
        'pending' => 'bg-warning',
        'processing' => 'bg-primary',
        'on-hold' => 'bg-secondary',
        'completed' => 'bg-success',
        'cancelled' => 'bg-danger',
        'refunded' => 'bg-info',
        'failed' => 'bg-dark',
    ];
    $status_class = isset($status_map[$status]) ? $status_map[$status] : 'bg-secondary';

    $image_url = '';
    if ($product) {
        $img = wp_get_attachment_image_src($product->get_image_id(), 'large');
        $image_url = $img ? $img[0] : wc_placeholder_img_src();
    } else {
        $image_url = wc_placeholder_img_src();
    }

    $product_name = $product ? $product->get_name() : 'Product';
    $regular_price = $product ? (float) $product->get_regular_price() : 0;
    $sale_price = $product ? (float) $product->get_sale_price() : 0;
    $order_date = $order->get_date_created() ? $order->get_date_created()->date_i18n('F j, Y - H:i') : '';

    $shipping = $order->get_address('shipping');
    $one_line_address = '';
    if (!empty($shipping['first_name']) || !empty($shipping['address_1'])) {
        $one_line_address = trim(sprintf(
            '%s %s, %s, %s %s, %s',
            $shipping['address_1'] ?: '',
            $shipping['address_2'] ?: '',
            $shipping['city'] ?: '',
            $shipping['state'] ?: '',
            $shipping['postcode'] ?: '',
            $shipping['country'] ?: ''
        ));
    } else {
        $billing = $order->get_address('billing');
        $one_line_address = trim(sprintf(
            '%s %s, %s, %s %s, %s',
            $billing['address_1'] ?: '',
            $billing['address_2'] ?: '',
            $billing['city'] ?: '',
            $billing['state'] ?: '',
            $billing['postcode'] ?: '',
            $billing['country'] ?: ''
        ));
    }

    $shipping_methods = $order->get_shipping_methods();
    $carrier_title = $shipping_methods ? reset($shipping_methods)->get_name() : '-';

    $notes = wc_get_order_notes(['order_id' => $order->get_id(), 'type' => 'internal']);

    $lengo = pll_current_language();
    ?>
        <div class="account-order_detail">
            <div class="order-detail_image">
                <img class="lazyload"
                     src="<?php echo esc_url($image_url); ?>"
                     data-src="<?php echo esc_url($image_url); ?>"
                     alt="<?php echo esc_attr($product_name); ?>">
            </div>
            <div class="order-detail_content tf-grid-layout">
                <div class="detail-content_info">
                    <div class="detail-info_status h6 <?php echo esc_attr($status_class); ?>">
                        <?= $lengo == 'ar' ? $status_ar[$status] : $status_text;?>
                    </div>
                    <div class="detail-info_prd">
                        <p class="prd_name h4 text-black"><?php echo esc_html($product_name); ?></p>
                        <div class="price-wrap">
                            <?php if ($sale_price && $sale_price < $regular_price): ?>
                                    <span class="price-old h6 fw-normal"><?php echo wc_price($regular_price); ?></span>
                                    <span class="price-new h6 text-main fw-semibold"><?php echo wc_price($sale_price); ?></span>
                            <?php elseif ($regular_price): ?>
                                    <span class="price-new h6 text-main fw-semibold"><?php echo wc_price($regular_price); ?></span>
                            <?php else: ?>
                                    <span class="h6 fw-normal"><?php echo $order->get_formatted_order_total(); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="detail-info_item">
                        <p class="info-item_label"><?= $lengo == 'ar' ? 'التصنيف' : 'Category' ?></p>
                        <p class="info-item_value">
                            <?php
                            if ($product) {
                                $cats = get_the_terms($product->get_id(), 'product_cat');
                                foreach ($cats as $cat) {
                                    $ar_term_id = function_exists('pll_get_term') ? pll_get_term($cat->term_id, 'ar') : 0;
                                    $en_term_id = function_exists('pll_get_term') ? pll_get_term($cat->term_id, 'en') : 0;
                                    $ar_term = $ar_term_id ? get_term($ar_term_id, 'product_cat') : $cat;
                                    $en_term = $en_term_id ? get_term($en_term_id, 'product_cat') : $cat;
                                    if ($ar_term && !is_wp_error($ar_term)) {
                                        echo $lengo == 'ar' ? $ar_term->name : $en_term->name . ', ';
                                    }
                                }
                            } else {
                                echo '—';
                            }
                            ?>
                        </p>
                    </div>

                    <div class="detail-info_item">
                        <p class="info-item_label"><?= $lengo == 'ar' ? 'التاريخ' : 'Order date' ?></p>
                        <p class="info-item_value"><?php echo esc_html($order_date); ?></p>
                    </div>


                    <div class="detail-info_item">
                        <p class="info-item_label"><?= $lengo == 'ar' ? 'العنوان' : 'Address' ?></p>
                        <p class="info-item_value"><?php echo esc_html($one_line_address ?: '-'); ?></p>
                    </div>
                </div>

                <span class="br-line d-flex"></span>

                <div>
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="tf-btn style-line">
                        <?= $lengo == 'ar' ? 'العودة إلى المتجر' : 'Back to Store' ?>
                        <i class="icon icon-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="account-order_tab">
            <ul class="tab-order_detail" role="tablist">
                <li class="nav-tab-item" role="presentation">
                    <a href="#order-history" data-bs-toggle="tab" class="tf-btn-line tf-btn-tab active" aria-selected="true" role="tab">
                        <span class="h4"><?= $lengo == 'ar' ? 'تاريخ الطلبات' : 'Order history' ?></span>
                    </a>
                </li>
                <li class="nav-tab-item" role="presentation">
                    <a href="#item-detail" data-bs-toggle="tab" class="tf-btn-line tf-btn-tab" role="tab">
                        <span class="h4"><?= $lengo == 'ar' ? 'تفاصيل العنصر' : 'Item details' ?></span>
                    </a>
                </li>
                <li class="nav-tab-item" role="presentation">
                    <a href="#receiver" data-bs-toggle="tab" class="tf-btn-line tf-btn-tab" role="tab">
                        <span class="h4"><?= $lengo == 'ar' ? 'تفاصيل المستلم' : 'Receiver details' ?></span>
                    </a>
                </li>
            </ul>

            <div class="tab-content overflow-hidden">
                <!-- Order history -->
                <div class="tab-pane active show" id="order-history" role="tabpanel">
                    <div class="order-timeline">
                        <?php if ($notes): ?>
                                <?php foreach ($notes as $n): ?>
                                        <div class="timeline-step completed">
                                            <div class="timeline_icon">
                                                <span class="icon"><i class="icon-check-1"></i></span>
                                            </div>
                                            <div class="timeline_content">
                                                <h5 class="step-title fw-semibold">
                                                    <?php //echo esc_html(wp_strip_all_tags($n->content)); ?>

                                                    <?php echo $lengo == 'ar' ? $status_ar[$status] : $status_text;?>
                                                </h5>
                                                <h6 class="step-date fw-normal">
                                                    <?php echo esc_html(date_i18n('F j, Y - H:i', strtotime($n->date_created))); ?>
                                                </h6>
                                            </div>
                                        </div>
                                <?php endforeach; ?>
                        <?php else: ?>
                                <div class="timeline-step">
                                    <div class="timeline_icon">
                                        <span class="icon"><i class="icon-check-1"></i></span>
                                    </div>
                                    <div class="timeline_content">
                                        <h5 class="step-title fw-semibold">Order placed</h5>
                                        <h6 class="step-date fw-normal mb-0"><?php echo esc_html($order_date); ?></h6>
                                    </div>
                                </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Item details -->
                <div class="tab-pane" id="item-detail" role="tabpanel">
                    <?php
                    $total_discounts = $order->get_total_discount();
                    ?>
                    <?php foreach ($order->get_items() as $item): ?>
                            <?php $p = $item->get_product();
                            if (!$p)
                                continue; ?>
                            <div class="order-item_detail">
                                <div class="prd-info">
                                    <div class="info_image">
                                        <?php
                                        $img = wp_get_attachment_image_src($p->get_image_id(), 'thumbnail');
                                        $pimg = $img ? $img[0] : wc_placeholder_img_src();
                                        ?>
                                        <img class="lazyload" src="<?php echo esc_url($pimg); ?>" data-src="<?php echo esc_url($pimg); ?>" alt="Product">
                                    </div>
                                    <div class="info_detail">
                                        <a href="<?php echo get_permalink($p->get_id()); ?>" class="link info-name h4"><?php echo esc_html($p->get_name()); ?></a>
                                        <p class="info-price">Price: <span class="fw-semibold h6 text-black"><?php echo wc_price($p->get_price()); ?></span></p>
                                        <?php
                                        $meta = $item->get_formatted_meta_data();
                                        if ($meta) {
                                            foreach ($meta as $m) {
                                                echo '<p class="info-variant">' . esc_html($m->display_key) . ': <span class="fw-semibold h6 text-black">' . esc_html($m->display_value) . '</span></p>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="prd-price d-none">
                                    <div class="price_total">
                                        <span>Total price:</span>
                                        <span class="fw-semibold h6 text-black">
                                            <?php echo $order->get_formatted_line_subtotal($item); ?>
                                        </span>
                                    </div>
                                    <p class="price_dis">
                                        <span>Total discounts:</span>
                                        <span class="fw-semibold h6 text-black">
                                            <?php echo $total_discounts ? wc_price($total_discounts) : wc_price(0); ?>
                                        </span>
                                    </p>
                                </div>
                                <!-- <div class="prd-order_total mb-4 border-bottom pb-4">
                                    <span>Order total</span>
                                    <span class="fw-semibold h6 text-black"><?php //echo $order->get_formatted_order_total(); ?></span>
                                </div> -->
                            </div>
                         
                    <?php endforeach; ?>
                </div>

                <!-- Courier -->
                <div class="tab-pane" id="courier" role="tabpanel">
                    <p class="h6 text-courier h6">
                        <?php echo esc_html($carrier_title ?: '—'); ?>
                    </p>
                </div>

                <!-- Receiver -->
                <div class="tab-pane" id="receiver" role="tabpanel">
                    <div class="order-receiver">
                        <div class="recerver_text h6">
                            <span class="text"><?php echo $lengo == 'ar' ? 'رقم الطلب' : 'Order Number:' ?></span>
                            <span class="text_info">#<?php echo $order->get_order_number(); ?></span>
                        </div>
                        <div class="recerver_text h6">
                            <span class="text"><?php echo $lengo == 'ar' ? 'التاريخ' : 'Date:' ?></span>
                            <span class="text_info"><?php echo esc_html($order_date); ?></span>
                        </div>
                        <div class="recerver_text h6">
                            <span class="text"><?php echo $lengo == 'ar' ? 'المجموع' : 'Total:' ?></span>
                            <span class="text_info"><?php echo $order->get_formatted_order_total(); ?></span>
                        </div>
                        <div class="recerver_text h6">
                            <span class="text"><?php echo $lengo == 'ar' ? 'طريقة الدفع' : 'Payment Methods:' ?></span>
                            <span class="text_info"><?php echo esc_html($order->get_payment_method_title() ?: '—'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="order-actions mt-3">
            <a href="#" class="btn btn-secondary js-back-to-orders"><?= $lengo == 'ar' ? 'العودة إلى الطلبات' : 'Back to Orders' ?></a>
            <?php if (in_array($status, ['pending', 'on-hold'])): ?>
                    <a href="#" class="btn btn-warning"
                       onclick="cancelOrder(<?php echo $order->get_id(); ?>);return false;"><?= $lengo == 'ar' ? 'الإلغاء' : 'Cancel Order' ?></a>
            <?php endif; ?>
        </div>
        <?php
}

function my_account_assets()
{
    // حمّل سكربتك (عدّل المسار حسب مشروعك)
    wp_enqueue_script(
        'my-account-js',
        get_stylesheet_directory_uri() . '/js/my-account.js',
        [],
        null,
        true
    );

    // مرّر بيانات AJAX للسكربت
    wp_localize_script('my-account-js', 'myAccountAjax', [
        'ajaxUrl' => admin_url('admin-ajax.php'),                
        'nonce' => wp_create_nonce('get_order_details_nonce'),
        'lengo' => pll_current_language(),
    ]);
}
add_action('wp_enqueue_scripts', 'my_account_assets');


// تحسين select field للمنتجات المرتبطة بناءً على التصنيف
add_filter('acf/fields/post_object/query', 'filter_related_products_by_category', 10, 3);

function filter_related_products_by_category($args, $field, $post_id)
{

    // التحقق من أن هذا هو الحقل المطلوب
    if ($field['name'] !== 'link') {
        return $args;
    }

    // التحقق من أن المنتج الحالي موجود
    if (!$post_id || get_post_type($post_id) !== 'product') {
        return $args;
    }

    // الحصول على تصنيفات المنتج الحالي
    $current_categories = wp_get_post_terms($post_id, 'product_cat', array('fields' => 'ids'));

    if (!empty($current_categories)) {
        // إضافة tax_query لعرض المنتجات من نفس التصنيف فقط
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $current_categories,
                'operator' => 'IN'
            )
        );

        // استبعاد المنتج الحالي من النتائج
        $args['post__not_in'] = array($post_id);

        // ترتيب النتائج
        $args['orderby'] = 'title';
        $args['order'] = 'ASC';

        // تحديد عدد النتائج المعروضة
        $args['posts_per_page'] = 50;
    }

    return $args;
}


// Product Timer
require_once get_template_directory() . '/func/filter-wo.php';


