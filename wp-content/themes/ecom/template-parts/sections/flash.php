<?php
// جلب المنتج الأعلى خصماً مع تاريخ انتهاء
function get_highest_discount_product_with_timer() {
    // الحصول على جميع المنتجات التي عليها خصم
    $sale_product_ids = wc_get_product_ids_on_sale();
    
    if (empty($sale_product_ids)) {
        return null;
    }
    
    $highest_discount = 0;
    $best_product = null;
    $current_time = current_time('timestamp');
    
    foreach ($sale_product_ids as $product_id) {
        $product = wc_get_product($product_id);
        
        if (!$product || !$product->is_on_sale()) {
            continue;
        }
        
        // التحقق من وجود تاريخ انتهاء للخصم
        $sale_end_timestamp = get_post_meta($product_id, '_sale_price_dates_to', true);
        
        if (!$sale_end_timestamp || $sale_end_timestamp <= $current_time) {
            continue; // تجاهل المنتجات بدون تاريخ انتهاء أو منتهية الصلاحية
        }
        
        // حساب نسبة الخصم
        $regular_price = (float) $product->get_regular_price();
        $sale_price = (float) $product->get_sale_price();
        
        if ($regular_price > 0) {
            $discount_percentage = (($regular_price - $sale_price) / $regular_price) * 100;
            
            if ($discount_percentage > $highest_discount) {
                $highest_discount = $discount_percentage;
                $best_product = $product;
            }
        }
    }
    
    return $best_product;
}

/**
 * رجّع أعلى منتج (أو فاريشن) عليه خصم مع تايمر صالح، للغة معيّنة.
 * @param string $lang_slug مثال: 'ar' أو 'en'
 * @return WC_Product|WC_Product_Variation|null
 */
function wcd_get_highest_discount_product_with_timer_by_lang($lang_slug)
{
  $sale_ids = wc_get_product_ids_on_sale();
  if (empty($sale_ids))
    return null;

  $now = current_time('timestamp');
  $best_prod = null;
  $best_disc = 0;
  $seen = [];

  $has_pll = function_exists('pll_get_post_language') && function_exists('pll_get_post');

  foreach ($sale_ids as $orig_id) {
    $work_id = (int) $orig_id;
    $post_type = get_post_type($work_id);

    // -------- Polylang: حوّل الـ ID للّغة المطلوبة --------
    if ($has_pll) {
      if ($post_type === 'product_variation') {
        // أولًا جرّب ترجمة الفاريشن نفسه مباشرةً
        $t_var_id = pll_get_post($work_id, $lang_slug);
        if ($t_var_id) {
          $work_id = (int) $t_var_id;
          $post_type = 'product_variation';
        } else {
          // لو مفيش ترجمة مباشرة: هات الأب باللغة المطلوبة وبعدين دور على فاريشناته اللي عليها خصم وTimer
          $parent_id = wp_get_post_parent_id($work_id);
          if (!$parent_id)
            continue;

          $t_parent = pll_get_post($parent_id, $lang_slug);
          if (!$t_parent)
            continue;

          $parent_obj = wc_get_product($t_parent);
          if (!$parent_obj || !$parent_obj->is_type('variable'))
            continue;

          foreach ((array) $parent_obj->get_children() as $vid) {
            $v = wc_get_product($vid);
            if (!$v || !$v->is_on_sale())
              continue;
            $end = (int) get_post_meta($vid, '_sale_price_dates_to', true);
            if (!$end || $end <= $now)
              continue;

            $reg = (float) $v->get_regular_price();
            $sale = (float) $v->get_sale_price();
            if ($reg <= 0 || $sale <= 0 || $sale >= $reg)
              continue;

            $disc = (($reg - $sale) / $reg) * 100.0;
            if ($disc > $best_disc) {
              $best_disc = $disc;
              $best_prod = $v;
            }
          }
          // كمل للآي دي اللي بعده
          continue;
        }
      } else {
        // product (simple/variable parent)
        $cur_lang = pll_get_post_language($work_id, 'slug');
        if ($cur_lang !== $lang_slug) {
          $t_id = pll_get_post($work_id, $lang_slug);
          if (!$t_id)
            continue; // المنتج ده ملوش ترجمة في اللغة المطلوبة
          $work_id = (int) $t_id;
        }
      }
    }

    // منع تكرار نفس ID بعد الترجمة
    if (isset($seen[$work_id]))
      continue;
    $seen[$work_id] = true;

    $p = wc_get_product($work_id);
    if (!$p || !$p->is_on_sale())
      continue;

    // لازم تايمر صالح
    if ($p->is_type('variable')) {
      // الخصم عادة على الفاريشنات؛ نتأكد من أي فاريشن عليه خصم + تايمر
      foreach ((array) $p->get_children() as $vid) {
        $v = wc_get_product($vid);
        if (!$v || !$v->is_on_sale())
          continue;
        $end = (int) get_post_meta($vid, '_sale_price_dates_to', true);
        if (!$end || $end <= $now)
          continue;

        $reg = (float) $v->get_regular_price();
        $sale = (float) $v->get_sale_price();
        if ($reg <= 0 || $sale <= 0 || $sale >= $reg)
          continue;

        $disc = (($reg - $sale) / $reg) * 100.0;
        if ($disc > $best_disc) {
          $best_disc = $disc;
          $best_prod = $v;
        }
      }
    } else {
      // simple أو variation (لو وصلنا هنا)
      $end = (int) get_post_meta($work_id, '_sale_price_dates_to', true);
      if (!$end || $end <= $now)
        continue;

      $reg = (float) $p->get_regular_price();
      $sale = (float) $p->get_sale_price();
      if ($reg <= 0 || $sale <= 0 || $sale >= $reg)
        continue;

      $disc = (($reg - $sale) / $reg) * 100.0;
      if ($disc > $best_disc) {
        $best_disc = $disc;
        $best_prod = $p;
      }
    }
  }

  return $best_prod ?: null;
}

/**
 * هيلبر صغير يجيب العربي والإنجليزي مرة واحدة.
 * @return array ['ar' => WC_Product|WC_Product_Variation|null, 'en' => ...]
 */
function wcd_get_highest_discount_products_both_langs()
{
  return [
    'ar' => wcd_get_highest_discount_product_with_timer_by_lang('ar'),
    'en' => wcd_get_highest_discount_product_with_timer_by_lang('en'),
  ];
}

$products = wcd_get_highest_discount_products_both_langs();

$name_ara = $products['ar']->get_name();
$name_eng = $products['en']->get_name();

// استخدام الدالة
$product = get_highest_discount_product_with_timer();

if ($product) {
    $product_id = $product->get_id();
    $sale_end_timestamp = get_post_meta($product_id, '_sale_price_dates_to', true);
    
    if ($sale_end_timestamp) {
        $now = current_time('timestamp');
        $seconds_remaining = $sale_end_timestamp - $now;
        
        // صورة المنتج
        $image_url = get_the_post_thumbnail_url($product_id, '') ?: wc_placeholder_img_src();
        
        // السعر
        $price_html = $product->get_price_html();
        
        // رابط المنتج
        $link = get_permalink($product_id);
        
        // الاسم
        $title = $product->get_name();
        
        // حساب نسبة الخصم للعرض
        $regular_price = (float) $product->get_regular_price();
        $sale_price = (float) $product->get_sale_price();
        $discount_percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
?>
<!-- Banner Countdown -->
<div class="themesFlat">
  <div class="container">
    <div class="banner-cd_v02 wow fadeInUp">
      <div class="banner_title">
        <span class="icon">
          <!-- أيقونة النار أو عرض -->
          🔥
        </span>
        <h4 class="text-primary"><?php echo is_rtl() ? 'العرض ينتهي في:' : 'Hurry up offer ends in:'; ?> <span class="badge bg-danger"><?= $discount_percentage; ?>% <?php echo is_rtl() ? 'خصم' : 'OFF'; ?></span></h4>
      </div>

      <div class="count-down_v02">
        <div class="js-countdown cd-has-zero" data-timer="<?= esc_attr($seconds_remaining); ?>" data-labels="Days,Hours,Mins,Secs"></div>
      </div>

      <!-- Product Display Section -->
      <div class="product-showcase mt-4">
        <div class="product-image-container">
          <a href="<?= esc_url($link); ?>" class="product-image-link">
            <img src="<?= esc_url($image_url); ?>" alt="<?= esc_attr($title); ?>" class="product-image lazyload" />
            <div class="discount-badge">
              <span><?= $discount_percentage; ?>%</span>
              <small><?php echo is_rtl() ? 'خصم' : 'OFF'; ?></small>
            </div>
          </a>
        </div>
        
        <div class="product-details">
          <div class="product-title">
            <h3><a href="<?= esc_url($link); ?>" class="title-link"><?= $lang == 'ar' ? $name_ara : $name_eng; ?></a></h3>
          </div>
          
          <div class="product-price">
            <?= $price_html; ?>
          </div>
          
          <div class="product-action">
            <a href="<?= esc_url($link); ?>" class="shop-now-btn">
              <span>Shop Now</span>
              <i class="arrow">→</i>
            </a>
          </div>
        </div>
      </div>

      <style>
        .product-showcase {
          display: flex;
          align-items: center;
          gap: 30px;
          padding: 20px;
          background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
          border-radius: 20px;
          box-shadow: 0 10px 30px rgba(0,0,0,0.1);
          transition: transform 0.3s ease;
        }
        
        .product-showcase:hover {
          transform: translateY(-5px);
        }
        
        .product-image-container {
          position: relative;
          flex-shrink: 0;
        }
        
        .product-image-link {
          display: block;
          width: 180px;
          height: 180px;
          border-radius: 15px;
          overflow: hidden;
          box-shadow: 0 8px 25px rgba(0,0,0,0.15);
          transition: transform 0.3s ease;
        }
        
        .product-image-link:hover {
          transform: scale(1.05);
        }
        
        .product-image {
          width: 100%;
          height: 100%;
          object-fit: cover;
          transition: transform 0.3s ease;
        }
        
        .discount-badge {
          position: absolute;
          top: -10px;
          right: -10px;
          background: linear-gradient(45deg, #ff6b6b, #ff4757);
          color: white;
          padding: 8px 12px;
          border-radius: 50px;
          font-weight: bold;
          font-size: 14px;
          text-align: center;
          box-shadow: 0 4px 15px rgba(255,107,107,0.4);
          animation: pulse 2s infinite;
        }
        
        .discount-badge small {
          display: block;
          font-size: 10px;
          margin-top: -2px;
        }
        
        @keyframes pulse {
          0% { transform: scale(1); }
          50% { transform: scale(1.1); }
          100% { transform: scale(1); }
        }
        
        .product-details {
          flex: 1;
        }
        
        .product-title h3 {
          font-size: 28px;
          font-weight: 700;
          margin-bottom: 15px;
          line-height: 1.3;
        }
        
        .title-link {
          color: #2c3e50;
          text-decoration: none;
          transition: color 0.3s ease;
        }
        
        .title-link:hover {
          color: #3498db;
        }
        
        .product-price {
          margin-bottom: 20px;
          font-size: 24px;
          font-weight: 600;
        }
        
        .product-price del {
          color: #95a5a6;
          font-size: 18px;
          margin-right: 10px;
        }
        
        .product-price ins {
          color: #e74c3c;
          text-decoration: none;
          font-weight: 700;
        }
        
        .shop-now-btn {
          display: inline-flex;
          align-items: center;
          gap: 10px;
          background: linear-gradient(45deg, #3498db, #2980b9);
          color: white;
          padding: 15px 30px;
          border-radius: 50px;
          text-decoration: none;
          font-weight: 600;
          font-size: 16px;
          transition: all 0.3s ease;
          box-shadow: 0 4px 15px rgba(52,152,219,0.3);
        }
        
        .shop-now-btn:hover {
          background: linear-gradient(45deg, #2980b9, #3498db);
          transform: translateY(-2px);
          box-shadow: 0 6px 20px rgba(52,152,219,0.4);
          color: white;
        }
        
        .arrow {
          transition: transform 0.3s ease;
        }
        
        .shop-now-btn:hover .arrow {
          transform: translateX(5px);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
          .product-showcase {
            flex-direction: column;
            text-align: center;
            gap: 20px;
          }
          
          .product-image-link {
            width: 150px;
            height: 150px;
          }
          
          .product-title h3 {
            font-size: 24px;
          }
          
          .product-price {
            font-size: 20px;
          }
        }
      </style>
    </div>
  </div>
</div>
<?php
    }
}
?>