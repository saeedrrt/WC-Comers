<?php
/**
 * Custom Thank You Page Template for WooCommerce
 * Template Name: Custom Thank You Page
 * Version: 1.0
 * 
 * Features:
 * - Order details display
 * - Payment status handling
 * - Security and validation
 * - Enhanced user experience
 * - Multi-language support
 */

// Security: Check if WooCommerce is active
if (!class_exists('WooCommerce')) {
    wp_die(__('WooCommerce is required for this page to work.', 'ecom'));
}

// Get order ID from URL parameter or session
$order_id = 0;
$order = null;

// Try to get order ID from different sources
if (isset($_GET['order_id'])) {
    $order_id = absint($_GET['order_id']);
} elseif (isset($_GET['key'])) {
    $order_key = wc_clean($_GET['key']);
    $order_id = wc_get_order_id_by_order_key($order_key);
} elseif (WC()->session && WC()->session->get('order_awaiting_payment')) {
    $order_id = absint(WC()->session->get('order_awaiting_payment'));
}

// Validate and get order
if ($order_id > 0) {
    $order = wc_get_order($order_id);

    // Security check: verify order belongs to current user or check order key
    if ($order) {
        $valid_access = false;

        if (is_user_logged_in()) {
            // For logged in users, check if order belongs to them
            $valid_access = ($order->get_customer_id() == get_current_user_id());
        } else {
            // For guests, check order key
            if (isset($_GET['key'])) {
                $order_key = wc_clean($_GET['key']);
                $valid_access = hash_equals($order->get_order_key(), $order_key);
            }
        }

        if (!$valid_access) {
            wp_safe_redirect(wc_get_page_permalink('shop'));
            exit;
        }
    }
}

// If no valid order found, redirect to shop
if (!$order) {
    wp_safe_redirect(wc_get_page_permalink('shop'));
    exit;
}

// Clear cart after successful order
WC()->cart->empty_cart();

// Remove order from session
if (WC()->session) {
    WC()->session->set('order_awaiting_payment', null);
}

// Get order details
$order_status = $order->get_status();
$order_total = $order->get_total();
$order_date = $order->get_date_created();
$payment_method_title = $order->get_payment_method_title();
$billing_address = $order->get_formatted_billing_address();
$shipping_address = $order->get_formatted_shipping_address();
$order_items = $order->get_items();

// Check if order needs payment
$needs_payment = $order->needs_payment();

get_header(); ?>

<div class="tf-thankyou-wrapper">
    <section class="flat-spacing">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="tf-thankyou-content text-center">

                        <?php if ($order_status === 'processing' || $order_status === 'completed'): ?>
                            <!-- Success Message -->
                            <div class="thankyou-icon mb-20">
                                <i class="icon icon-check-circle text-success" style="font-size: 64px;"></i>
                            </div>

                            <h1 class="thankyou-title mb-12">شكراً لك!</h1>
                            <h2 class="thankyou-subtitle mb-20">تم استلام طلبك بنجاح</h2>

                            <p class="thankyou-message mb-30">
                                سيتم معالجة طلبك في أقرب وقت ممكن. ستتلقى رسالة تأكيد عبر البريد الإلكتروني قريباً.
                            </p>

                        <?php elseif ($order_status === 'pending'): ?>
                            <!-- Pending Payment -->
                            <div class="thankyou-icon mb-20">
                                <i class="icon icon-clock text-warning" style="font-size: 64px;"></i>
                            </div>

                            <h1 class="thankyou-title mb-12">في انتظار الدفع</h1>
                            <h2 class="thankyou-subtitle mb-20">طلبك في انتظار إتمام عملية الدفع</h2>

                            <p class="thankyou-message mb-30">
                                يرجى إكمال عملية الدفع لتأكيد طلبك. يمكنك الدفع الآن باستخدام الرابط أدناه.
                            </p>

                            <?php if ($needs_payment): ?>
                                <div class="payment-actions mb-30">
                                    <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>"
                                        class="tf-btn animate-btn btn-lg">
                                        إتمام الدفع الآن
                                    </a>
                                </div>
                            <?php endif; ?>

                        <?php elseif ($order_status === 'failed'): ?>
                            <!-- Failed Order -->
                            <div class="thankyou-icon mb-20">
                                <i class="icon icon-x-circle text-danger" style="font-size: 64px;"></i>
                            </div>

                            <h1 class="thankyou-title mb-12">فشل في معالجة الطلب</h1>
                            <h2 class="thankyou-subtitle mb-20">حدث خطأ أثناء معالجة طلبك</h2>

                            <p class="thankyou-message mb-30">
                                عذراً، لم نتمكن من معالجة طلبك. يرجى المحاولة مرة أخرى أو الاتصال بخدمة العملاء.
                            </p>

                            <div class="failed-actions mb-30">
                                <a href="<?php echo esc_url(wc_get_checkout_url()); ?>"
                                    class="tf-btn animate-btn btn-lg me-10">
                                    المحاولة مرة أخرى
                                </a>
                                <a href="<?php echo esc_url(wc_get_page_permalink('contact')); ?>"
                                    class="tf-btn outline animate-btn btn-lg">
                                    اتصل بنا
                                </a>
                            </div>

                        <?php else: ?>
                            <!-- Other statuses -->
                            <div class="thankyou-icon mb-20">
                                <i class="icon icon-info-circle text-info" style="font-size: 64px;"></i>
                            </div>

                            <h1 class="thankyou-title mb-12">تم استلام طلبك</h1>
                            <h2 class="thankyou-subtitle mb-20">رقم الطلب: #<?php echo $order->get_order_number(); ?></h2>

                        <?php endif; ?>

                        <!-- Order Summary Card -->
                        <div class="order-summary-card text-start">
                            <div class="card-header">
                                <h3 class="mb-0">تفاصيل الطلب</h3>
                            </div>

                            <div class="card-body">
                                <!-- Order Info -->
                                <div class="order-info mb-20">
                                    <div class="row">
                                        <div class="col-sm-6 mb-10">
                                            <strong>رقم الطلب:</strong> #<?php echo $order->get_order_number(); ?>
                                        </div>
                                        <div class="col-sm-6 mb-10">
                                            <strong>تاريخ الطلب:</strong> <?php echo $order_date->date_i18n('d F Y'); ?>
                                        </div>
                                        <div class="col-sm-6 mb-10">
                                            <strong>حالة الطلب:</strong>
                                            <span class="order-status status-<?php echo $order_status; ?>">
                                                <?php echo wc_get_order_status_name($order_status); ?>
                                            </span>
                                        </div>
                                        <div class="col-sm-6 mb-10">
                                            <strong>طريقة الدفع:</strong> <?php echo $payment_method_title; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Order Items -->
                                <div class="order-items mb-20">
                                    <h4 class="section-title">المنتجات المطلوبة</h4>
                                    <div class="items-list">
                                        <?php foreach ($order_items as $item_id => $item):
                                            $product = $item->get_product();
                                            if (!$product)
                                                continue;
                                            ?>
                                            <div class="order-item d-flex align-items-center mb-15">
                                                <div class="item-image me-15">
                                                    <?php echo wp_kses_post($product->get_image('thumbnail')); ?>
                                                </div>
                                                <div class="item-details flex-grow-1">
                                                    <h6 class="item-name mb-5">
                                                        <?php echo wp_kses_post($item->get_name()); ?>
                                                    </h6>
                                                    <div class="item-meta text-small text-muted">
                                                        <?php echo wp_kses_post(wc_display_item_meta($item, array('before' => '', 'after' => '', 'separator' => ', '))); ?>
                                                    </div>
                                                    <div class="item-quantity">
                                                        الكمية: <?php echo $item->get_quantity(); ?>
                                                    </div>
                                                </div>
                                                <div class="item-price">
                                                    <strong><?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?></strong>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Order Totals -->
                                <div class="order-totals">
                                    <h4 class="section-title">إجمالي الطلب</h4>
                                    <div class="totals-table">
                                        <div class="total-row">
                                            <span>المجموع الفرعي:</span>
                                            <span><?php echo wp_kses_post($order->get_subtotal_to_display()); ?></span>
                                        </div>

                                        <?php if ($order->get_shipping_total() > 0): ?>
                                            <div class="total-row">
                                                <span>الشحن:</span>
                                                <span><?php echo wp_kses_post(wc_price($order->get_shipping_total())); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($order->get_total_tax() > 0): ?>
                                            <div class="total-row">
                                                <span>الضريبة:</span>
                                                <span><?php echo wp_kses_post(wc_price($order->get_total_tax())); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ($order->get_total_discount() > 0): ?>
                                            <div class="total-row discount">
                                                <span>الخصم:</span>
                                                <span>-<?php echo wp_kses_post(wc_price($order->get_total_discount())); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="total-row final-total">
                                            <span><strong>المجموع الكلي:</strong></span>
                                            <span><strong><?php echo wp_kses_post(wc_price($order_total)); ?></strong></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="address-info mt-30">
                            <div class="row">
                                <?php if ($billing_address): ?>
                                    <div class="col-md-6 mb-20">
                                        <div class="address-card">
                                            <h4 class="address-title">عنوان الفاتورة</h4>
                                            <div class="address-content">
                                                <?php echo wp_kses_post($billing_address); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($shipping_address && $shipping_address !== $billing_address): ?>
                                    <div class="col-md-6 mb-20">
                                        <div class="address-card">
                                            <h4 class="address-title">عنوان الشحن</h4>
                                            <div class="address-content">
                                                <?php echo wp_kses_post($shipping_address); ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons mt-40">
                            <div class="d-flex flex-wrap justify-content-center gap-15">

                                <?php if (is_user_logged_in()): ?>
                                    <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>"
                                        class="tf-btn outline animate-btn">
                                        عرض جميع الطلبات
                                    </a>
                                <?php endif; ?>

                                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"
                                    class="tf-btn animate-btn">
                                    متابعة التسوق
                                </a>

                                <?php if ($order_status === 'processing' || $order_status === 'completed'): ?>
                                    <button onclick="window.print()" class="tf-btn outline animate-btn">
                                        طباعة الطلب
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Contact Support -->
                        <div class="support-info mt-40 p-20 bg-light rounded">
                            <h5>هل تحتاج للمساعدة؟</h5>
                            <p class="mb-15">
                                إذا كان لديك أي استفسارات حول طلبك، لا تتردد في الاتصال بفريق خدمة العملاء لدينا.
                            </p>
                            <div class="contact-methods">
                                <a href="mailto:support@example.com" class="contact-link me-20">
                                    <i class="icon icon-mail"></i> support@example.com
                                </a>
                                <a href="tel:+966123456789" class="contact-link">
                                    <i class="icon icon-phone"></i> +966 12 345 6789
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Additional Styles -->
<style>
    .tf-thankyou-wrapper {
        padding: 40px 0;
    }

    .thankyou-title {
        color: #2c2c2c;
        font-size: 2.5rem;
        font-weight: 600;
    }

    .thankyou-subtitle {
        color: #666;
        font-size: 1.25rem;
        font-weight: 400;
    }

    .thankyou-message {
        color: #777;
        font-size: 1rem;
        line-height: 1.6;
    }

    .order-summary-card {
        background: #fff;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin-top: 30px;
    }

    .order-summary-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e5e5e5;
        padding: 20px;
        border-radius: 8px 8px 0 0;
    }

    .order-summary-card .card-body {
        padding: 20px;
    }

    .section-title {
        color: #2c2c2c;
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }

    .order-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .order-status.status-processing {
        background: #fff3cd;
        color: #856404;
    }

    .order-status.status-completed {
        background: #d4edda;
        color: #155724;
    }

    .order-status.status-pending {
        background: #f8d7da;
        color: #721c24;
    }

    .order-status.status-failed {
        background: #f8d7da;
        color: #721c24;
    }

    .order-item {
        border: 1px solid #f0f0f0;
        border-radius: 8px;
        padding: 15px;
        background: #fafafa;
    }

    .order-item .item-image img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 4px;
    }

    .order-item .item-name {
        color: #2c2c2c;
        font-size: 1rem;
        font-weight: 500;
    }

    .order-item .item-price {
        color: #2c2c2c;
        font-weight: 600;
        font-size: 1.125rem;
    }

    .totals-table .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .totals-table .total-row.final-total {
        border-bottom: none;
        border-top: 2px solid #e5e5e5;
        font-size: 1.125rem;
        padding: 15px 0 0 0;
        margin-top: 10px;
    }

    .totals-table .total-row.discount {
        color: #28a745;
    }

    .address-card {
        background: #f8f9fa;
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        padding: 20px;
    }

    .address-title {
        color: #2c2c2c;
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 10px;
    }

    .address-content {
        color: #666;
        line-height: 1.6;
    }

    .support-info {
        text-align: center;
    }

    .contact-methods {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
    }

    .contact-link {
        color: #007cba;
        text-decoration: none;
        font-weight: 500;
    }

    .contact-link:hover {
        text-decoration: underline;
    }

    .contact-link i {
        margin-right: 5px;
    }

    @media (max-width: 768px) {
        .thankyou-title {
            font-size: 2rem;
        }

        .order-item {
            flex-direction: column;
            text-align: center;
        }

        .order-item .item-image {
            margin-bottom: 10px;
        }

        .totals-table .total-row {
            font-size: 0.875rem;
        }

        .action-buttons .d-flex {
            flex-direction: column;
            align-items: center;
        }

        .contact-methods {
            flex-direction: column;
            gap: 10px;
        }
    }

    /* Print styles */
    @media print {

        .action-buttons,
        .support-info {
            display: none;
        }

        .order-summary-card {
            box-shadow: none;
            border: 1px solid #000;
        }
    }
</style>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        // Auto-refresh order status for pending payments
        <?php if ($order_status === 'pending' && $needs_payment): ?>
            var checkStatusInterval = setInterval(function () {
                $.ajax({
                    url: wc_checkout_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'check_order_status',
                        order_id: <?php echo $order_id; ?>,
                        nonce: '<?php echo wp_create_nonce("check_order_status_nonce"); ?>'
                    },
                    success: function (response) {
                        if (response.success && response.data.status !== 'pending') {
                            // Reload page if status changed
                            location.reload();
                        }
                    }
                });
            }, 30000); // Check every 30 seconds

            // Clear interval after 10 minutes
            setTimeout(function () {
                clearInterval(checkStatusInterval);
            }, 600000);
        <?php endif; ?>

        // Smooth scroll to order details
        if (location.hash) {
            $('html, body').animate({
                scrollTop: $(location.hash).offset().top - 100
            }, 500);
        }
    });
</script>

<?php
// Hook for additional content
do_action('woocommerce_thankyou', $order_id);

get_footer();
?>