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
  <meta name="description" content="<?php echo get_bloginfo('description'); ?>">

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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
    integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
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
    <div class="tf-topbar bg-black">
      <div class="container">
        <div class="row">
          <div class="col-xl-7 col-lg-8">
            <div class="topbar-left">
              <h6 class="text-up text-white fw-normal text-line-clamp-1">
                <?= $lango == 'ar' ? get_field('marketing_txt_ar', 'option') : get_field('marketing_txt_en', 'option'); ?>
              </h6>
            </div>
          </div>
          <div class="col-xl-5 col-lg-4 d-none d-lg-block">
            <ul class="topbar-right topbar-option-list">

              <li class="br-line d-none d-xl-inline-flex"></li>
              <li class="tf-languages d-none d-xl-block">
                <a href="<?= $lango == 'ar' ? site_url('/') : site_url('/ar'); ?>"
                  class="tf-btn-line style-white letter-space-0"><?= $lango == 'ar' ? 'English' : 'Arabic'; ?></a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <!-- /Top Bar -->
    <!-- Header -->
    <header class="tf-header style-3">
      <div class="header-top">
        <div class="container">
          <div class="row align-items-center">
            <div class="col-md-4 col-3 d-xl-none">
              <a href="#mobileMenu" data-bs-toggle="offcanvas" class="btn-mobile-menu">
                <span></span>
              </a>
            </div>

            <div class="col-xl-4 d-none d-xl-block">
              <div id="wc-search-results"></div>
              <form id="wc-ajax-search-form" class="form_search-product"
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
                      <span><?php echo ($lango == 'ar' ? 'كل التصنيفات' : 'All categories'); ?></span>
                    </li>
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

              </form>


            </div>
            <div class="col-xl-4 col-md-4 col-6">
              <a href="<?php echo home_url(); ?>" class="logo-site justify-content-center">
                <img src="<?php echo get_field('site_logo_arabic', 'option'); ?>" alt="Logo">
              </a>
            </div>
            <div class="col-xl-4 col-md-4 col-3">
              <ul class="nav-icon-list">
                <li class="d-none d-lg-flex">
                  <?php if (is_user_logged_in()):
                    $url = $lango == 'ar' ? site_url('my-account') : site_url('my-account-2');
                  else:
                    $url = $lango == 'ar' ? site_url('/login') : site_url('/login');
                  endif;
                  ?>
                  <a class="nav-icon-item-2 text-white link" href="<?php echo $url; ?>">
                    <i class="icon icon-user"></i>
                  </a>
                </li>
                <li class="d-none none">
                  <a class="nav-icon-item link" href="#search" data-bs-toggle="modal">
                    <i class="icon icon-magnifying-glass"></i>
                  </a>
                </li>
                <li class="d-none d-sm-flex">
                  <a class="nav-icon-item link" href="<?php echo site_url('wishlist'); ?>"><i class="icon icon-heart"></i></a>
                </li>
                <?php echo my_header_cart_markup(); ?>

              </ul>
            </div>
          </div>
        </div>
      </div>
      <div class="header-inner d-none d-xl-block">
        <div class="container">
          <span class="br-line d-block"></span>
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
      </div>
    </header>
    <header class="tf-header header-fixed">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-md-4 col-3 d-xl-none">
            <a href="#mobileMenu" data-bs-toggle="offcanvas" class="btn-mobile-menu">
              <span></span>
            </a>
          </div>
          <div class="col-xl-3 col-md-4 col-6 text-center text-xl-start">
            <a href="<?php echo home_url(); ?>" class="logo-site justify-content-center">
                <img src="<?php echo get_field('site_logo_arabic', 'option'); ?>" alt="Logo">
            </a>
          </div>
          <div class="col-xl-6 d-none d-xl-block">
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
          <div class="col-xl-3 col-md-4 col-3">
            <ul class="nav-icon-list">
              <li class="d-none d-lg-flex">
                <?php if (is_user_logged_in()):
                  $url = $lango == 'ar' ? site_url('my-account') : site_url('my-account-2');
                else:
                  $url = $lango == 'ar' ? site_url('/login') : site_url('/login');
                endif;
                ?>
                <a class="nav-icon-item-2 text-white link" href="<?php echo $url; ?>">
                  <i class="icon icon-user"></i>
                </a>
              </li>

              <li class="d-none d-sm-flex">
                <a class="nav-icon-item link" href="<?= $lango == 'ar' ? site_url('/المفضلة') : site_url('/wishlist') ?>"><i class="icon icon-heart"></i></a>
              </li>
              <?php echo my_header_cart_markup(); ?>

              <li style="background-color: #000;padding: 5px 10px;border-radius: 5px;">
                <a href="<?= $lango == 'ar' ? site_url('/') : site_url('/ar'); ?>"
          class="tf-btn-line style-white letter-space-0"><?= $lango == 'ar' ? 'English' : 'Arabic'; ?></a>
              </li>
              
            </ul>
          </div>
        </div>
      </div>
    </header>
    <!-- /Header -->