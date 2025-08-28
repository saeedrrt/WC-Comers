<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package ecom
 */
$lango = pll_current_language();

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <title><?php echo get_bloginfo('name'); ?></title>
  <meta name="author" content="themesflat.com">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="description"
    content="Themesflat Ochaka - A modern and elegant Multipurpose eCommerce HTML Template, perfect for online stores selling rings, necklaces, watches, and other accessories. SEO-optimized, fast-loading, and fully customizable.">

  <!-- font -->
  <link rel="stylesheet" href="<?= get_template_directory_uri(); ?>/assets/fonts/fonts.css">
  <link rel="stylesheet" href="<?= get_template_directory_uri(); ?>/assets/icon/icomoon/style.css">
  <!-- css -->
  <link rel="stylesheet" href="<?= get_template_directory_uri(); ?>/assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?= get_template_directory_uri(); ?>/assets/css/drift-basic.min.css">
  <link rel="stylesheet" href="<?= get_template_directory_uri(); ?>/assets/css/photoswipe.css">
  <link rel="stylesheet" href="<?= get_template_directory_uri(); ?>/assets/css/swiper-bundle.min.css">
  <link rel="stylesheet" href="<?= get_template_directory_uri(); ?>/assets/css/animate.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"
    integrity="sha512-3pIirOrwegjM6erE5gPSwkUzO+3cTjpnV9lexlNZqvupR64iZBnOOTiiLPb9M36zpMScbmUNIcHUqKD47M719g=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css" integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" type="text/css" href="<?= get_template_directory_uri(); ?>/assets/css/styles.css">

  <!-- Favicon and Touch Icons  -->
  <!--<link rel="shortcut icon" href="<?//= get_template_directory_uri(); ?>/assets/images/logo/favicon.svg">-->
  <!--<link rel="apple-touch-icon-precomposed" href="<?//= get_template_directory_uri(); ?>/assets/images/logo/favicon.svg">-->

  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
  <?php wp_body_open(); ?>

  <!-- Scroll Top -->
  <button id="goTop">
    <span class="border-progress"></span>
    <span class="icon icon-caret-up"></span>
  </button>


  <!-- preload -->
  <div class="preload preload-container" id="preload">
    <div class="preload-logo">
      <div class="spinner"></div>
    </div>
  </div>
  <!-- /preload -->

  <div id="wrapper">
    <!-- Top Bar-->
    <div class="tf-topbar bg-dark-blu type-space-2 line-bt-3">
      <div class="container-full-2">
        <div class="row">
          <div class="col-xl-6 col-lg-8">
            <div class="topbar-left justify-content-center justify-content-sm-start">
              <ul class="topbar-option-list">
                <li class="h6 d-none d-sm-flex">
                  <a href="tel:18001234567" class="text-white link track"
                    style="direction: <?= $lango == 'ar' ? 'rtl' : 'ltr'; ?>;">
                    <i class="icon icon-phone"></i>
                    <?= $lango == 'ar' ? 'اتصل بنا: ' . get_field('site_number', 'option') : 'Call us for free: ' . get_field('site_number', 'option'); ?>
                  </a>
                </li>
                <li class="br-line d-none d-sm-flex"></li>
              </ul>
            </div>
          </div>
          <div class="col-xl-6 col-lg-4 d-none d-lg-block">
            <ul class="topbar-right topbar-option-list">

              <?php
              $languages = pll_the_languages(array('raw' => 1));
              if (!empty($languages)): ?>

                <li class="tf-languages d-none d-xl-block">
                  <select class="tf-dropdown-select style-default color-white type-languages" id="languageSwitcher">
                    <?php foreach ($languages as $lang): ?>
                      <option value="<?= esc_url($lang['url']); ?>" <?= $lang['current_lang'] ? 'selected' : ''; ?>>
                        <?= esc_html($lang['name']); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </li>

                <script>
                  document.getElementById('languageSwitcher').addEventListener('change', function () {
                    if (this.value) window.location.href = this.value;
                  });
                </script>

              <?php endif; ?>

            </ul>
          </div>
        </div>
      </div>
    </div>
    <!-- /Top Bar -->
    <!-- Header -->
    <header class="tf-header style-5">
      <div class="header-top">
        <div class="container-full-2">
          <div class="row align-items-center">
            <div class="col-md-4 col-3 d-xl-none">
              <a href="#mobileMenu" data-bs-toggle="offcanvas" class="btn-mobile-menu style-white">
                <span></span>
              </a>
            </div>
            <div class="col-xl-2 col-md-4 col-6 text-center text-xl-start">
              <a href="<?= $lango == 'ar' ? site_url('/ar') : site_url(); ?>"
                class="logo-site justify-content-center justify-content-xl-start">
                <img src="<?php the_field('site_logo_arabic', 'option'); ?>" alt="">
              </a>
            </div>

            <div class="col-xl-10 col-md-4 col-3">
              <div class="header-right">
                <div id="wc-search-results"></div>

                <form id="wc-ajax-search-form" class="form_search-product style-search-2 d-none d-xl-flex"
                  action="<?php echo esc_url(home_url('/shop/')); ?>" method="get">
                  <?php
                  // جلب كل تصنيفات المنتجات اللي فيها منتجات
                  $terms = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                  ));
                  ?>

                  <div class="select-category">
                    <select name="product_cat" id="product_cat" class="dropdown_product_cat">
                      <option value="" <?php selected(isset($_GET['product_cat']) ? $_GET['product_cat'] : '', ''); ?>>
                        <?php echo ($lango == 'ar' ? 'كل التصنيفات' : 'All categories'); ?>
                      </option>
                      <?php foreach ($terms as $term): ?>
                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(isset($_GET['product_cat']) ? $_GET['product_cat'] : '', $term->slug); ?>>
                          <?php echo esc_html($term->name); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>

                    <ul class="select-options">
                      <li class="link" rel="">
                        <span><?php echo ($lango == 'ar' ? 'كل التصنيفات' : 'All categories'); ?></span></li>
                      <?php foreach ($terms as $term): ?>
                        <li class="link" rel="<?php echo esc_attr($term->slug); ?>">
                          <span><?php echo esc_html($term->name); ?></span>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>

                  <span class="br-line type-vertical"></span>

                  <input id="wc-search-term" name="s" class="style-def" type="text"
                    placeholder="<?php echo ($lango == 'ar' ? 'بحث عن منتجات...' : 'Search for products...'); ?>"
                    value="<?php echo esc_attr(isset($_GET['s']) ? $_GET['s'] : ''); ?>" required>

                  <button type="submit" id="wc-search-btn" class="btn-submit">
                    <i class="icon icon-magnifying-glass"></i>
                    <span class="h6 fw-bold"><?php echo ($lango == 'ar' ? 'بحث' : 'Search'); ?></span>
                  </button>

                </form>

                <ul class="nav-icon-list text-nowrap">
                  <li class="d-none d-lg-flex">
                    <?php if (is_user_logged_in()):
                      $url = $lango == 'ar' ? site_url('my-account') : site_url('my-account-2');
                      $label = $lango == 'ar' ? 'حسابي' : 'Account';
                      $sub = $lango == 'ar' ? 'مرحبا،' . wp_get_current_user()->display_name : 'Welcome ' . wp_get_current_user()->display_name;
                    else:
                      $url = $lango == 'ar' ? site_url('/login') : site_url('/login');
                      $label = $lango == 'ar' ? 'تسجيل الدخول' : 'Login';
                      $sub = $lango == 'ar' ? 'مرحبا،' : 'Welcome ';
                    endif;
                    ?>
                    <a class="nav-icon-item-2 text-white link" href="<?php echo $url; ?>">
                      <i class="icon icon-user"></i>
                      <div class="nav-icon-item_sub">
                        <span class="text-sub text-small-2"><?php echo esc_html($sub); ?></span>
                        <span class="h6"><?php echo esc_html($label); ?></span>
                      </div>
                    </a>
                  </li>

                  <li>

                    <?php if (is_user_logged_in()): ?>
                      <?php echo do_shortcode("[ti_wishlist_products_counter]") ?>
                    <?php else: ?>
                      <?php echo do_shortcode("[ti_wishlist_products_counter]") ?>
                      <script>
                        jQuery(document).ready(function ($) {
                          var counter = $('.wishlist_products_counter_number'); // غير الكلاس حسب الكلاس المستخدم
                          if (counter.text() == '0' || counter.text() == '') {
                            counter.hide();
                          }
                        });
                      </script>
                    <?php endif; ?>
                  </li>
                  <!-- <li>
                    <a href="#compare" data-bs-toggle="offcanvas">
                      <span class="icon icon-compare"></span>
                      <span class="compare-count"><?php //echo count( compare_get_ids() ); ?></span>
                    </a>
                  </li> -->
                  <?php echo my_header_cart_markup(); ?>

                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="header-inner d-none d-xl-block bg-white">
        <div class="container-full-2">
          <div class="header-inner_wrap">
            <div class="col-left">
              <div class="nav-category-wrap main-action-active">
                <div class="btn-nav-drop btn-active">
                  <span class="btn-mobile-menu type-small"><span></span></span>
                  <h6 class="name-category fw-semibold"><?php echo $lango == 'ar' ? 'الأقسام' : 'Departments'; ?></h6>
                  <i class="icon icon-caret-down"></i>
                </div>
                <ul class="box-nav-category active-item">
                  <?php
                  $terms = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                    'orderby' => 'name',
                    'parent' => 0,
                    'pad_counts' => true,
                  ));

                  if (!empty($terms) && !is_wp_error($terms)):
                    foreach ($terms as $term):
                      $term_link = get_term_link($term);
                      $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                      $image_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : wc_placeholder_img_src();
                      $count = $term->count;
                      ?>
                      <li>
                        <a href="<?= esc_url($term_link); ?>" class="nav-category_link h5">
                          <!-- <i class="icon icon-tv"></i> -->
                          <?= esc_html($term->name); ?>
                        </a>
                      </li>

                      <?php
                    endforeach;
                  endif;
                  ?>
                </ul>
              </div>
              <span class="br-line type-vertical h-24"></span>
              <nav class="box-navigation">
                <ul class="box-nav-menu">
                  <li class="menu-item">
                    <a href="javascript:void(0)"
                      class="item-link"><?php echo $lango == 'ar' ? 'الرئيسية' : 'Home'; ?></a>
                  </li>
                  <li class="menu-item">
                    <a href="<?= $lango == 'ar' ? home_url('/أسئلة-شائعة') : home_url('/faq'); ?>"
                      class="item-link"><?php echo $lango == 'ar' ? 'الأسئلة الشائعة' : 'FAQ'; ?></a>
                  </li>
                  <li class="menu-item">
                    <a href="<?= $lango == 'ar' ? home_url('/تواصل-معنا') : home_url('/contact-us'); ?>"
                      class="item-link"><?php echo $lango == 'ar' ? 'اتصل بنا' : 'Contact'; ?></a>
                  </li>


                  <li class="menu-item">
                    <a href="javascript:void(0)" class="item-link"><?php echo $lango == 'ar' ? 'التسوق' : 'SHOP'; ?><i
                        class="icon icon-caret-down"></i>
                    </a>

                    <div class="sub-menu mega-menu">
                      <div class="container">
                        <div class="row">

                          <?php
                          // 1) الأقسام الرئيسية
                          $parents = get_terms([
                            'taxonomy' => 'product_cat',
                            'parent' => 0,
                            'hide_empty' => true,
                            'orderby' => 'menu_order',
                            'order' => 'ASC',
                          ]);

                          if (!is_wp_error($parents) && !empty($parents)):

                            // أول 3 بس للأعمدة الشمال
                            $columns_left = array_slice($parents, 0, 3);
                            ?>

                            <!-- الأعمدة الشمال: 3×col-2 (قسم رئيسي + أبناؤه) -->
                            <?php foreach ($columns_left as $parent_term): ?>
                              <div class="col-2">
                                <div class="mega-menu-item">
                                  <h4 class="menu-heading">
                                    <a href="<?php echo esc_url(get_term_link($parent_term)); ?>">
                                      <?php echo esc_html($parent_term->name); ?>
                                    </a>
                                  </h4>

                                  <?php
                                  $children = get_terms([
                                    'taxonomy' => 'product_cat',
                                    'parent' => $parent_term->term_id,
                                    'hide_empty' => true,
                                    'number' => 4,
                                    'orderby' => 'menu_order',
                                    'order' => 'ASC',
                                  ]);
                                  ?>

                                  <?php if (!is_wp_error($children) && !empty($children)): ?>
                                    <ul class="sub-menu_list">
                                      <?php foreach ($children as $child): ?>
                                        <li>
                                          <a href="<?php echo esc_url(get_term_link($child)); ?>" class="sub-menu_link">
                                            <?php echo esc_html($child->name); ?>
                                          </a>
                                        </li>
                                      <?php endforeach; ?>
                                    </ul>
                                  <?php endif; ?>
                                </div>
                              </div>
                            <?php endforeach; ?>

                            <!-- العمود اليمين: آخر اتنين Discount Rules -->
                            <div class="col-6">
                              <ul class="list-hor">
                                <?php
                                $rules_q = new WP_Query([
                                  'post_type' => 'wc_discount_rule',
                                  'post_status' => 'publish',
                                  'posts_per_page' => 2, // اتنين بس
                                  'orderby' => 'date',
                                  'order' => 'DESC',
                                ]);

                                if ($rules_q->have_posts()):
                                  while ($rules_q->have_posts()):
                                    $rules_q->the_post();
                                    $rule_id = get_the_ID();
                                    $title = get_the_title() ?: 'Sale';
                                    $discount_url = function_exists('wcd_get_discount_rule_url')
                                      ? wcd_get_discount_rule_url($rule_id)
                                      : get_permalink($rule_id);

                                    $percent = (float) get_post_meta($rule_id, '_wcd_percent', true);
                                    $bannerTxt = (string) get_post_meta($rule_id, '_wcd_banner', true);

                                    $sale_text = $bannerTxt
                                      ? ((strpos($bannerTxt, '%s') !== false) ? sprintf($bannerTxt, $percent) : $bannerTxt)
                                      : sprintf('SALE upto %s%%', $percent);

                                    if (has_post_thumbnail($rule_id)) {
                                      $image_url = get_the_post_thumbnail_url($rule_id, 'large');
                                    } else {
                                      $image_url = wc_placeholder_img_src();
                                    }
                                    ?>
                                    <li class="wg-cls hover-img">
                                      <a href="<?php echo esc_url($discount_url); ?>" class="image img-style">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>"
                                          class="lazyloaded" />
                                      </a>
                                      <div class="cls-content">
                                        <h4 class="tag_cls"><?php echo esc_html($title); ?></h4>

                                        <a href="<?php echo esc_url($discount_url); ?>" class="tf-btn-line">
                                          <?php echo $lango == 'ar' ? 'تسوق الآن' : 'Shop now'; ?>
                                        </a>
                                      </div>
                                    </li>
                                    <?php
                                  endwhile;
                                  wp_reset_postdata();
                                endif;
                                ?>
                              </ul>
                            </div>

                          <?php endif; // end parents check ?>
                        </div>
                      </div>
                    </div>
                  </li>

                  <style>
                    .mega-menu-item .child-list {
                      display: none;
                      position: absolute;
                      top: 100%;
                      /* يطلع تحت الأب */
                      left: 0;
                      background: #fff;
                      min-width: 180px;
                      padding: 8px 0;
                      border: 1px solid #ddd;
                      border-radius: 4px;
                      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                      z-index: 999;
                    }

                    .mega-menu-item:hover .child-list {
                      display: block;
                    }

                    .mega-menu-item .child-list li a {
                      display: block;
                      padding: 6px 14px;
                      font-size: 14px;
                      color: #333;
                    }

                    .mega-menu-item .child-list li a:hover {
                      background: #f5f5f5;
                      color: #000;
                    }
                  </style>


                </ul>
              </nav>

            </div>

            <?php
            /**
             * رجّع أعلى منتج (أو تفريعة) من حيث نسبة الخصم
             * return: [
             *   'product_id'    => int,         // ID المنتج (الأب لو variable)
             *   'variation_id'  => int|0,       // ID التفريعة لو فيه
             *   'percent'       => int,         // نسبة الخصم %
             *   'regular_price' => float,
             *   'sale_price'    => float,
             *   'product'       => WC_Product,  // كائن المنتج (الأب)
             * ]
             */
            function wcd_get_top_discounted_product($cache_seconds = 600)
            {
              $cache_key = 'wcd_top_discounted_product';
              $cached = get_transient($cache_key);
              if ($cached && is_array($cached)) {
                return $cached;
              }

              // هات IDs المنتجات اللي عليها خصم (WooCommerce API)
              $on_sale_ids = wc_get_product_ids_on_sale();
              if (empty($on_sale_ids)) {
                return null;
              }

              $best = [
                'product_id' => 0,
                'variation_id' => 0,
                'percent' => 0,
                'regular_price' => 0,
                'sale_price' => 0,
                'product' => null,
              ];

              foreach ($on_sale_ids as $pid) {
                $product = wc_get_product($pid);
                if (!$product)
                  continue;

                // لو Simple
                if ($product->is_type('simple')) {
                  if (!$product->is_on_sale())
                    continue;

                  $reg = (float) $product->get_regular_price();
                  $sale = (float) $product->get_sale_price();
                  if ($reg > 0 && $sale > 0 && $sale < $reg) {
                    $percent = (int) round((($reg - $sale) / $reg) * 100);
                    if ($percent > $best['percent']) {
                      $best = [
                        'product_id' => $product->get_id(),
                        'variation_id' => 0,
                        'percent' => $percent,
                        'regular_price' => $reg,
                        'sale_price' => $sale,
                        'product' => $product,
                      ];
                    }
                  }
                }

                // لو Variable: افحص كل التفريعات
                elseif ($product->is_type('variable')) {
                  $variation_ids = $product->get_children(); // IDs التفريعات
                  $best_var = [
                    'variation_id' => 0,
                    'percent' => 0,
                    'regular_price' => 0,
                    'sale_price' => 0,
                  ];

                  foreach ($variation_ids as $vid) {
                    $var = wc_get_product($vid);
                    if (!$var || !$var->is_on_sale())
                      continue;

                    $reg = (float) $var->get_regular_price();
                    $sale = (float) $var->get_sale_price();
                    if ($reg > 0 && $sale > 0 && $sale < $reg) {
                      $percent = (int) round((($reg - $sale) / $reg) * 100);
                      if ($percent > $best_var['percent']) {
                        $best_var = [
                          'variation_id' => $vid,
                          'percent' => $percent,
                          'regular_price' => $reg,
                          'sale_price' => $sale,
                        ];
                      }
                    }
                  }

                  if ($best_var['percent'] > $best['percent']) {
                    $best = [
                      'product_id' => $product->get_id(),
                      'variation_id' => $best_var['variation_id'],
                      'percent' => $best_var['percent'],
                      'regular_price' => $best_var['regular_price'],
                      'sale_price' => $best_var['sale_price'],
                      'product' => $product,
                    ];
                  }
                }
                // تجاهل الأنواع التانية (grouped/external) هنا
              }

              if ($best['product_id']) {
                set_transient($cache_key, $best, $cache_seconds);
                return $best;
              }

              return null;
            }

            ?>
            <div class="col-right">
              <?php
              $top = wcd_get_top_discounted_product();

              if ($top) {
                $product = $top['product'];                 // WC_Product (الأب لو variable)
                $product_link = get_permalink($top['product_id']);
                $title = $product->get_name();
                $img_id = $product->get_image_id();
                $img_src = $img_id ? wp_get_attachment_image_url($img_id, 'medium') : wc_placeholder_img_src();

                ?>


                <i class="icon icon-truck"></i>
                <a href="<?php echo $product_link; ?>">
                  <p class="h6 text-black">
                    <?php echo $lango == 'ar' ? 'المنتج الأكثر خصم' : 'Most Discounted Product'; ?>: <?php echo $title; ?>
                    <span><?php echo $top['percent']; ?>%</span>
                  </p>
                </a>

                <?php
              } else {
                echo '<p>لا توجد منتجات عليها خصم حالياً.</p>';
              } ?>
            </div>
          </div>
        </div>
      </div>
    </header>
    <header class="tf-header header-fixed style-5 bg-dark-blu">
      <div class="header-top ">
        <div class="container-full-2">
          <div class="row align-items-center">
            <div class="col-md-4 col-3 d-xl-none">
              <a href="#mobileMenu" data-bs-toggle="offcanvas" class="btn-mobile-menu style-white">
                <span></span>
              </a>
            </div>
            <div class="col-xl-2 col-md-4 col-6 text-center text-xl-start">
              <a href="index.html" class="logo-site justify-content-center justify-content-xl-start">
                <img src="<?= get_template_directory_uri(); ?>/assets/images/logo/logo-white-2.svg" alt="">
              </a>
            </div>
            <div class="col-xl-10 col-md-4 col-3">
              <div class="header-right">
                <form class="form_search-product style-search-2 d-none d-xl-flex">
                  <div class="select-category">

                    <select name="product_cat" id="product_cat" class="dropdown_product_cat">
                      <option value="" selected="selected">
                        <?php echo $lango == 'ar' ? 'جميع الأقسام' : 'All categories'; ?></option>
                      <?php $args = array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => false,
                        'parent' => 0,
                      );
                      $terms = get_terms($args);
                      foreach ($terms as $term): ?>
                        <option value="<?php echo esc_attr($term->slug); ?>" <?php selected(isset($_GET['product_cat']) ? $_GET['product_cat'] : '', $term->slug); ?>>
                          <?php echo esc_html($term->name); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <ul class="select-options">
                      <li class="link" rel="">
                        <span><?php echo $lango == 'ar' ? 'جميع الأقسام' : 'All categories'; ?></span></li>
                      <?php foreach ($terms as $term): ?>
                        <li class="link" rel="<?php echo esc_attr($term->slug); ?>">
                          <span><?php echo esc_html($term->name); ?></span></li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                  <span class="br-line type-vertical"></span>
                  <input class="style-def" type="text"
                    placeholder="<?php echo $lango == 'ar' ? 'بحث عن منتجات...' : 'Search for products...'; ?>"
                    required>
                  <button type="submit" class="btn-submit">
                    <i class="icon icon-magnifying-glass"></i>
                    <span class="h6 fw-bold"><?php echo $lango == 'ar' ? 'بحث' : 'Search'; ?></span>
                  </button>
                </form>
                <ul class="nav-icon-list text-nowrap">
                  <li class="d-none d-lg-flex">
                    <?php if (is_user_logged_in()):
                      $url = site_url('my-account');
                      $label = 'Your account';
                      $sub = 'مرحبا، ' . wp_get_current_user()->display_name;
                    else:
                      $url = wp_login_url(get_permalink()); // بعد تسجيل الدخول يعيد لهذه الصفحة
                      $label = 'تسجيل الدخول';
                      $sub = 'مرحبًا بك';
                    endif;
                    ?>
                    <a class="nav-icon-item-2 text-white link" href="<?php echo esc_url($url); ?>">
                      <i class="icon icon-user"></i>
                      <div class="nav-icon-item_sub">
                        <span class="text-sub text-small-2"><?php echo esc_html($sub); ?></span>
                        <span class="h6"><?php echo esc_html($label); ?></span>
                      </div>
                    </a>
                  </li>

                  <li>
                  
                    <?php if (is_user_logged_in()): ?>
                      <?php echo do_shortcode("[ti_wishlist_products_counter]") ?>
                    <?php else: ?>
                      <?php echo do_shortcode("[ti_wishlist_products_counter]") ?>
                      <script>
                        jQuery(document).ready(function ($) {
                          var counter = $('.wishlist_products_counter_number'); // غير الكلاس حسب الكلاس المستخدم
                          if (counter.text() == '0' || counter.text() == '') {
                            counter.hide();
                          }
                        });
                      </script>
                    <?php endif; ?>
                  </li>
                  <!-- <li>
                                                        <a href="#compare" data-bs-toggle="offcanvas">
                                                          <span class="icon icon-compare"></span>
                                                          <span class="compare-count"><?php //echo count( compare_get_ids() ); ?></span>
                                                        </a>
                                                      </li> -->
                  <?php echo my_header_cart_markup(); ?>


                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="header-inner d-none d-xl-block bg-white">
        <div class="container-full-2">
          <div class="header-inner_wrap">
            <div class="col-left">
              <div class="nav-category-wrap main-action-active">
                <div class="btn-nav-drop btn-active">
                  <span class="btn-mobile-menu type-small"><span></span></span>
                  <h6 class="name-category fw-semibold"><?php echo $lango == 'ar' ? 'الأقسام' : 'Departments'; ?></h6>
                  <i class="icon icon-caret-down"></i>
                </div>
                <ul class="box-nav-category active-item">
                  <?php
                  $terms = get_terms([
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                    'orderby' => 'name',
                    'pad_counts' => true
                  ]);

                  $children_only = array_filter($terms, fn($t) => $t->parent !== 0);

                  if (!empty($children_only) && !is_wp_error($children_only)):
                    foreach ($children_only as $term):
                      $term_link = get_term_link($term);
                      $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                      $image_url = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : wc_placeholder_img_src();
                      $count = $term->count;
                      ?>
                      <li>
                        <a href="<?= esc_url($term_link); ?>" class="nav-category_link h5">
                          <!-- <i class="icon icon-tv"></i> -->
                          <?= esc_html($term->name); ?>
                        </a>
                      </li>

                      <?php
                    endforeach;
                  endif;
                  ?>
                </ul>
              </div>
              <span class="br-line type-vertical h-24"></span>
              <nav class="box-navigation">
                <ul class="box-nav-menu">
                  <li class="menu-item">
                    <a href="javascript:void(0)"
                      class="item-link"><?php echo $lango == 'ar' ? 'الرئيسية' : 'Home'; ?></a>
                  </li>
                  <li class="menu-item">
                    <a href="<?= $lango == 'ar' ? home_url('/تواصل-معنا') : home_url('/contact-us'); ?>"
                      class="item-link"><?php echo $lango == 'ar' ? 'اتصل بنا' : 'Contact'; ?></a>
                  </li>
                  <li class="menu-item">
                    <a href="<?= $lango == 'ar' ? home_url('/أسئلة-شائعة') : home_url('/faq'); ?>"
                      class="item-link"><?php echo $lango == 'ar' ? 'الأسئلة الشائعة' : 'FAQ'; ?></a>
                  </li>

                  <li class="menu-item">
                    <a href="javascript:void(0)" class="item-link"><?php echo $lango == 'ar' ? 'التسوق' : 'SHOP'; ?><i
                        class="icon icon-caret-down"></i>
                    </a>

                    <div class="sub-menu mega-menu">
                      <div class="container">
                        <div class="row">

                          <?php
                          // 1) الأقسام الرئيسية
                          $parents = get_terms([
                            'taxonomy' => 'product_cat',
                            'parent' => 0,
                            'hide_empty' => true,
                            'orderby' => 'menu_order',
                            'order' => 'ASC',
                          ]);

                          if (!is_wp_error($parents) && !empty($parents)):

                            // أول 3 بس للأعمدة الشمال
                            $columns_left = array_slice($parents, 0, 3);
                            ?>

                            <!-- الأعمدة الشمال: 3×col-2 (قسم رئيسي + أبناؤه) -->
                            <?php foreach ($columns_left as $parent_term): ?>
                              <div class="col-2">
                                <div class="mega-menu-item">
                                  <h4 class="menu-heading">
                                    <a href="<?php echo esc_url(get_term_link($parent_term)); ?>">
                                      <?php echo esc_html($parent_term->name); ?>
                                    </a>
                                  </h4>

                                  <?php
                                  $children = get_terms([
                                    'taxonomy' => 'product_cat',
                                    'parent' => $parent_term->term_id,
                                    'hide_empty' => true,
                                    'number' => 4,
                                    'orderby' => 'menu_order',
                                    'order' => 'ASC',
                                  ]);
                                  ?>

                                  <?php if (!is_wp_error($children) && !empty($children)): ?>
                                    <ul class="sub-menu_list">
                                      <?php foreach ($children as $child): ?>
                                        <li>
                                          <a href="<?php echo esc_url(get_term_link($child)); ?>" class="sub-menu_link">
                                            <?php echo esc_html($child->name); ?>
                                          </a>
                                        </li>
                                      <?php endforeach; ?>
                                    </ul>
                                  <?php endif; ?>
                                </div>
                              </div>
                            <?php endforeach; ?>

                            <!-- العمود اليمين: آخر اتنين Discount Rules -->
                            <div class="col-6">
                              <ul class="list-hor">
                                <?php
                                $rules_q = new WP_Query([
                                  'post_type' => 'wc_discount_rule',
                                  'post_status' => 'publish',
                                  'posts_per_page' => 2, // اتنين بس
                                  'orderby' => 'date',
                                  'order' => 'DESC',
                                ]);

                                if ($rules_q->have_posts()):
                                  while ($rules_q->have_posts()):
                                    $rules_q->the_post();
                                    $rule_id = get_the_ID();
                                    $title = get_the_title() ?: 'Sale';
                                    $discount_url = function_exists('wcd_get_discount_rule_url')
                                      ? wcd_get_discount_rule_url($rule_id)
                                      : get_permalink($rule_id);

                                    $percent = (float) get_post_meta($rule_id, '_wcd_percent', true);
                                    $bannerTxt = (string) get_post_meta($rule_id, '_wcd_banner', true);

                                    $sale_text = $bannerTxt
                                      ? ((strpos($bannerTxt, '%s') !== false) ? sprintf($bannerTxt, $percent) : $bannerTxt)
                                      : sprintf('SALE upto %s%%', $percent);

                                    if (has_post_thumbnail($rule_id)) {
                                      $image_url = get_the_post_thumbnail_url($rule_id, 'large');
                                    } else {
                                      $image_url = wc_placeholder_img_src();
                                    }
                                    ?>
                                    <li class="wg-cls hover-img">
                                      <a href="<?php echo esc_url($discount_url); ?>" class="image img-style">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($title); ?>"
                                          class="lazyloaded" />
                                      </a>
                                      <div class="cls-content">
                                        <h4 class="tag_cls"><?php echo esc_html($title); ?></h4>

                                        <a href="<?php echo esc_url($discount_url); ?>" class="tf-btn-line">
                                          <?php echo $lango == 'ar' ? 'تسوق الآن' : 'Shop now'; ?>
                                        </a>
                                      </div>
                                    </li>
                                    <?php
                                  endwhile;
                                  wp_reset_postdata();
                                endif;
                                ?>
                              </ul>
                            </div>

                          <?php endif; // end parents check ?>
                        </div>
                      </div>
                    </div>
                  </li>


                  <style>
                    /* الكاتيجوري الرئيسي */
                    .main-cat-link {
                      font-weight: 700;
                      font-size: 15px;
                      display: inline-block;
                      color: #222;
                      text-transform: uppercase;
                      margin-bottom: 6px;
                    }

                    .main-cat-link:hover {
                      color: rgb(0, 28, 104);
                      /* لون عند الـ Hover */
                    }

                    /* قائمة السبكاتيجوري */
                    .styled-sub {
                      margin: 0 0 1rem;
                      padding-left: 0;
                      list-style: none;
                    }

                    .styled-sub li {
                      margin-bottom: 4px;
                    }

                    .sub-cat-link {
                      display: flex;
                      align-items: center;
                      font-size: 14px;
                      color: #555;
                      text-decoration: none;
                      transition: all 0.2s ease-in-out;
                    }

                    .sub-cat-link .sub-icon {
                      font-size: 12px;
                      margin-right: 6px;
                      color: #999;
                      transition: margin-right 0.2s ease-in-out, color 0.2s ease-in-out;
                    }

                    .sub-cat-link:hover {
                      color: rgb(0, 28, 104);
                    }

                    .sub-cat-link:hover .sub-icon {
                      margin-right: 10px;
                      color: rgb(0, 28, 104);
                    }
                  </style>


                  </li>

                </ul>
              </nav>
            </div>
            <div class="col-right">
              <div class="lang-switcher">
                <a href="<?= $lango == 'ar' ? site_url('/') : site_url('/ar'); ?>" class="btn btn-border btn-sm">
                  <?= $lango == 'ar' ? 'English' : 'عربي'; ?>
                  <i class="fa fa-globe"></i>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>
    <!-- /Header -->