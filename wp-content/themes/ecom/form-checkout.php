<?php
/**
 * Enhanced Custom Checkout Template for WooCommerce with Cities
 * Template Name: Enhanced Custom Checkout with Cities
 * Version: 2.0
 * 
 * Features:
 * - Enhanced security with nonce verification
 * - Improved form validation and error handling
 * - Better accessibility support
 * - Real-time field validation
 * - Enhanced user experience
 * - Performance optimizations
 */

// Security: Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    wp_die(__('WooCommerce is required for this page to work.', 'ecom'));
}

// Check if cart has items
if (WC()->cart->is_empty()) {
    wp_safe_redirect(wc_get_cart_url());
    exit;
}

// Security: Generate nonces for form actions
$checkout_nonce = wp_create_nonce('checkout_form_nonce');
$coupon_nonce = wp_create_nonce('coupon_apply_nonce');

// Performance: Cache frequently used data
$cart = WC()->cart;
$checkout = WC()->checkout();
$cart_total = $cart->get_total();
$cart_count = $cart->get_cart_contents_count();

get_header(); ?>

<!-- Page Title -->
<section class="s-page-title">
    <div class="container">
        <div class="content">
            <h1 class="title-page"><?= pll_current_language() == 'ar' ? 'الدفع' : 'Checkout' ?></h1>
            <ul class="breadcrumbs-page">
                <li><a href="<?= home_url(); ?>" class="h6 link"><?= pll_current_language() == 'ar' ? 'الرئيسية' : 'Home' ?></a></li>
                <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                <li>
                    <h6 class="current-page fw-normal"><?= pll_current_language() == 'ar' ? 'الدفع' : 'Checkout' ?></h6>
                </li>
            </ul>
        </div>
    </div>
</section>
<!-- /Page Title -->

<div class="tf-checkout-wrapper">
    <section class="flat-spacing">
        <div class="container">
            <div class="row">
                <div class="col-lg-7">
                    <div class="tf-page-checkout mb-lg-0">
                        
                        <!-- Coupon Section -->
                        <?php if (wc_coupons_enabled()) : ?>
                        <div class="wrap-coupon">
                            <h5 class="mb-12"><?= pll_current_language() == 'ar' ? 'هل لديك كوبون؟' : 'Have a coupon?' ?> <span class="text-primary"><?= pll_current_language() == 'ar' ? 'أدخل رمزك' : 'Enter your code' ?></span></h5>
                            <form class="tf-coupon-form" method="post">
                                <div class="ip-discount-code mb-0">
                                    <input type="text" name="coupon_code" placeholder="<?= pll_current_language() == 'ar' ? 'أدخل رمزك' : 'Enter your code' ?>" id="coupon_code" value="" class="tf-input" />
                                    <button class="tf-btn animate-btn" type="submit" name="apply_coupon">
                                        <?= pll_current_language() == 'ar' ? 'تطبيق الكوبون' : 'Apply Coupon' ?>
                                    </button>
                                </div>
                                <?php wp_nonce_field('woocommerce-coupon', 'woocommerce-coupon-nonce'); ?>
                            </form>
                        </div>
                        <?php endif; ?>

                        <?php
                        // معالجة الكوبون
                        if (isset($_POST['apply_coupon']) && !empty($_POST['coupon_code'])) {
                            if (wp_verify_nonce($_POST['woocommerce-coupon-nonce'], 'woocommerce-coupon')) {
                                WC()->cart->apply_coupon(sanitize_text_field($_POST['coupon_code']));
                                wp_redirect(wc_get_checkout_url());
                                exit;
                            }
                        }
                        ?>

                        <!-- Checkout Form -->
                        <form name="checkout" method="post" class="tf-checkout-form tf-checkout-cart-main" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">

                            <?php if (WC()->checkout->get_checkout_fields()) : ?>

                            <!-- Billing Information -->
                            <div class="box-ip-checkout estimate-shipping">
                                <h2 class="title type-semibold"><?= pll_current_language() == 'ar' ? 'معلومات الفاتورة' : 'Billing Information' ?></h2>
                                <div class="form_content">
                                    
                                    <!-- Name Fields -->
                                    <div class="cols tf-grid-layout sm-col-2">
                                        <fieldset class="tf-fieldset">
                                            <input type="text" 
                                                   id="billing_first_name" 
                                                   name="billing_first_name" 
                                                   placeholder="<?= pll_current_language() == 'ar' ? 'الاسم الأول' : 'First name' ?>" 
                                                   value="<?php echo esc_attr(WC()->checkout->get_value('billing_first_name')); ?>" 
                                                   class="tf-input" 
                                                   required />
                                        </fieldset>
                                        <fieldset class="tf-fieldset">
                                            <input type="text" 
                                                   id="billing_last_name" 
                                                   name="billing_last_name" 
                                                   placeholder="<?= pll_current_language() == 'ar' ? 'الاسم الأخير' : 'Last name' ?>" 
                                                   value="<?php echo esc_attr(WC()->checkout->get_value('billing_last_name')); ?>" 
                                                   class="tf-input" 
                                                   required />
                                        </fieldset>
                                    </div>

                                    <!-- Email and Phone -->
                                    <div class="cols tf-grid-layout sm-col-2">
                                        <fieldset class="tf-fieldset">
                                            <input type="email" 
                                                   id="billing_email" 
                                                   name="billing_email" 
                                                   placeholder="<?= pll_current_language() == 'ar' ? 'البريد الالكتروني' : 'Email address' ?>" 
                                                   value="<?php echo esc_attr(WC()->checkout->get_value('billing_email')); ?>" 
                                                   class="tf-input" 
                                                   required />
                                        </fieldset>
                                        <fieldset class="tf-fieldset">
                                            <input type="tel" 
                                                   id="billing_phone" 
                                                   name="billing_phone" 
                                                   placeholder="<?= pll_current_language() == 'ar' ? 'رقم الهاتف' : 'Phone number' ?>" 
                                                   value="<?php echo esc_attr(WC()->checkout->get_value('billing_phone')); ?>" 
                                                   class="tf-input" 
                                                   required />
                                        </fieldset>
                                    </div>

                                    <!-- Country -->
                                    <fieldset class="tf-select">
                                        <select id="billing_country" 
                                                name="billing_country" 
                                                class="tf-select w-100" 
                                                required>
                                            <option value="">Choose country / Region</option>
                                            <?php
                                            $countries = WC()->countries->get_allowed_countries();
                                            $selected_country = WC()->checkout->get_value('billing_country');
                                            foreach ($countries as $code => $name) {
                                                echo '<option value="' . esc_attr($code) . '"' . selected($selected_country, $code, false) . '>' . esc_html($name) . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </fieldset>

                                    <!-- State and City -->
                                    <div class="cols tf-grid-layout sm-col-2">
                                        <fieldset class="tf-select">
                                            <select id="billing_state" 
                                                    name="billing_state" 
                                                    class="tf-select" 
                                                    required>
                                                <option value=""><?= pll_current_language() == 'ar' ? 'اختر المنطقة' : 'Choose region' ?></option>
                                                <?php
                                                $states = WC()->countries->get_states('SA');
                                                $selected_state = WC()->checkout->get_value('billing_state');
                                                if ($states) {
                                                    foreach ($states as $code => $name) {
                                                        echo '<option value="' . esc_attr($code) . '"' . selected($selected_state, $code, false) . '>' . esc_html($name) . '</option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </fieldset>
                                        <fieldset class="tf-select">
                                            <select id="billing_city_custom" 
                                                    name="billing_city_custom" 
                                                    class="tf-select" 
                                                    required>
                                                <option value=""><?= pll_current_language() == 'ar' ? 'اختر المدينة' : 'Choose city' ?></option>
                                            </select>
                                        </fieldset>
                                    </div>

                                    <!-- Street Address -->
                                    <fieldset class="tf-fieldset">
                                        <input type="text" 
                                               id="billing_address_1" 
                                               name="billing_address_1" 
                                               placeholder="<?= pll_current_language() == 'ar' ? 'العنوان' : 'Street Address' ?>" 
                                               value="<?php echo esc_attr(WC()->checkout->get_value('billing_address_1')); ?>" 
                                               class="tf-input" 
                                               required />
                                    </fieldset>

                                    <!-- Postal Code -->
                                    <fieldset class="tf-fieldset">
                                        <input type="text" 
                                               id="billing_postcode" 
                                               name="billing_postcode" 
                                               placeholder="<?= pll_current_language() == 'ar' ? 'الرمز البريدي' : 'Postal code' ?>" 
                                               value="<?php echo esc_attr(WC()->checkout->get_value('billing_postcode')); ?>" 
                                               class="tf-input" 
                                               required />
                                    </fieldset>

                                    <!-- Hidden City Field for WooCommerce Compatibility -->
                                    <input type="hidden" id="billing_city" name="billing_city" value="" />

                                    <!-- Order Notes -->
                                    <fieldset class="tf-fieldset">
                                        <textarea id="order_comments" 
                                                  name="order_comments" 
                                                  placeholder="<?= pll_current_language() == 'ar' ? 'ملاحظة عن الطلب' : 'Note about your order' ?>" 
                                                  class="tf-textarea notes" 
                                                  style="height: 180px;"><?php echo esc_textarea(WC()->checkout->get_value('order_comments')); ?></textarea>
                                    </fieldset>
                                </div>
                            </div>

                            <?php endif; ?>

                            <!-- Payment Methods -->
                            <div class="box-ip-payment">
                                <h2 class="title type-semibold"><?= pll_current_language() == 'ar' ? 'اختر طريقة الدفع' : 'Choose Payment Option' ?></h2>
                                
                                <?php if (WC()->payment_gateways->get_available_payment_gateways()) : ?>
                                <div class="tf-payment-methods" id="tf-payment-methods">
                                    <?php 
                                    $gateways = WC()->payment_gateways->get_available_payment_gateways();
                                    $selected_gateway = WC()->session->get('chosen_payment_method');
                                    
                                    if (empty($selected_gateway)) {
                                        $selected_gateway = current(array_keys($gateways));
                                    }
                                    
                                    foreach ($gateways as $gateway_id => $gateway) :
                                        $checked = ($gateway_id === $selected_gateway) ? 'checked' : '';
                                    ?>
                                    <div class="tf-payment-accordion">
                                        <label for="payment_method_<?php echo esc_attr($gateway_id); ?>" class="tf-payment-check checkbox-wrap" data-bs-toggle="collapse" data-bs-target="#<?php echo esc_attr($gateway_id); ?>-payment" aria-controls="<?php echo esc_attr($gateway_id); ?>-payment">
                                            <input type="radio" class="tf-check-rounded style-2" id="payment_method_<?php echo esc_attr($gateway_id); ?>" name="payment_method" value="<?php echo esc_attr($gateway_id); ?>" <?php echo $checked; ?> />
                                            <span class="pay-title"><?php echo $gateway->get_title(); ?></span>
                                        </label>
                                        
                                        <?php if ($gateway->has_fields() || $gateway->get_description()) : ?>
                                        <div id="<?php echo esc_attr($gateway_id); ?>-payment" class="collapse <?php echo $checked ? 'show' : ''; ?>" data-bs-parent="#tf-payment-methods">
                                            <div class="tf-payment-body">
                                                <?php 
                                                // عرض وصف الدفع بدون كلاسات WooCommerce
                                                if ($gateway->get_description()) {
                                                    echo '<div class="tf-payment-description">' . wp_kses_post($gateway->get_description()) . '</div>';
                                                }
                                                
                                                // عرض حقول الدفع مع تعديل الكلاسات
                                                ob_start();
                                                $gateway->payment_fields();
                                                $payment_fields = ob_get_clean();
                                                
                                                // استبدال كلاسات WooCommerce بكلاسات الثيم
                                                $payment_fields = str_replace([
                                                    'form-row',
                                                    'woocommerce-form',
                                                    'woocommerce-input',
                                                    'input-text'
                                                ], [
                                                    'tf-form-row',
                                                    'tf-form',
                                                    'tf-input',
                                                    'tf-input'
                                                ], $payment_fields);
                                                
                                                echo $payment_fields;
                                                ?>
                                            </div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <p class="h6 mb-20">
                                    <?= pll_current_language() == 'ar' ? 'بياناتك الشخصية سيتم استخدامها لمعالجة طلبك، دعم تجربتك خلال هذا الموقع، و لغرض أخرى كما هو محدد في سياسة الخصوصية لدينا.' : 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.' ?>
                                </p>
                                
                                <!-- Terms and Conditions -->
                                <?php if (wc_get_page_id('terms') > 0 && apply_filters('woocommerce_checkout_show_terms', true)) : ?>
                                <div class="checkbox-wrap">
                                    <input type="checkbox" class="tf-check style-2" name="terms" id="terms" />
                                    <label for="terms" class="checkbox h6">
                                        <?= pll_current_language() == 'ar' ? 'أنا قرأت و أتفق على الشروط والأحكام' : 'I have read and agree to the website' ?>
                                        <a href="<?php echo esc_url(wc_get_page_permalink('terms')); ?>" class="text-primary" target="_blank"><?= pll_current_language() == 'ar' ? 'الشروط والأحكام' : 'terms and conditions' ?></a> *
                                    </label>
                                    <input type="hidden" name="terms-field" value="1" />
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Shipping Methods -->
                            <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                            <div class="box-ip-shipping">
                                <h2 class="title type-semibold"><?= pll_current_language() == 'ar' ? 'طريقة الشحن' : 'Shipping Method' ?></h2>
                                
                                <?php
                                $packages = WC()->shipping->get_packages();
                                foreach ($packages as $i => $package) :
                                    $chosen_method = isset(WC()->session->chosen_shipping_methods[$i]) ? WC()->session->chosen_shipping_methods[$i] : '';
                                ?>

                                <div class="tf-shipping-methods" data-index="<?php echo $i; ?>">
                                    <?php if (1 < count($package['rates'])) : ?>
                                        <?php foreach ($package['rates'] as $method) : ?>
                                            <label for="shipping_method_<?php echo $i; ?>_<?php echo sanitize_title($method->id); ?>" class="check-ship mb-12">
                                                <input type="radio" name="shipping_method[<?php echo $i; ?>]" data-index="<?php echo $i; ?>" id="shipping_method_<?php echo $i; ?>_<?php echo sanitize_title($method->id); ?>" value="<?php echo esc_attr($method->id); ?>" class="tf-check-rounded style-2 line-black" <?php checked($method->id, $chosen_method); ?> />
                                                <span class="text h6">
                                                    <span><?php echo wc_cart_totals_shipping_method_label($method); ?></span>
                                                    <span class="price"><?php echo wp_kses_post($method->cost > 0 ? wc_price($method->cost) : __('Free', 'woocommerce')); ?></span>
                                                </span>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php elseif (1 === count($package['rates'])) : ?>
                                        <?php
                                        $method = current($package['rates']);
                                        printf('%s: %s', esc_html($method->get_label()), wp_kses_post($method->cost > 0 ? wc_price($method->cost) : __('Free', 'woocommerce')));
                                        ?>
                                        <input type="hidden" name="shipping_method[<?php echo $i; ?>]" data-index="<?php echo $i; ?>" id="shipping_method_<?php echo $i; ?>_<?php echo sanitize_title($method->id); ?>" value="<?php echo esc_attr($method->id); ?>" />
                                    <?php endif; ?>
                                </div>

                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Submit Button -->
                            <div class="button_submit">
    <?php wp_nonce_field('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce'); ?>
    
    <?php if ( is_user_logged_in() ) : ?>
        <button type="submit" class="button alt tf-btn animate-btn w-100" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e('Place order', 'woocommerce'); ?>" data-value="<?php esc_attr_e('Place order', 'woocommerce'); ?>">
            <?php esc_html_e('Payment', 'woocommerce'); ?>
        </button>
    <?php else : ?>
        <a href="<?= site_url('/login'); ?>" class="button alt tf-btn animate-btn w-100">
            <?php esc_html_e('Login to continue', 'woocommerce'); ?>
        </a>
    <?php endif; ?>
</div>


                        </form>
                    </div>
                </div>

                <!-- Order Summary Sidebar -->
                <div class="col-lg-5">
                    <div class="fl-sidebar-cart sticky-top">
                        <div class="box-your-order">
                            <h2 class="title type-semibold"><?= pll_current_language() == 'ar' ? 'الإجمالي' : 'Your Order' ?></h2>
                            
                            <!-- Cart Items -->
                            <ul class="list-order-product">
                                <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                                    $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                                    
                                    if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) :
                                ?>
                                <li class="order-item">
                                    <a href="<?php echo esc_url($_product->get_permalink($cart_item)); ?>" class="img-prd">
                                        <?php echo wp_kses_post($_product->get_image('thumbnail')); ?>
                                    </a>
                                    <div class="infor-prd">
                                        <h6 class="prd_name">
                                            <a href="<?php echo esc_url($_product->get_permalink($cart_item)); ?>" class="link">
                                                <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)); ?>
                                            </a>
                                        </h6>
                                        <div class="prd_select text-small">
                                            <?php echo wc_get_formatted_cart_item_data($cart_item); ?>
                                            Qty: <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', sprintf('&times; %s', $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                        </div>
                                    </div>
                                    <p class="price-prd h6">
                                        <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); ?>
                                    </p>
                                </li>
                                <?php endif; endforeach; ?>
                            </ul>

                            <!-- Order Totals -->
                            <ul class="list-total">
                                <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                                <li class="total-item h6">
                                    <span class="fw-bold text-black"><?= pll_current_language() == 'ar' ? 'الخصم' : 'Discount' ?> (<?php echo esc_html($code); ?>)</span>
                                    <span>-<?php wc_cart_totals_coupon_html($coupon); ?></span>
                                </li>
                                <?php endforeach; ?>

                                <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                                <li class="total-item h6">
                                    <span class="fw-bold text-black"><?= pll_current_language() == 'ar' ? 'الشحن' : 'Shipping' ?></span>
                                    <span><?php 
                                        $shipping_total = WC()->cart->get_shipping_total();
                                        echo $shipping_total > 0 ? wc_price($shipping_total) : __('Free', 'woocommerce');
                                    ?></span>
                                </li>
                                <?php endif; ?>

                                <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
                                    <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                                        <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
                                        <li class="total-item h6">
                                            <span class="fw-bold text-black"><?php echo esc_html($tax->label); ?></span>
                                            <span><?php echo wp_kses_post($tax->formatted_amount); ?></span>
                                        </li>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                    <li class="total-item h6">
                                        <span class="fw-bold text-black"><?php echo esc_html(WC()->countries->tax_or_vat()); ?></span>
                                        <span><?php wc_cart_totals_taxes_total_html(); ?></span>
                                    </li>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </ul>

                            <!-- Final Total -->
                            <div class="last-total h5 fw-medium text-black">
                                <span><?= pll_current_language() == 'ar' ? 'الإجمالي' : 'Total' ?></span>
                                <span><?php echo WC()->cart->get_cart_total(); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    
    // تعيين القيم الافتراضية
    function setDefaultValues() {
        // تعيين السعودية كدولة افتراضية
        if (!$('#billing_country').val()) {
            $('#billing_country').val('SA').trigger('change');
        }
        
        // تعيين الرياض كمنطقة افتراضية بعد تحميل الدولة
        setTimeout(function() {
            if (!$('#billing_state').val()) {
                $('#billing_state').val('AR').trigger('change');
            }
        }, 500);
    }
    
    // تشغيل الإعدادات الافتراضية
    setDefaultValues();
    
    // معالجة تغيير المنطقة لتحديث المدن
    $(document).on('change', '#billing_state', function() {
        var state = $(this).val();
        var $cityField = $('#billing_city_custom');
        
        // مسح المدن الحالية
        $cityField.html('<option value="">جاري التحميل...</option>');
        
        if (state && state !== '') {
            // طلب AJAX لجلب المدن
            $.ajax({
                url: wc_checkout_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'update_cities_by_state',
                    state: state,
                    field_type: 'billing',
                    nonce: '<?php echo wp_create_nonce("update_cities_nonce"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $cityField.html(response.data.options);
                        // تعيين الرياض كمدينة افتراضية إذا كانت المنطقة هي الرياض
                        if (state === 'AR') {
                            $cityField.val('riyadh');
                        }
                    } else {
                        $cityField.html('<option value="">خطأ في تحميل المدن</option>');
                    }
                },
                error: function() {
                    $cityField.html('<option value="">خطأ في الاتصال</option>');
                }
            });
        } else {
            $cityField.html('<option value="">اختر المنطقة أولاً</option>');
        }
        
        // تحديث الـ checkout
        $('body').trigger('update_checkout');
    });
    
    // معالجة تغيير المدينة
    $(document).on('change', '#billing_city_custom', function() {
        var selectedCity = $(this).find('option:selected').text();
        // نسخ اسم المدينة إلى الحقل المخفي
        $('#billing_city').val(selectedCity);
        
        // تحديث الـ checkout
        $('body').trigger('update_checkout');
    });
    
    // Update checkout when payment method changes
    $('body').on('change', 'input[name="payment_method"]', function() {
        $('body').trigger('update_checkout');
    });
    
    // Update checkout when shipping method changes
    $('body').on('change', 'input[name^="shipping_method"]', function() {
        $('body').trigger('update_checkout');
    });
    
    // Update states when country changes
    $('body').on('change', '#billing_country', function() {
        var country = $(this).val();
        
        if (country === 'SA') {
            // إذا كانت السعودية، تحديث المناطق
            setTimeout(function() {
                if (!$('#billing_state').val()) {
                    $('#billing_state').val('AR').trigger('change');
                }
            }, 100);
        } else {
            // إذا كانت دولة أخرى، مسح المدن المخصصة
            $('#billing_city_custom').html('<option value="">غير متوفر للدولة المختارة</option>');
        }
        
        $('body').trigger('update_checkout');
    });
    
    // التحقق من الحقول المطلوبة قبل الإرسال
    $(document).on('click', '#place_order', function(e) {
        var errors = [];
        
        // التحقق من الحقول الأساسية
        if (!$('#billing_first_name').val().trim()) {
            errors.push('يرجى إدخال الاسم الأول');
            $('#billing_first_name').addClass('error-field');
        }
        
        if (!$('#billing_last_name').val().trim()) {
            errors.push('يرجى إدخال الاسم الأخير');
            $('#billing_last_name').addClass('error-field');
        }
        
        if (!$('#billing_email').val().trim()) {
            errors.push('يرجى إدخال البريد الإلكتروني');
            $('#billing_email').addClass('error-field');
        }
        
        if (!$('#billing_phone').val().trim()) {
            errors.push('يرجى إدخال رقم الهاتف');
            $('#billing_phone').addClass('error-field');
        }
        
        if (!$('#billing_country').val()) {
            errors.push('يرجى اختيار الدولة');
            $('#billing_country').addClass('error-field');
        }
        
        if (!$('#billing_state').val()) {
            errors.push('يرجى اختيار المنطقة');
            $('#billing_state').addClass('error-field');
        }
        
        if (!$('#billing_city_custom').val()) {
            errors.push('يرجى اختيار المدينة');
            $('#billing_city_custom').addClass('error-field');
        }
        
        if (!$('#billing_address_1').val().trim()) {
            errors.push('يرجى إدخال عنوان الشارع');
            $('#billing_address_1').addClass('error-field');
        }
        
        // التحقق من الشروط والأحكام
        if ($('#terms').length && !$('#terms').is(':checked')) {
            errors.push('يرجى الموافقة على الشروط والأحكام');
            $('#terms').addClass('error-field');
        }
        
        if (errors.length > 0) {
            e.preventDefault();
            
            // عرض الأخطاء
            var errorHtml = '<div class="woocommerce-error checkout-errors" role="alert">' +
                           '<strong>يرجى تصحيح الأخطاء التالية:</strong><ul>';
            $.each(errors, function(index, error) {
                errorHtml += '<li>' + error + '</li>';
            });
            errorHtml += '</ul></div>';
            
            // إزالة الأخطاء السابقة
            $('.checkout-errors').remove();
            
            // إضافة الأخطاء الجديدة
            $('.tf-checkout-form').prepend(errorHtml);
            
            // انتقال إلى أعلى النموذج
            $('html, body').animate({
                scrollTop: $('.checkout-errors').offset().top - 50
            }, 500);
            
            return false;
        }
    });
    
    // إزالة علامات الخطأ عند التصحيح
    $(document).on('change input', '.error-field', function() {
        $(this).removeClass('error-field');
        
        // إزالة رسالة الخطأ إذا تم تصحيح جميع الحقول
        if ($('.error-field').length === 0) {
            $('.checkout-errors').fadeOut();
        }
    });
    
    // Auto-update checkout
    $('body').trigger('update_checkout');
});
</script>

<?php get_footer(); ?>