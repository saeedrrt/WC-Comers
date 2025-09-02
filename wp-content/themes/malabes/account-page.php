<?php
/**
 * Custom Cart Template for WooCommerce
 * Template Name: Custom Account
 */

// التأكد من تسجيل الدخول
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

get_header();

$lango = pll_current_language();
$current_user = wp_get_current_user();
$customer = new WC_Customer(get_current_user_id());

// تحديد التاب النشط
$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'dashboard';

// معالجة حفظ البيانات
if ($_POST) {
    handle_form_submissions();
}

// دالة معالجة جميع النماذج
function handle_form_submissions()
{
    if (!wp_verify_nonce($_POST['account_nonce'], 'account_action')) {
        return;
    }

    $user_id = get_current_user_id();

    // معالجة تحديث بيانات الحساب
    if (isset($_POST['save_account_details'])) {
        
        // تحديث البيانات الأساسية
        wp_update_user(array(
            'ID' => $user_id,
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'display_name' => sanitize_text_field($_POST['display_name']),
            'user_email' => sanitize_email($_POST['email'])
        ));

        // معالجة رفع الصورة - الكود المحدث
        if (isset($_FILES['avatar_image']) && $_FILES['avatar_image']['error'] == 0) {
            $uploaded_file = $_FILES['avatar_image'];
            
            // التحقق من نوع الملف
            $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
            if (in_array($uploaded_file['type'], $allowed_types)) {
                
                // استخدام WordPress upload functions
                if (!function_exists('wp_handle_upload')) {
                    require_once(ABSPATH . 'wp-admin/includes/file.php');
                }
                
                $upload_overrides = array('test_form' => false);
                $movefile = wp_handle_upload($uploaded_file, $upload_overrides);
                
                if ($movefile && !isset($movefile['error'])) {
                    // حفظ رابط الصورة في user meta
                    update_user_meta($user_id, 'custom_avatar', $movefile['url']);
                    
                    // حذف الصورة القديمة إذا كانت موجودة
                    $old_avatar = get_user_meta($user_id, 'custom_avatar', true);
                    if ($old_avatar && $old_avatar != $movefile['url']) {
                        $old_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $old_avatar);
                        if (file_exists($old_path)) {
                            wp_delete_file($old_path);
                        }
                    }
                    
                    echo '<script>
                        toastr.success("تم رفع الصورة بنجاح!");
                        // تحديث الصورة في الصفحة فوراً
                        document.querySelector(".imgDash").src = "' . $movefile['url'] . '";
                        if(document.querySelector(".author_avatar img")) {
                            document.querySelector(".author_avatar img").src = "' . $movefile['url'] . '";
                        }
                    </script>';
                } else {
                    echo '<script>toastr.error("' . $movefile['error'] . '");</script>';
                }
            } else {
                echo '<script>toastr.error("يرجى اختيار صورة صحيحة (JPG, PNG, GIF)");</script>';
            }
        }

        // معالجة الهاتف
        if (isset($_POST['full_phone']) && !empty($_POST['full_phone'])) {
            update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['full_phone']));
        }
        
        // معالجة باقي الحقول
        if (isset($_POST['company'])) {
            update_user_meta($user_id, 'billing_company', sanitize_text_field($_POST['company']));
        }
        
        if (isset($_POST['address'])) {
            update_user_meta($user_id, 'billing_address_1', sanitize_text_field($_POST['address']));
        }
        
        if (isset($_POST['city'])) {
            update_user_meta($user_id, 'billing_city', sanitize_text_field($_POST['city']));
        }
        
        if (isset($_POST['country'])) {
            update_user_meta($user_id, 'billing_country', sanitize_text_field($_POST['country']));
        }

        // معالجة تغيير كلمة المرور
        if (!empty($_POST['password_1']) && $_POST['password_1'] === $_POST['password_2']) {
            // التحقق من كلمة المرور القديمة
            if (!empty($_POST['password_current'])) {
                $user = wp_authenticate($current_user->user_login, $_POST['password_current']);
                if (!is_wp_error($user)) {
                    wp_set_password($_POST['password_1'], $user_id);
                    echo '<script>toastr.success("تم تغيير كلمة المرور بنجاح!");</script>';
                } else {
                    echo '<script>toastr.error("كلمة المرور الحالية غير صحيحة!");</script>';
                }
            } else {
                // إذا لم يدخل كلمة المرور القديمة
                wp_set_password($_POST['password_1'], $user_id);
                echo '<script>toastr.success("تم تغيير كلمة المرور بنجاح!");</script>';
            }
        } elseif (!empty($_POST['password_1']) && $_POST['password_1'] !== $_POST['password_2']) {
            echo '<script>toastr.error("كلمات المرور الجديدة غير متطابقة!");</script>';
        }

        if (empty($_FILES['avatar_image']['name']) && empty($_POST['password_1'])) {
            echo '<script>toastr.success("تم حفظ البيانات بنجاح!");</script>';
        }
    }
}

// إضافة AJAX handler لرفع الصورة منفصلاً
add_action('wp_ajax_upload_avatar', 'handle_avatar_upload_ajax');
function handle_avatar_upload_ajax() {
    check_ajax_referer('upload_avatar', 'nonce');
    
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] != 0) {
        wp_send_json_error('No file uploaded or upload error');
    }
    
    $user_id = get_current_user_id();
    $uploaded_file = $_FILES['avatar'];
    
    // التحقق من نوع الملف
    $allowed_types = array('image/jpeg', 'image/jpg', 'image/png', 'image/gif');
    if (!in_array($uploaded_file['type'], $allowed_types)) {
        wp_send_json_error('Invalid file type');
    }
    
    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }
    
    $upload_overrides = array('test_form' => false);
    $movefile = wp_handle_upload($uploaded_file, $upload_overrides);
    
    if ($movefile && !isset($movefile['error'])) {
        // حذف الصورة القديمة
        $old_avatar = get_user_meta($user_id, 'custom_avatar', true);
        if ($old_avatar) {
            $old_path = str_replace(wp_upload_dir()['baseurl'], wp_upload_dir()['basedir'], $old_avatar);
            if (file_exists($old_path)) {
                wp_delete_file($old_path);
            }
        }
        
        update_user_meta($user_id, 'custom_avatar', $movefile['url']);
        wp_send_json_success(['url' => $movefile['url']]);
    } else {
        wp_send_json_error($movefile['error']);
    }
}
?>

<!-- إضافة هذا JavaScript في نهاية الصفحة -->
<script>
// معالجة رفع الصورة في sidebar
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.querySelector('.fileInputDash');
    const changeBtn = document.querySelector('.changeImgDash');
    const avatarImg = document.querySelector('.imgDash');
    
    if (changeBtn && fileInput) {
        changeBtn.addEventListener('click', function() {
            fileInput.click();
        });
        
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // عرض preview فوري
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarImg.src = e.target.result;
                };
                reader.readAsDataURL(file);
                
                // رفع الملف عبر AJAX
                const formData = new FormData();
                formData.append('action', 'upload_avatar');
                formData.append('avatar', file);
                formData.append('nonce', '<?php echo wp_create_nonce('upload_avatar'); ?>');
                
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // تحديث جميع صور الأفتار في الصفحة
                        document.querySelectorAll('.imgDash, .author_avatar img').forEach(img => {
                            img.src = data.data.url;
                        });
                        toastr.success("تم تحديث الصورة بنجاح!");
                    } else {
                        toastr.error("خطأ في رفع الصورة: " + data.data);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error("حدث خطأ أثناء رفع الصورة");
                });
            }
        });
    }
    
    // معالجة رفع الصورة في form الإعدادات
    const settingsFileInput = document.querySelector('input[name="avatar_image"]');
    if (settingsFileInput) {
        settingsFileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // إنشاء preview للصورة
                    let preview = document.querySelector('.avatar-preview');
                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'avatar-preview';
                        preview.style.cssText = 'width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-top: 10px;';
                        settingsFileInput.parentNode.appendChild(preview);
                    }
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// دالة لتحديث الصورة في جميع أنحاء الصفحة
function updateAllAvatars(newUrl) {
    document.querySelectorAll('.imgDash, .author_avatar img, .avatar-preview').forEach(img => {
        img.src = newUrl;
    });
}
</script>
<?php

// دوال مساعدة
function get_user_order_stats($user_id)
{
    $orders = wc_get_orders(array('customer_id' => $user_id, 'limit' => -1));
    $stats = array('pending' => 0, 'completed' => 0, 'total' => count($orders));

    foreach ($orders as $order) {
        $status = $order->get_status();
        if (in_array($status, array('pending', 'processing', 'on-hold'))) {
            $stats['pending']++;
        } elseif ($status == 'completed') {
            $stats['completed']++;
        }
    }
    return $stats;
}

function get_recent_orders($user_id, $limit = 6)
{
    return wc_get_orders(array(
        'customer_id' => $user_id,
        'limit' => $limit,
        'orderby' => 'date',
        'order' => 'DESC'
    ));
}

function get_all_orders($user_id, $page = 1, $per_page = 10)
{
    $offset = ($page - 1) * $per_page;
    return wc_get_orders(array(
        'customer_id' => $user_id,
        'limit' => $per_page,
        'offset' => $offset,
        'orderby' => 'date',
        'order' => 'DESC'
    ));
}

// جلب البيانات حسب التاب النشط
$order_stats = get_user_order_stats(get_current_user_id());
$recent_orders = get_recent_orders(get_current_user_id());

// جلب الصورة المخصصة
$custom_avatar = get_user_meta(get_current_user_id(), 'custom_avatar', true);
$avatar_url = $custom_avatar ? $custom_avatar : get_avatar_url(get_current_user_id());


// AJAX handler لجلب قائمة الأوردرز
add_action('wp_ajax_get_orders_list', 'tf_get_orders_list_ajax');
function tf_get_orders_list_ajax()
{
    check_ajax_referer('get_orders_list', 'nonce');

    $current_page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
    $all_orders = get_all_orders(get_current_user_id(), $current_page, 10);

    ob_start();
    ?>
    <table class="table-my_order order_recent">
        <thead>
            <tr>
                <th>Orders</th>
                <th>Date</th>
                <th>Status</th>
                <th>Total</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_orders as $order): ?>
                <tr class="tb-order-item">
                    <td class="tb-order_code">#<?php echo $order->get_order_number(); ?></td>
                    <td><?php echo $order->get_date_created()->format('Y-m-d'); ?></td>
                    <td>
                        <div class="tb-order_status stt-<?php echo $order->get_status(); ?>">
                            <?php echo wc_get_order_status_name($order->get_status()); ?>
                        </div>
                    </td>
                    <td class="tb-order_price"><?php echo $order->get_formatted_order_total(); ?></td>
                    <td>
                        <a href="#" class="btn btn-sm js-view-order" data-order-id="<?php echo $order->get_id(); ?>">
                            <?php echo $lango == 'ar' ? 'عرض' : 'View'; ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    $html = ob_get_clean();

    wp_send_json_success(['html' => $html]);
}

/**
 * يرندر بلوك تفاصيل الأوردر
 */

?>

<!-- Page Title -->
<section class="s-page-title">
    <div class="container">
        <div class="content">
            <h1 class="title-page"><?php echo $lango == 'ar' ? 'حسابي' : 'My Account'; ?></h1>
            <ul class="breadcrumbs-page">
                <li><a href="index.html" class="h6 link"><?php echo $lango == 'ar' ? 'الرئيسية' : 'Home'; ?></a></li>
                <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                <li>
                    <h6 class="current-page fw-normal"><?php echo $lango == 'ar' ? 'حسابي' : 'My account'; ?></h6>
                </li>
            </ul>
        </div>
    </div>
</section>

<section class="flat-spacing">
    <!-- <input class="fileInputDash" type="file" accept="image/*" style="display: none;"> -->
    <div class="container">
        <div class="row">
            <div class="col-xl-3 d-none d-xl-block">
                <div class="sidebar-account sidebar-content-wrap sticky-top">
                    <div class="account-author">
                        <div class="author_avatar">
                            <div class="image">
                                <img class="lazyload imgDash" src="<?php echo esc_url($avatar_url); ?>"
                                    data-src="<?php echo esc_url($avatar_url); ?>" alt="Avatar">
                            </div>
                        </div>
                        <h4 class="author_name"><?php echo esc_html($current_user->display_name); ?></h4>
                        <p class="author_email h6"><?php echo esc_html($current_user->user_email); ?></p>
                    </div>
                    <ul class="my-account-nav">
                        <li>
                            <a href="?tab=dashboard"
                                class="my-account-nav_item h5 <?php echo $active_tab == 'dashboard' ? 'active' : ''; ?>">
                                <i class="icon icon-circle-four"></i>
                                <?php echo $lango == 'ar' ? 'الرئيسية' : 'Dashboard'; ?>
                            </a>
                        </li>
                        <li>
                            <a href="?tab=orders"
                                class="my-account-nav_item h5 <?php echo $active_tab == 'orders' ? 'active' : ''; ?>">
                                <i class="icon icon-box-arrow-down"></i>
                                <?php echo $lango == 'ar' ? 'الطلبات' : 'Orders'; ?>
                            </a>
                        </li>
                        <li>
                            <a href="?tab=addresses"
                                class="my-account-nav_item h5 <?php echo $active_tab == 'addresses' ? 'active' : ''; ?>">
                                <i class="icon icon-address-book"></i>
                                <?php echo $lango == 'ar' ? 'عناويني' : 'Addresses'; ?>
                            </a>
                        </li>
                        <li>
                            <a href="?tab=account"
                                class="my-account-nav_item h5 <?php echo $active_tab == 'account' ? 'active' : ''; ?>">
                                <i class="icon icon-setting"></i>
                                <?php echo $lango == 'ar' ? 'الإعدادات' : 'Setting'; ?>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo wp_logout_url(home_url()); ?>" class="my-account-nav_item h5">
                                <i class="icon icon-sign-out"></i>
                                <?php echo $lango == 'ar' ? 'تسجيل الخروج' : 'Log out'; ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-xl-9">
                <div class="my-account-content">

                    <?php if ($active_tab == 'dashboard'): ?>
                        <!-- Dashboard Content -->
                        <div class="account-my_order">
                            <h2 class="account-title type-semibold"><?php echo $lango == 'ar' ? 'الطلبات' : 'Recent Orders'; ?></h2>
                            <div class="overflow-auto">
                                <?php if (!empty($recent_orders)): ?>
                                    <table class="table-recent-orders">
                                        <thead>
                                            <tr>
                                                <th><?php echo $lango == 'ar' ? 'الطلبات' : 'Order'; ?></th>
                                                <th><?php echo $lango == 'ar' ? 'المنتجات' : 'Products'; ?></th>
                                                <th><?php echo $lango == 'ar' ? 'السعر' : 'Pricing'; ?></th>
                                                <th><?php echo $lango == 'ar' ? 'الحالة' : 'Status'; ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_orders as $order): ?>
                                                <tr class="order-row">
                                                    <td class="order-number">
                                                        #<?php echo $order->get_order_number(); ?>
                                                    </td>
                                                    <td class="order-products">
                                                        <?php
                                                        $items = $order->get_items();
                                                        $first_item = reset($items);
                                                        if ($first_item) {
                                                            $product = $first_item->get_product();
                                                            if ($product) {
                                                                $image = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'thumbnail');
                                                                $image_url = $image ? $image[0] : wc_placeholder_img_src();

                                                                // Get product attributes
                                                                $attributes = [];
                                                                if ($product->is_type('variable')) {
                                                                    $variation_attributes = $first_item->get_meta_data();
                                                                    foreach ($variation_attributes as $meta) {
                                                                        $key = $meta->get_data()['key'];
                                                                        $value = $meta->get_data()['value'];
                                                                        if (strpos($key, 'pa_') === 0) {
                                                                            $attribute_name = wc_attribute_label($key);
                                                                            $attributes[] = $attribute_name . ': ' . $value;
                                                                        }
                                                                    }
                                                                }

                                                                echo '<div class="product-info">';
                                                                echo '<div class="product-image">';
                                                                echo '<img src="' . esc_url($image_url) . '" alt="' . esc_attr($product->get_name()) . '">';
                                                                echo '</div>';
                                                                echo '<div class="product-details">';
                                                                echo '<h4 class="product-name">' . esc_html($product->get_name()) . '</h4>';
                                                                if (!empty($attributes)) {
                                                                    echo '<p class="product-attributes">' . implode(', ', $attributes) . '</p>';
                                                                } else {
                                                                    // Fallback to basic product info
                                                                    echo '<p class="product-category">';
                                                                    $categories = get_the_terms($product->get_id(), 'product_cat');
                                                                    if ($categories && !is_wp_error($categories)) {
                                                                        echo esc_html($categories[0]->name);
                                                                    }
                                                                    echo '</p>';
                                                                }
                                                                echo '</div>';
                                                                echo '</div>';
                                                            }
                                                        }
                                                        ?>
                                                    </td>
                                                    <td class="order-total">
                                                        <?php echo $order->get_formatted_order_total(); ?>
                                                    </td>
                                                    <td class="order-status">
                                                        <?php
                                                        $status = $order->get_status();
                                                        $status_name = wc_get_order_status_name($status);
                                                        $status_class = '';

                                                        switch ($status) {
                                                            case 'completed':
                                                                $status_class = 'status-completed';
                                                                break;
                                                            case 'processing':
                                                            case 'pending':
                                                                $status_class = 'status-pending';
                                                                break;
                                                            case 'on-hold':
                                                                $status_class = 'status-delivery';
                                                                break;
                                                            case 'cancelled':
                                                                $status_class = 'status-cancelled';
                                                                break;
                                                            default:
                                                                $status_class = 'status-default';
                                                        }
                                                        ?>
                                                        <span class="order-status-badge <?php echo $status_class; ?>">
                                                            <?php echo esc_html($status_name); ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <div class="no-orders">
                                        <p><?php echo $lango == 'ar' ? 'لا توجد طلبات' : 'No recent orders found.'; ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <style>
                            .table-recent-orders {
                                width: 100%;
                                border-collapse: collapse;
                                margin-top: 20px;
                            }

                            .table-recent-orders th,
                            .table-recent-orders td {
                                padding: 15px;
                                text-align: left;
                                border-bottom: 1px solid #eee;
                            }

                            .table-recent-orders th {
                                background-color: #f8f9fa;
                                font-weight: 600;
                                color: #333;
                            }

                            .order-row:hover {
                                background-color: #f8f9fa;
                            }

                            .product-info {
                                display: flex;
                                align-items: center;
                                gap: 12px;
                            }

                            .product-image img {
                                width: 50px;
                                height: 50px;
                                object-fit: cover;
                                border-radius: 8px;
                            }

                            .product-details {
                                flex: 1;
                            }

                            .product-name {
                                font-size: 14px;
                                font-weight: 600;
                                margin: 0 0 4px 0;
                                color: #333;
                            }

                            .product-attributes,
                            .product-category {
                                font-size: 12px;
                                color: #666;
                                margin: 0;
                            }

                            .order-status-badge {
                                padding: 6px 12px;
                                border-radius: 20px;
                                font-size: 12px;
                                font-weight: 500;
                                text-transform: capitalize;
                            }

                            .status-completed {
                                background-color: #d4edda;
                                color: #155724;
                            }

                            .status-pending {
                                background-color: #fff3cd;
                                color: #856404;
                            }

                            .status-delivery {
                                background-color: #cce5ff;
                                color: #004085;
                            }

                            .status-cancelled {
                                background-color: #f8d7da;
                                color: #721c24;
                            }

                            .status-default {
                                background-color: #e9ecef;
                                color: #495057;
                            }

                            .order-number {
                                font-weight: 600;
                                color: #333;
                            }

                            .order-total {
                                font-weight: 600;
                                color: #333;
                            }

                            .no-orders {
                                text-align: center;
                                padding: 40px 20px;
                                color: #666;
                            }

                            @media (max-width: 768px) {
                                .table-recent-orders {
                                    font-size: 14px;
                                }

                                .table-recent-orders th,
                                .table-recent-orders td {
                                    padding: 10px 8px;
                                }

                                .product-info {
                                    flex-direction: column;
                                    text-align: center;
                                    gap: 8px;
                                }

                                .product-image img {
                                    width: 40px;
                                    height: 40px;
                                }
                            }
                        </style>

                    <?php elseif ($active_tab == 'orders'): ?>
                        <!-- All Orders Content -->
                        <div id="orders-content">
                            <div id="orders-list-wrap">
                                <div class="account-my_order">
                                    <h2 class="account-title type-semibold"><?php echo $lango == 'ar' ? 'الطلبات' : 'Orders'; ?></h2>
                                    <div class="overflow-auto">
                                        <?php
                                        $current_page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
                                        $all_orders = get_all_orders(get_current_user_id(), $current_page, 10);
                                        ?>
                                        <table class="table-my_order order_recent">
                                            <thead>
                                                <tr>
                                                    <th><?php echo $lango == 'ar' ? 'الطلبات' : 'Orders'; ?></th>
                                                    <th><?php echo $lango == 'ar' ? 'التاريخ' : 'Date'; ?></th>
                                                    <th><?php echo $lango == 'ar' ? 'الحالة' : 'Status'; ?></th>
                                                    <th><?php echo $lango == 'ar' ? 'الإجمالي' : 'Total'; ?></th>
                                                    <th><?php echo $lango == 'ar' ? 'الإجراءات' : 'Actions'; ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($all_orders as $order): ?>
                                                    <tr class="tb-order-item">
                                                        <td class="tb-order_code">#<?php echo $order->get_order_number(); ?>
                                                        </td>
                                                        <td><?php echo $order->get_date_created()->format('Y-m-d'); ?></td>
                                                        <td>
                                                            <div
                                                                class="tb-order_status stt-<?php echo $order->get_status(); ?>">
                                                                <?php echo wc_get_order_status_name($order->get_status()); ?>
                                                            </div>
                                                        </td>
                                                        <td class="tb-order_price">
                                                            <?php echo $order->get_formatted_order_total(); ?>
                                                        </td>
                                                        <td>
                                                            <a href="#" class="btn btn-dark js-view-order"
                                                                data-order-id="<?php echo $order->get_id(); ?>">
                                                                <?php echo $lango == 'ar' ? 'عرض' : 'View'; ?>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div id="order-detail-wrap" class="mt-4" style="display:none;"></div>
                        </div>

                    <?php elseif ($active_tab == 'addresses'): ?>
                        <?php

                        $billing_address = array(
                            'first_name' => get_user_meta(get_current_user_id(), 'billing_first_name', true),
                            'last_name' => get_user_meta(get_current_user_id(), 'billing_last_name', true),
                            'company' => get_user_meta(get_current_user_id(), 'billing_company', true),
                            'address_1' => get_user_meta(get_current_user_id(), 'billing_address_1', true),
                            'address_2' => get_user_meta(get_current_user_id(), 'billing_address_2', true),
                            'city' => get_user_meta(get_current_user_id(), 'billing_city', true),
                            'state' => get_user_meta(get_current_user_id(), 'billing_state', true),
                            'postcode' => get_user_meta(get_current_user_id(), 'billing_postcode', true),
                            'country' => get_user_meta(get_current_user_id(), 'billing_country', true),
                            'phone' => get_user_meta(get_current_user_id(), 'billing_phone', true)
                        );

                        ?>

                        <div class="col-xl-9">
                            <div class="my-account-content">
                                <h2 class="account-title type-semibold"><?php echo $lango == 'ar' ? 'عناويني' : 'Addresses'; ?></h2>
                                <div class="account-my_address">


                                    <div class="account-address-item file-delete">
                                        <div class="address-item_content">
                                            <h4 class="address-title"><?php echo $lango == 'ar' ? 'العنوان الافتراضي' : 'Default Address'; ?></h4>
                                            <div class="address-info">
                                                <h5 class="fw-semibold">
                                                    <?php echo $billing_address['first_name'] . ' ' . $billing_address['last_name']; ?>
                                                </h5>
                                                <p class="h6"><?php echo $billing_address['address_1']; ?> </p>
                                            </div>
                                            <div class="address-info">
                                                <h5 class="fw-semibold"><?php echo $lango == 'ar' ? 'الهاتف' : 'Phone'; ?></h5>
                                                <p class="h6"> <?php echo $billing_address['phone']; ?> </p>
                                            </div>
                                        </div>
                                        <div class="address-item_action">

                                            <a href="?tab=edit-address&type=billing" class="tf-btn animate-btn"><?php echo $lango == 'ar' ? 'تعديل' : 'Edit'; ?></a>

                                        </div>
                                    </div>


                                </div>
                            </div>
                        </div>

                    <?php elseif ($active_tab == 'edit-address'): ?>
                        <!-- Edit Address Content -->
                        <?php
                        $address_type = isset($_GET['type']) ? $_GET['type'] : 'billing';
                        $address_data = array();
                        $fields = array('first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone');
                        foreach ($fields as $field) {
                            $address_data[$field] = get_user_meta(get_current_user_id(), $address_type . '_' . $field, true);
                        }
                        ?>
                        <div class="edit-address-form">
                            <h2 class="account-title type-semibold"><?php echo $lango == 'ar' ? 'تعديل عنوان' : 'Edit Address'; ?></h2>
                            <form method="post">
                                <?php wp_nonce_field('account_action', 'account_nonce'); ?>
                                <input type="hidden" name="address_type" value="<?php echo $address_type; ?>">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo $lango == 'ar' ? 'الاسم الأول' : 'First Name'; ?> *</label>
                                            <input type="text" name="first_name"
                                                value="<?php echo esc_attr($address_data['first_name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label><?php echo $lango == 'ar' ? 'الاسم الأخير' : 'Last Name'; ?> *</label>
                                            <input type="text" name="last_name"
                                                value="<?php echo esc_attr($address_data['last_name']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label><?php echo $lango == 'ar' ? 'اسم الشركة' : 'Company Name'; ?></label>
                                            <input type="text" name="company"
                                                value="<?php echo esc_attr($address_data['company']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label><?php echo $lango == 'ar' ? 'العنوان' : 'Street Address'; ?> *</label>
                                            <input type="text" name="address_1"
                                                value="<?php echo esc_attr($address_data['address_1']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label><?php echo $lango == 'ar' ? 'الشقة، المبنى، الخدمة' : 'Apartment, suite, etc.'; ?></label>
                                            <input type="text" name="address_2"
                                                value="<?php echo esc_attr($address_data['address_2']); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><?php echo $lango == 'ar' ? 'المدينة' : 'Town / City'; ?> *</label>
                                            <input type="text" name="city"
                                                value="<?php echo esc_attr($address_data['city']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><?php echo $lango == 'ar' ? 'الولاية' : 'State'; ?> *</label>
                                            <input type="text" name="state"
                                                value="<?php echo esc_attr($address_data['state']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><?php echo $lango == 'ar' ? 'الرمز البريدي' : 'Postcode / ZIP'; ?> *</label>
                                            <input type="text" name="postcode"
                                                value="<?php echo esc_attr($address_data['postcode']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="country" class="form-label"><?php echo $lango == 'ar' ? 'البلد' : 'Country'; ?> <span
                                                    class="text-danger">*</span></label>
                                            <select id="country" name="country" class="form-select" required>
                                                <option value=""><?php echo $lango == 'ar' ? 'اختر البلد' : 'Select Country'; ?></option>

                                                <?php
                                                $countries = WC()->countries->get_shipping_countries();
                                                foreach ($countries as $key => $value) {
                                                    $selected = ($key === $address_data['country']) ? 'selected' : '';
                                                    echo "<option value='{$key}' {$selected}>{$value}</option>";
                                                }
                                                ?>

                                            </select>
                                        </div>
                                    </div>

                                    <?php if ($address_type == 'billing'): ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label><?php echo $lango == 'ar' ? 'رقم الهاتف' : 'Phone'; ?></label>
                                                <input type="tel" name="phone"
                                                    value="<?php echo esc_attr($address_data['phone']); ?>">
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="form-actions">
                                    <button type="submit" name="save_address" class="btn btn-primary"><?php echo $lango == 'ar' ? 'حفظ العنوان' : 'Save Address'; ?></button>
                                    <a href="?tab=addresses" class="btn btn-secondary"><?php echo $lango == 'ar' ? 'إلغاء' : 'Cancel'; ?></a>
                                </div>
                            </form>
                        </div>

                    <?php elseif ($active_tab == 'account'): ?>

                        <?php
                        // Get user's phone number and country code
                        $user_phone = get_user_meta(get_current_user_id(), 'billing_phone', true);
                        $user_country = get_user_meta(get_current_user_id(), 'billing_country', true);

                        // Separate country code from phone number
                        $phone_number = '';
                        $country_code = '';
                        if (!empty($user_phone)) {
                            // Try to extract country code (basic logic)
                            if (preg_match('/^\+(\d{1,4})\s?(.*)/', $user_phone, $matches)) {
                                $country_code = '+' . $matches[1];
                                $phone_number = $matches[2];
                            } else {
                                $phone_number = $user_phone;
                                // Set default country code based on user's country
                                $country_codes = array(
                                    'EG' => '+20',
                                    'SA' => '+966',
                                    'AE' => '+971',
                                    'US' => '+1',
                                    'GB' => '+44',
                                    'FR' => '+33',
                                    'DE' => '+49',
                                    'IT' => '+39'
                                );
                                $country_code = isset($country_codes[$user_country]) ? $country_codes[$user_country] : '+20';
                            }
                        }

                        // Get user's additional data
                        $user_company = get_user_meta(get_current_user_id(), 'billing_company', true);
                        $user_address = get_user_meta(get_current_user_id(), 'billing_address_1', true);
                        $user_city = get_user_meta(get_current_user_id(), 'billing_city', true);
                        ?>
                        <!-- Account Settings Content -->
                        <div class="edit-account-form">
                            <h2 class="account-title type-semibold">Account Setting</h2>
                            <form method="post" enctype="multipart/form-data" class="modern-account-form">
                                <?php wp_nonce_field('account_action', 'account_nonce'); ?>

                                <!-- Personal Information Section -->
                                <div class="form-section personal-info">
                                    <div class="form-row">
                                        <div class="form-group half-width">
                                            <input type="text" name="first_name" placeholder="<?php echo $lango == 'ar' ? 'الاسم الأول' : 'First name'; ?> *"
                                                value="<?php echo esc_attr($current_user->first_name); ?>" required>
                                        </div>
                                        <div class="form-group half-width">
                                            <input type="text" name="last_name" placeholder="<?php echo $lango == 'ar' ? 'الاسم الأخير' : 'Last name'; ?> *"
                                                value="<?php echo esc_attr($current_user->last_name); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group full-width">
                                        <input type="text" name="display_name" placeholder="<?php echo $lango == 'ar' ? 'اسم العرض' : 'Display Name'; ?> *"
                                            value="<?php echo esc_attr($current_user->display_name); ?>" required>
                                    </div>

                                    <div class="form-group full-width">
                                        <input type="email" name="email" placeholder="<?php echo $lango == 'ar' ? 'البريد الالكتروني' : 'Email'; ?> *"
                                            value="<?php echo esc_attr($current_user->user_email); ?>" required>
                                    </div>

                                    <!-- Phone Number with Country Code -->
                                    <div class="form-group full-width phone-group">
                                        <div class="phone-input-container">
                                            <select name="country_code" class="country-code-select">
                                                <?php
                                                $country_codes = array(
                                                    '+20' => '🇪🇬 +20 (' . $lango == 'ar' ? 'مصر' : 'Egypt' . ')',
                                                    '+966' => '🇸🇦 +966 (' . $lango == 'ar' ? 'السعودية' : 'Saudi Arabia' . ')',
                                                    '+971' => '🇦🇪 +971 (' . $lango == 'ar' ? 'الإمارات' : 'UAE' . ')',
                                                    '+965' => '🇰🇼 +965 (' . $lango == 'ar' ? 'الكويت' : 'Kuwait' . ')',
                                                    '+974' => '🇶🇦 +974 (' . $lango == 'ar' ? 'القطر' : 'Qatar' . ')',
                                                    '+973' => '🇧🇭 +973 (' . $lango == 'ar' ? 'البحرين' : 'Bahrain' . ')'
                                                );

                                                foreach ($country_codes as $code => $label) {
                                                    $selected = ($code == $country_code) ? 'selected' : '';
                                                    echo "<option value='{$code}' {$selected}>{$code}</option>";
                                                }
                                                ?>
                                            </select>
                                            <input type="tel" name="phone_number" placeholder="<?php echo $lango == 'ar' ? 'رقم الهاتف' : 'Phone number'; ?>"
                                                value="<?php echo esc_attr($phone_number); ?>" class="phone-input">
                                        </div>
                                    </div>

                                    <!-- Additional Info -->
                                    <div class="form-group full-width">
                                        <input type="text" name="company" placeholder="<?php echo $lango == 'ar' ? 'الشركة' : 'Company'; ?> (optional)"
                                            value="<?php echo esc_attr($user_company); ?>">
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group half-width">
                                            <input type="text" name="address" placeholder="<?php echo $lango == 'ar' ? 'العنوان' : 'Address'; ?>"
                                                value="<?php echo esc_attr($user_address); ?>">
                                        </div>
                                        <div class="form-group half-width">
                                            <input type="text" name="city" placeholder="<?php echo $lango == 'ar' ? 'المدينة' : 'City'; ?>"
                                                value="<?php echo esc_attr($user_city); ?>">
                                        </div>
                                    </div>

                                    <div class="form-group full-width">
                                        <select name="country" class="country-select">
                                            <option value=""><?php echo $lango == 'ar' ? 'البلد' : 'Country'; ?></option>
                                            <?php
                                            $countries = WC()->countries->get_countries();
                                            foreach ($countries as $key => $value) {
                                                $selected = ($key === $user_country) ? 'selected' : '';
                                                echo "<option value='{$key}' {$selected}>{$value}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- Profile Picture -->
                                    <div class="form-group full-width file-upload-group">
                                        <label class="file-upload-label">
                                            <input type="file" name="avatar_image" accept="image/*" class="file-input">
                                            <span class="file-upload-text">
                                                <i class="icon-camera"></i>
                                                <?php echo $lango == 'ar' ? 'تحميل صورة الملف الشخصي' : 'Upload Profile Picture'; ?>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Change Password Section -->
                                <div class="form-section password-section">
                                    <h3 class="section-title"><?php echo $lango == 'ar' ? 'تغيير كلمة المرور' : 'Change Password'; ?></h3>

                                    <div class="form-group full-width password-input-group">
                                        <input type="password" name="password_current" placeholder="<?php echo $lango == 'ar' ? 'كلمة المرور الحالية' : 'Current password'; ?> *"
                                            class="password-input">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="icon-eye"></i>
                                        </button>
                                    </div>

                                    <div class="form-group full-width password-input-group">
                                        <input type="password" name="password_1" placeholder="<?php echo $lango == 'ar' ? 'كلمة المرور الجديدة' : 'New password'; ?> *"
                                            class="password-input">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="icon-eye"></i>
                                        </button>
                                    </div>

                                    <div class="form-group full-width password-input-group">
                                        <input type="password" name="password_2" placeholder="<?php echo $lango == 'ar' ? 'تأكيد كلمة المرور الجديدة' : 'Confirm new password'; ?> *"
                                            class="password-input">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="icon-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="form-actions">
                                    <button type="submit" name="save_account_details" class="save-changes-btn">
                                        <?php echo $lango == 'ar' ? 'حفظ التغييرات' : 'Save changes'; ?>
                                        <i class="arrow-up-icon">↗</i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <style>
                            .form-section {
                                margin-bottom: 40px;
                            }

                            .section-title {
                                font-size: 24px;
                                font-weight: 600;
                                margin-bottom: 30px;
                                color: #000;
                            }

                            .form-row {
                                display: flex;
                                gap: 20px;
                                margin-bottom: 20px;
                            }

                            .form-group {
                                position: relative;
                            }

                            .form-group.half-width {
                                flex: 1;
                            }

                            .form-group.full-width {
                                width: 100%;
                                margin-bottom: 20px;
                            }

                            .form-group input,
                            .form-group select {
                                width: 100%;
                                padding: 16px 20px;
                                border: none;
                                background-color: #f5f5f5;
                                border-radius: 12px;
                                font-size: 16px;
                                color: #333;
                                outline: none;
                                transition: all 0.3s ease;
                            }

                            .form-group .country-code-select {
                                width: 10%;
                                padding: 16px 20px;
                                border: none;
                                background-color: #f5f5f5;
                                border-radius: 12px;
                                font-size: 16px;
                                color: #333;
                                outline: none;
                                transition: all 0.3s ease;
                            }

                            .form-group input::placeholder {
                                color: #999;
                            }

                            /* Phone Input Styling */
                            .phone-input-container {
                                display: flex;
                                gap: 0;
                                background-color: #f5f5f5;
                                border-radius: 12px;
                                overflow: hidden;
                            }

                            .country-code-select {
                                min-width: 160px;
                                border-right: 1px solid #ddd;
                                border-radius: 0;
                                background-color: transparent;
                            }

                            .phone-input {
                                flex: 1;
                                border-radius: 0;
                                background-color: transparent;
                                border: none;
                            }

                            /* Password Input with Toggle */
                            .password-input-group {
                                position: relative;
                            }

                            .password-toggle {
                                position: absolute;
                                right: 16px;
                                top: 50%;
                                transform: translateY(-50%);
                                background: none;
                                border: none;
                                cursor: pointer;
                                color: #666;
                                font-size: 18px;
                            }

                            .password-toggle:hover {
                                color: #333;
                            }

                            /* File Upload Styling */
                            .file-upload-label {
                                display: block;
                                padding: 16px 20px;
                                background-color: #f5f5f5;
                                border-radius: 12px;
                                cursor: pointer;
                                text-align: center;
                                transition: all 0.3s ease;
                            }

                            .file-upload-label:hover {
                                background-color: #eee;
                            }

                            .file-input {
                                display: none;
                            }

                            .file-upload-text {
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 10px;
                                color: #666;
                                font-size: 16px;
                            }

                            /* Save Button */
                            .save-changes-btn {
                                width: 100%;
                                padding: 18px 24px;
                                background-color: #000;
                                color: #fff;
                                border: none;
                                border-radius: 50px;
                                font-size: 16px;
                                font-weight: 600;
                                cursor: pointer;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                gap: 10px;
                                transition: all 0.3s ease;
                                margin-top: 30px;
                            }

                            .save-changes-btn:hover {
                                background-color: #333;
                                transform: translateY(-2px);
                            }

                            .arrow-up-icon {
                                font-size: 18px;
                                font-weight: bold;
                            }

                            /* Responsive Design */
                            @media (max-width: 768px) {
                                .form-row {
                                    flex-direction: column;
                                    gap: 0;
                                }

                                .country-code-select {
                                    min-width: 140px;
                                }

                                .phone-input-container {
                                    flex-direction: column;
                                }

                                .country-code-select {
                                    border-right: none;
                                    border-bottom: 1px solid #ddd;
                                }
                            }
                        </style>

                        <script>
                            function togglePassword(button) {
                                const input = button.previousElementSibling;
                                const icon = button.querySelector('i');

                                if (input.type === 'password') {
                                    input.type = 'text';
                                    icon.className = 'icon-eye-slash';
                                } else {
                                    input.type = 'password';
                                    icon.className = 'icon-eye';
                                }
                            }

                            // Handle form submission with phone number combination
                            document.addEventListener('DOMContentLoaded', function () {
                                const form = document.querySelector('.modern-account-form');

                                form.addEventListener('submit', function (e) {
                                    const countryCode = document.querySelector('select[name="country_code"]').value;
                                    const phoneNumber = document.querySelector('input[name="phone_number"]').value;

                                    if (phoneNumber) {
                                        // Create a hidden input with combined phone number
                                        const hiddenInput = document.createElement('input');
                                        hiddenInput.type = 'hidden';
                                        hiddenInput.name = 'full_phone';
                                        hiddenInput.value = countryCode + ' ' + phoneNumber;
                                        form.appendChild(hiddenInput);
                                    }
                                });
                            });
                        </script>

                        <?php
                        // Add this to the form handling section
                        if (isset($_POST['save_account_details'])) {
                            // Handle phone number
                            if (isset($_POST['full_phone']) && !empty($_POST['full_phone'])) {
                                update_user_meta($user_id, 'billing_phone', sanitize_text_field($_POST['full_phone']));
                            }

                            // Handle other fields
                            if (isset($_POST['company'])) {
                                update_user_meta($user_id, 'billing_company', sanitize_text_field($_POST['company']));
                            }

                            if (isset($_POST['address'])) {
                                update_user_meta($user_id, 'billing_address_1', sanitize_text_field($_POST['address']));
                            }

                            if (isset($_POST['city'])) {
                                update_user_meta($user_id, 'billing_city', sanitize_text_field($_POST['city']));
                            }

                            if (isset($_POST['country'])) {
                                update_user_meta($user_id, 'billing_country', sanitize_text_field($_POST['country']));
                            }
                        }
                        ?>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- Hidden form for avatar upload -->
<form id="avatar-form" method="post" enctype="multipart/form-data" style="display: none;">
    <?php wp_nonce_field('account_action', 'account_nonce'); ?>
    <input type="file" name="avatar_image" class="fileInputDash" accept="image/*">
    <input type="submit" name="upload_avatar">
</form>

<script>

    // Cancel Order function
    function cancelOrder(orderId) {
        if (confirm('Are you sure you want to cancel this order?')) {
            const formData = new FormData();
            formData.append('action', 'cancel_order');
            formData.append('order_id', orderId);

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success("Order cancelled successfully");
                        location.reload();  
                    } else {
                        toastr.error("Error cancelling order");
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error("Network error occurred");
                });
        }
    }
</script>

<?php
// AJAX handler لإلغاء الطلب
add_action('wp_ajax_cancel_order', 'handle_cancel_order');
function handle_cancel_order()
{
    // التحقق من nonce (مبسط)
    // if (!wp_verify_nonce($_POST['nonce'], 'cancel_order')) {
    //     wp_die('Security check failed');
    // }

    $order_id = (int) $_POST['order_id'];
    $order = wc_get_order($order_id);

    if ($order && $order->get_customer_id() == get_current_user_id()) {
        if (in_array($order->get_status(), array('pending', 'on-hold'))) {
            $order->update_status('cancelled', 'Order cancelled by customer');
            wp_send_json_success();
        }
    }

    wp_send_json_error();
}

get_footer(); ?>