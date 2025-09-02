<?php
/**
 * Custom Cart Template for WooCommerce
 * Template Name: Custom Cart
 */

defined('ABSPATH') || exit;

get_header();

$cart_items = WC()->cart->get_cart();

$lang_cart = pll_current_language();
?>

<!-- Page Title -->
<section class="s-page-title">
    <div class="container">
        <div class="content">
            <h1 class="title-page"><?= get_the_title(); ?></h1>
            <ul class="breadcrumbs-page">
                <li><a href="<?= home_url(); ?>" class="h6 link"><?= $lang_cart == 'ar' ? 'الرئيسية' : 'Home' ?></a></li>
                <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                <li>
                    <h6 class="current-page fw-normal"><?= $lang_cart == 'ar' ? 'السلة' : 'Shopping Cart' ?></h6>
                </li>
            </ul>
        </div>
    </div>
</section>
<!-- /Page Title -->

<!-- View Cart -->
<div class="flat-spacing each-list-prd">
    <div class="container">
        <div class="row">
            <div class="col-xxl-9 col-xl-8">
                <div class="tf-cart-sold">
                    <div class="notification-sold bg-surface d-none">
                        <img class="icon" src="<?= get_template_directory_uri(); ?>/assets/icon/fire.svg" alt="Icon">
                        <div class="count-text h6">
                            <?= $lang_cart == 'ar' ? 'السلة ستنتهي في' : 'Your cart will expire in' ?>
                            <div class="js-countdown time-count cd-has-zero cd-no" data-timer="65" data-labels=":,:,:,">
                            </div>
                            <?= $lang_cart == 'ar' ? 'السلة ستنتهي في' : 'minutes! Please checkout now before your items sell out!' ?>
                        </div>
                    </div>
                    <div class="notification-progress">
                        <div class="text">
                            <i class="icon icon-truck"></i>
                            <p class="h6">
                                
                                <?= $lang_cart == 'ar' ? 'تسوق مجاني للطلبات' : 'Free Shipping for orders over' ?><span class="text-primary fw-bold"><?= $lang_cart == 'ar' ? '150 ريال سعودي' : '150 SAR' ?></span>
                            </p>
                        </div>
                        <div class="progress-cart">
                            <div class="value" style="width: 0%;" data-progress="50">
                                <span class="round"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <form>
                    <table class="tf-table-page-cart">
                        <thead>
                            <tr>
                                <th class="h6"><?= $lang_cart == 'ar' ? 'المنتج' : 'Product' ?></th>
                                <th class="h6"><?= $lang_cart == 'ar' ? 'السعر' : 'Price' ?></th>
                                <th class="h6"><?= $lang_cart == 'ar' ? 'الكمية' : 'Quality' ?></th>
                                <th class="h6"><?= $lang_cart == 'ar' ? 'السعر الإجمالي' : 'Total price' ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php
                            $cart = WC()->cart;

                            // if ($cart):
                            
                            foreach ($cart->get_cart() as $cart_item_key => $cart_item):
                                $product = $cart_item['data'];
                                if (!$product || !$product->exists() || $cart_item['quantity'] <= 0) {
                                    continue;
                                }
                                $product_id = $product->get_id();
                                $product_link = apply_filters('woocommerce_cart_item_permalink', $product->is_visible() ? $product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id($product_id), 'medium');

                                $name = $product->get_name();
                                $prices_r = $product->get_regular_price();
                                $prices_s = $product->get_sale_price();

                                ?>


                                <tr class="tf-cart_item each-prd"
                                    data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>">
                                    <td>
                                        <div class="cart_product">

                                            <a href="<?= $product_link; ?>" class="img-prd">
                                                <img class="lazyload" src="<?= $product_image[0]; ?>"
                                                    data-src="<?= $product_image[0]; ?>" alt="T Shirt">
                                            </a>
                                            <div class="infor-prd">
                                                <h6 class="prd_name">
                                                    <a href="<?= $product_link; ?>" class="link">
                                                        <?= $name; ?>
                                                    </a>
                                                </h6>
                                                <div class="prd_select text-small d-none">
                                                    <?= $lang_cart == 'ar' ? 'الحجم' : 'Size' ?>:
                                                    <div class="size-select">
                                                        <select class="bg-white">
                                                            <?php
                                                            // التحقق من وجود ألوان المنتج
                                                            if (have_rows('product_size2', $product_id)): ?>

                                                                <?php
                                                                $size_index = 0;
                                                                while (have_rows('product_size2', $product_id)):
                                                                    the_row();
                                                                    $size_name = get_sub_field('size_name');
                                                                    $size_code = get_sub_field('size_code');

                                                                    ?>

                                                                    <option selected="selected"
                                                                        data-product-id="<?php echo $product_id; ?>"
                                                                        data-size-name="<?php echo esc_attr($size_name); ?>"
                                                                        data-size-index="<?php echo $size_code; ?>">
                                                                        <?php echo $size_name; ?>
                                                                    </option>
                                                                    <?php
                                                                    $size_index++;
                                                                endwhile; ?>

                                                            <?php endif; ?>


                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="cart_price h6 each-price" data-cart-title="Price">
                                        <?php

                                        $currency = $lang_cart == 'ar' ? 'ريال' : 'SAR';
                                        if ($prices_s) {
                                            echo '<del>' . $prices_r . $currency.'</del>' . ' - ' . '<strong>' . $prices_s . $currency.'</strong>';
                                        } else {
                                            echo $prices_r . $currency;
                                        }
                                        ?>
                                    </td>
                                    <td class="tf-mini-cart-item file-delete"
                                        data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>">

                                        <div class="tf-mini-cart-info">

                                            <div class="d-flex justify-content-between align-items-center">
                                                <!-- أزرار التحكم في الكمية -->
                                                <div class="quantity-controls">
                                                    <button class="qty-btn minus" data-action="decrease">-</button>
                                                    <span
                                                        class="quantity-display"><?php echo esc_html($cart_item['quantity']); ?></span>
                                                    <button class="qty-btn plus" data-action="increase">+</button>
                                                </div>


                                            </div>
                                        </div>

                                    </td>

                                    <td class="h6 fw-semibold item-price">
                                        <?php
                                        echo WC()->cart->get_product_subtotal($product, $cart_item['quantity']);
                                        ?>
                                    </td>

                                    <td class="remove_from_cart_button icon link icon-close"
                                        data-product_id="<?php echo esc_attr($product_id); ?>"
                                        data-cart_item_key="<?php echo esc_attr($cart_item_key); ?>">
                                    </td>

                                </tr>


                                <?php
                            endforeach;
                            // endif;   
                            ?>

                        </tbody>
                    </table>
                    <!-- Coupon Code Input -->
                    <?php if (wc_coupons_enabled()): ?>
                        <div class="ip-discount-code">
                            <input type="text" name="coupon_code" id="coupon_code" placeholder="<?= pll_current_language() == 'ar' ? 'إضافة كوبون' : 'Add voucher discount'; ?>"
                                value="">
                            <button class="tf-btn animate-btn" type="button" id="apply-coupon-btn">
                                <?= pll_current_language() == 'ar' ? 'تطبيق الكوبون' : 'Apply Coupon'; ?>
                            </button>
                        </div>
                    <?php endif; ?>

                    <?= do_shortcode('[user_coupons]'); ?>
                </form>
            </div>

            <div class="col-xxl-3 col-xl-4">
                <div class="fl-sidebar-cart bg-white-smoke sticky-top">
                    <?php
                    $rt = $cart->get_total();
                    $data = tf_get_cart_discounts_breakdown();
                    $packages = WC()->shipping();

                    ?>

                    <div class="box-order-summary">
                        <h4 class="title fw-semibold"><?= pll_current_language() == 'ar' ? 'ملخص الطلب' : 'Order Summary'; ?></h4>
                        <div class="subtotal-ii h6 text-button d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold"><?= pll_current_language() == 'ar' ? 'المجموع' : 'Subtotal'; ?></h6>
                           
                            <span class="total-mer cart-subtotal-mer">
                                <?php echo WC()->cart->get_cart_subtotal(); ?>
                            </span>
                        </div>
                        <div class="discount  text-button d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold"><?= pll_current_language() == 'ar' ? 'الخصومات' : 'Discounts'; ?></h6>
                            
                            <?php
                            $total_discount = array_reduce($data['items'], function ($carry, $item) {
                                return $carry + $item['discount_percent'];
                            }, 0);
                            ?>
                            <span class="total discon h6"><?= $total_discount; ?>%</span>
                        </div>
                        
                        <div class="ship">
                            <h6 class="fw-bold"><?= pll_current_language() == 'ar' ? 'الشحن' : 'Shipping'; ?></h6>
                            <div class="flex-grow-1">
                                <?php
                                // الحصول على طرق الشحن المتاحة
                                $packages = WC()->shipping()->get_packages();
                                if (!empty($packages)) {
                                    foreach ($packages as $package_key => $package) {
                                        if (!empty($package['rates'])) {
                                            $rates = $package['rates'];
                                            $chosen_methods = WC()->session->get('chosen_shipping_methods');
                                            $chosen = isset($chosen_methods[$package_key]) ? $chosen_methods[$package_key] : '';
                                            
                                            foreach ($rates as $rate_id => $rate): ?>
                                                <fieldset class="ship-item">
                                                    <input type="radio" name="shipping_method[<?php echo $package_key; ?>]"
                                                        id="rate-<?php echo esc_attr($rate_id); ?>"
                                                        value="<?php echo esc_attr($rate_id); ?>" 
                                                        <?php checked($rate_id, $chosen); ?> />

                                                    <label for="rate-<?php echo esc_attr($rate_id); ?>">
                                                        <?php echo esc_html($rate->get_label()); ?> –
                                                        <?php echo wc_price($rate->get_cost() + array_sum($rate->get_taxes())); ?>
                                                    </label>
                                                </fieldset>
                                            <?php endforeach;
                                        }
                                    }
                                } else {
                                    echo pll_current_language() == 'ar' ? 'لا طرق شحن متاحة' : 'No shipping methods available.';
                                }
                                ?>
                            </div>
                        </div>
                        <h5 class="total-order d-flex justify-content-between align-items-center">
                            <span><?= pll_current_language() == 'ar' ? 'المجموع' : 'Total'; ?></span>
                            <span class="total-mer cart-total-mer">
                                <?php echo WC()->cart->get_cart_total(); ?>
                            </span>
                        </h5>
                        <div class="list-ver">
                            <a href="<?php echo wc_get_checkout_url(); ?>" class="tf-btn w-100 animate-btn">
                                <?= pll_current_language() == 'ar' ? 'الانتقال للدفع' : 'Process to checkout'; ?>
                                <i class="icon icon-arrow-right"></i>
                            </a>
                            <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="tf-btn btn-white animate-btn animate-dark w-100">
                                <?= pll_current_language() == 'ar' ? 'مواصلة التسوق' : 'Continue shopping'; ?>
                                <i class="icon icon-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>
</div>
<!-- /View Cart -->

<?php get_footer(); ?>