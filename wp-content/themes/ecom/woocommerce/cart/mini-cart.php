<div class="offcanvas offcanvas-end popup-shopping-cart" id="shoppingCart">

<?php
$lango = pll_current_language();

// ===== إعدادات بسيطة =====
$limit = 4;                 // عدد العناصر
$cache_key = 'mini_rec_' . $lango . '_' . md5(implode(',', wp_list_pluck(WC()->cart->get_cart(), 'product_id')));
$cache_expire = 30 * MINUTE_IN_SECONDS; // كاش 30 دقيقة
$display_on_sale = true;              // ابدا بالخصومات

// استبعاد منتجات الكارت
$exclude_ids = array();
if (WC()->cart) {
  foreach (WC()->cart->get_cart() as $item) {
    $exclude_ids[] = $item['product_id'];
  }
}
$exclude_ids = array_filter(array_unique($exclude_ids));

// حاول تجيب من الكاش الأول
$products = get_transient($cache_key);

if (false === $products) {
  $products = array();

  // فلترة المنتجات حسب اللغة الحالية
  add_filter('posts_where', 'filter_products_by_current_language', 10, 2);

  // 1) خصومات أولاً
  if ($display_on_sale) {
    $on_sale_ids = wc_get_product_ids_on_sale();
    if (!empty($on_sale_ids)) {
      // فلترة الخصومات حسب اللغة
      $on_sale_ids = filter_product_ids_by_language($on_sale_ids, $lango);
      // استبعاد الكارت
      $on_sale_ids = array_diff($on_sale_ids, $exclude_ids);

      if (!empty($on_sale_ids)) {
        // ناخد آخر خصومات مضافة (أحدثية)
        $args = array(
          'status' => 'publish',
          'limit' => $limit,
          'include' => $on_sale_ids,
          'orderby' => 'date',
          'order' => 'DESC',
          'return' => 'ids',
          'stock_status' => 'instock',
          'lang' => $lango, // إضافة اللغة
        );
        $products = wc_get_products($args);
      }
    }
  }

  // 2) لو ناقص عدد، كمل بالأكثر مبيعًا
  if (count($products) < $limit) {
    $need = $limit - count($products);
    $args_pop = array(
      'status' => 'publish',
      'limit' => $need,
      'exclude' => array_merge($exclude_ids, $products),
      'orderby' => 'meta_value_num',
      'meta_key' => 'total_sales',
      'order' => 'DESC',
      'return' => 'ids',
      'stock_status' => 'instock',
      'lang' => $lango, // إضافة اللغة
    );
    $popular = wc_get_products($args_pop);
    $products = array_merge($products, $popular);
  }

  // إزالة الفلتر
  remove_filter('posts_where', 'filter_products_by_current_language', 10);

  set_transient($cache_key, $products, $cache_expire);
}

// دالة مساعدة لفلترة المنتجات حسب اللغة
function filter_product_ids_by_language($product_ids, $lang)
{
  if (empty($product_ids) || !function_exists('pll_get_post')) {
    return $product_ids;
  }

  $filtered_ids = array();
  foreach ($product_ids as $id) {
    $product_lang = pll_get_post_language($id);
    if ($product_lang === $lang) {
      $filtered_ids[] = $id;
    }
  }
  return $filtered_ids;
}

// فلتر لتحديد المنتجات حسب اللغة في الاستعلام
function filter_products_by_current_language($where, $query)
{
  global $wpdb;

  if (!function_exists('pll_current_language')) {
    return $where;
  }

  $current_lang = pll_current_language();
  if (!$current_lang) {
    return $where;
  }

  // التأكد من أننا في استعلام منتجات
  if (isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'product') {
    $where .= $wpdb->prepare(
      " AND {$wpdb->posts}.ID IN (
                SELECT object_id 
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                WHERE tt.taxonomy = 'language' 
                AND t.slug = %s
            )",
      $current_lang
    );
  }

  return $where;
}
?>

<div class="tf-minicart-recommendations">
  <h4 class="title"><?php echo $lango == 'ar' ? 'المنتجات المماثلة' : 'You may also like'; ?></h4>
  <div class="wrap-recommendations">
    <div class="list-cart">
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $pid):
          $product = wc_get_product($pid);
          if (!$product || !$product->is_purchasable())
            continue;

          // التأكد من أن المنتج باللغة الحالية
          if (function_exists('pll_get_post_language')) {
            $product_lang = pll_get_post_language($pid);
            if ($product_lang && $product_lang !== $lango) {
              continue; // تخطي المنتج إذا كان بلغة مختلفة
            }
          }

          $permalink = get_permalink($pid);
          $title = $product->get_name();

          // صورة
          $img_html = get_the_post_thumbnail($pid, 'woocommerce_thumbnail', array(
            'class' => 'lazyload',
            'alt' => esc_attr($title),
          ));
          if (!$img_html) {
            $placeholder = wc_placeholder_img_src('woocommerce_thumbnail');
            $img_html = '<img class="lazyload" src="' . esc_url($placeholder) . '" alt="' . esc_attr($title) . '">';
          }

          // أسعار
          $regular = (float) $product->get_regular_price();
          $sale = (float) $product->get_sale_price();
          $is_sale = $product->is_on_sale() && $sale > 0 && $sale < $regular;

          // تنسيق الأرقام حسب إعدادات ووكوميرس
          $price_html_new = wc_price($is_sale ? $sale : (float) $product->get_price());
          $price_html_old = $is_sale ? wc_price($regular) : '';

          ?>
          <div class="list-cart-item">
            <div class="image">
              <a href="<?php echo esc_url($permalink); ?>">
                <?php echo $img_html; ?>
              </a>
            </div>
            <div class="content">
              <h6 class="name">
                <a class="link text-line-clamp-1" href="<?php echo esc_url($permalink); ?>">
                  <?php echo esc_html($title); ?>
                </a>
              </h6>
              <div class="cart-item-bot">
                <div class="price-wrap price">
                  <?php if ($is_sale): ?>
                    <span class="price-old h6 fw-normal"><?php echo wp_kses_post($price_html_old); ?></span>
                    <span class="price-new h6"><?php echo wp_kses_post($price_html_new); ?></span>
                  <?php else: ?>
                    <span class="price-new h6"><?php echo wp_kses_post($price_html_new); ?></span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-main">
          <?php echo $lango == 'ar' ? 'لا يوجد منتجات مماثلة حاليا' : 'No recommendations right now.'; ?></p>
      <?php endif; ?>
    </div>
  </div>
</div>

  <div class="canvas-wrapper">
    <div class="popup-header">
      <span class="title fw-semibold h4"><?php echo $lango == 'ar' ? 'سلة التسوق' : 'Shopping cart'; ?></span>
      <span class="icon-close icon-close-popup" data-bs-dismiss="offcanvas"></span>
    </div>

    
    <?php

    $cart = WC()->cart;
    ?>
    <div class="wrap">
      <div class="tf-mini-cart-wrap list-file-delete wrap-empty_text">

        <div class="tf-mini-cart-main">
          <div class="tf-mini-cart-sroll">
            <div class="tf-mini-cart-items <?php echo $cart->is_empty() ? 'list-empty' : ''; ?>">

              <?php if ($cart->is_empty()): ?>

                <div class="box-text_empty type-shop_cart">
                  <div class="shop-empty_top">
                    <span class="icon"><i class="icon-shopping-cart-simple"></i></span>
                    <h3 class="text-emp fw-normal"><?php echo $lango == 'ar' ? 'السلة فارغة' : 'Your cart is empty'; ?></h3>
                    <p class="h6 text-main">  
                      <?php echo $lango == 'ar' ? 'السلة فارغة حاليا. دعنا نساعدك في العثور على المنتج الصحيح' : 'Your cart is currently empty. Let us assist you in finding the right product'; ?>
                    </p>
                  </div>
                  <div class="shop-empty_bot">
                    <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"
                      class="tf-btn animate-btn"><?php echo $lango == 'ar' ? 'تسوق الآن' : 'Shop now'; ?></a>
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
                        <!-- أزرار التحكم في الكمية -->
                        <div class="quantity-controls">
                          <button class="qty-btn minus" data-action="decrease">-</button>
                          <span class="quantity-display"><?php echo esc_html($cart_item['quantity']); ?></span>
                          <button class="qty-btn plus" data-action="increase">+</button>
                        </div>
                        
                        <div class="h6 fw-semibold item-price">
                          <span class="price text-primary tf-mini-card-price"><?php echo wc_price($product->get_price() * $cart_item['quantity']); ?></span>
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
          </div>
        </div>

        <div class="tf-mini-cart-bottom box-empty_clear">
          <?php //if (!$cart->is_empty()): ?>
            <div class="tf-mini-cart-threshold">
              <div class="text">
                <h6 class="subtotal">
                  <?php
                  printf(
                    __('Subtotal (%s item)', 'textdomain'),
                    '<span class="prd-count">' . $cart->get_cart_contents_count() . '</span>'
                  );
                  ?>
                </h6>
                <h4 class="text-primary total-price tf-totals-total-value">
                  <?php echo wc_price($cart->get_cart_contents_total()); ?>
                </h4>
              </div>
            </div>

            <div class="tf-mini-cart-bottom-wrap">
              <div class="tf-mini-cart-view-checkout">
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>"
                  class="tf-btn btn-white animate-btn animate-dark line"><?php echo $lango == 'ar' ? 'عرض السلة' : 'View cart'; ?></a>
                <a href="<?php echo esc_url(wc_get_checkout_url()); ?>"
                  class="tf-btn animate-btn d-inline-flex bg-dark-2 w-100 justify-content-center"><span><?php echo $lango == 'ar' ? 'الدفع' : 'Check out'; ?></span></a>
              </div>
            </div>
          <?php //endif; ?>
        </div>

      </div>
    </div>
    
  </div>
</div>