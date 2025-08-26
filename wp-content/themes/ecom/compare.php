<?php
/**
 * Template Name: Compare Page
 */

// ===== إعدادات ACF Repeater =====
$acf_repeater   = 'product_attributes3'; // اسم الـ Repeater (عدله حسب مشروعك)
$acf_label_key  = 'color_name';            // اسم الحقل الفرعي لعنوان الفيشر
$acf_value_key  = 'color_code';            // اسم الحقل الفرعي لقيمة الفيشر

// ===== هيلبرز بسيطة للـ Session (نفس اللي استخدمناه قبل) =====
if ( ! function_exists( 'compare_get_ids' ) ) {
    function compare_get_ids() {
        return WC()->session->get( 'compare_products', [] );
    }
}
if ( ! function_exists( 'compare_set_ids' ) ) {
    function compare_set_ids( $ids ) {
        WC()->session->set( 'compare_products', $ids );
    }
}

get_header();

// هات IDs من السيشن
$compare_ids = array_values( array_filter( array_unique( compare_get_ids() ) ) );

// لو فاضي
if ( empty( $compare_ids ) ) : ?>
    <section class="s-page-title">
        <div class="container">
            <div class="content">
                <h1 class="title-page">Compare Product</h1>
                <ul class="breadcrumbs-page">
                    <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="h6 link">Home</a></li>
                    <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                    <li><h6 class="current-page fw-normal">Compare Product</h6></li>
                </ul>
            </div>
        </div>
    </section>

    <div class="flat-spacing">
        <div class="container">
            <p class="box-text_empty h6 text-main">Your Compare is currently empty</p>
        </div>
    </div>
<?php
get_footer();
return;
endif;

// حضّر المنتجات
$products = [];
foreach ( $compare_ids as $pid ) {
    $p = wc_get_product( $pid );
    if ( $p ) {
        $products[] = $p;
    }
}

// لو كله مش موجود لأي سبب
if ( empty( $products ) ) {
    compare_set_ids( [] );
    wp_safe_redirect( get_permalink() );
    exit;
}

// ===== تجميع بيانات ديناميكية =====

// 1) تجميع كل الـ Attributes المرئية عبر كل المنتجات (اسم → مصفوفة قيم لكل منتج)
$all_attr_names  = [];       // مصفوفة بأسماء الصفات (attributes) الفريدة
$product_attrs   = [];       // [product_id][attr_name] => value string

foreach ( $products as $p ) {
    $pid           = $p->get_id();
    $product_attrs[$pid] = [];
    foreach ( $p->get_attributes() as $taxonomy => $attr ) {
        // المرئية فقط
        if ( is_a( $attr, 'WC_Product_Attribute' ) && ! $attr->get_visible() ) {
            continue;
        }
        $name = wc_attribute_label( $taxonomy );
        $value = wc_get_product_terms( $pid, $taxonomy, ['fields' => 'names'] );
        $value_str = ! empty( $value ) ? implode( ', ', $value ) : '-';

        $product_attrs[$pid][$name] = $value_str;
        $all_attr_names[$name] = true;
    }
}
$all_attr_names = array_keys( $all_attr_names );

// 2) تجميع الـ Features من ACF Repeater (توحيد العناوين)
$all_feature_labels = [];          // عناوين الفيشر الموحّدة
$product_features   = [];          // [product_id][label] => value

if ( function_exists( 'have_rows' ) ) {
    foreach ( $products as $p ) {
        $pid = $p->get_id();
        $product_features[$pid] = [];
        if ( have_rows( $acf_repeater, $pid ) ) {
            while ( have_rows( $acf_repeater, $pid ) ) {
                the_row();
                $label = trim( get_sub_field( $acf_label_key ) );
                $value = get_sub_field( $acf_value_key );
                if ( $label !== '' ) {
                    $product_features[$pid][$label] = is_array( $value ) ? implode( ', ', $value ) : ( $value !== '' ? $value : '-' );
                    $all_feature_labels[$label] = true;
                }
            }
        }
    }
    $all_feature_labels = array_keys( $all_feature_labels );
}
?>

<!-- Page Title -->
<section class="s-page-title">
    <div class="container">
        <div class="content">
            <h1 class="title-page">Compare Product</h1>
            <ul class="breadcrumbs-page">
                <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="h6 link">Home</a></li>
                <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                <li><h6 class="current-page fw-normal">Compare Product</h6></li>
            </ul>
        </div>
    </div>
</section>
<!-- /Page Title -->

<!-- Compare -->
<div class="flat-spacing">
    <div class="container">
        <div class="tf-table-compare">
            <table>
                <thead>
                    <!-- الصف العلوي: صورة + إزالة + اسم + سعر -->
                    <tr class="compare-row">
                        <th class="compare-col"></th>

                        <?php foreach ( $products as $p ) :
                            $pid  = $p->get_id();
                            $img  = wp_get_attachment_image_url( $p->get_image_id(), 'medium' );
                            $img  = $img ?: wc_placeholder_img_src( 'medium' );
                            $url  = get_permalink( $pid );
                            $name = $p->get_name();
                            $price_html = $p->get_price_html();
                        ?>
                        <th class="compare-col compare-head" data-product-id="<?php echo esc_attr( $pid ); ?>">
                            <div class="compare-item">
                                <div class="item_image">
                                    <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>">
                                    <button class="remove compare-remove-btn" aria-label="Remove">
                                        <i class="icon icon-trash"></i>
                                    </button>
                                </div>

                                <a href="<?php echo esc_url( $url ); ?>" class="item_name h4 link">
                                    <?php echo esc_html( $name ); ?>
                                </a>

                                <div class="item_price price-wrap">
                                    <?php echo wp_kses_post( $price_html ); ?>
                                </div>
                            </div>
                        </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>

                <tbody>
                    <!-- التقييم -->
                    <tr class="compare-row">
                        <td class="compare-col compare-title">Rating</td>
                        <?php foreach ( $products as $p ) :
                            $avg   = $p->get_average_rating();
                            $count = $p->get_rating_count();
                        ?>
                        <td class="compare-col">
                            <div class="compare_rate">
                                <div class="rate_wrap">
                                    <?php echo wc_get_rating_html( $avg ); ?>
                                </div>
                                <span class="rate_count">(<?php echo esc_html( $count ); ?>)</span>
                            </div>
                        </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- السعر (كقيمة نصية إضافية إن حبيت غير الـ HTML العادي) -->
                    <tr class="compare-row">
                        <td class="compare-col compare-title">Price</td>
                        <?php foreach ( $products as $p ) : ?>
                        <td class="compare-col compare-value">
                            <span><?php echo wp_kses_post( $p->get_price_html() ); ?></span>
                        </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- الحالة -->
                    <tr class="compare-row">
                        <td class="compare-col compare-title">Stock</td>
                        <?php foreach ( $products as $p ) : ?>
                        <td class="compare-col compare-value">
                            <span>
                                <?php echo esc_html( wc_get_stock_html( $p ) ? wp_strip_all_tags( wc_get_stock_html( $p ) ) : ( $p->is_in_stock() ? 'In stock' : 'Out of stock' ) ); ?>
                            </span>
                        </td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- SKU -->
                    <tr class="compare-row">
                        <td class="compare-col compare-title">SKU</td>
                        <?php foreach ( $products as $p ) : ?>
                        <td class="compare-col compare-value"><span><?php echo esc_html( $p->get_sku() ?: '-' ); ?></span></td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- التصنيفات -->
                    <tr class="compare-row">
                        <td class="compare-col compare-title">Categories</td>
                        <?php foreach ( $products as $p ) :
                            $cats = wc_get_product_category_list( $p->get_id(), ', ' );
                        ?>
                        <td class="compare-col compare-value"><span><?php echo $cats ? wp_kses_post( $cats ) : '-'; ?></span></td>
                        <?php endforeach; ?>
                    </tr>

                    <!-- كل Attributes المرئية (ديناميكي) -->
                    <?php foreach ( $all_attr_names as $attr_label ) : ?>
                    <tr class="compare-row">
                        <td class="compare-col compare-title"><?php echo esc_html( $attr_label ); ?></td>
                        <?php foreach ( $products as $p ) :
                            $pid = $p->get_id();
                            $val = isset($product_attrs[$pid][$attr_label]) ? $product_attrs[$pid][$attr_label] : '-';
                        ?>
                        <td class="compare-col compare-value"><span><?php echo esc_html( $val ); ?></span></td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>

                    <!-- كل ACF Features (ديناميكي) -->
                    <?php if ( ! empty( $all_feature_labels ) ) : ?>
                        <?php foreach ( $all_feature_labels as $feat_label ) : ?>
                        <tr class="compare-row">
                            <td class="compare-col compare-title"><?php echo esc_html( $feat_label ); ?></td>
                            <?php foreach ( $products as $p ) :
                                $pid = $p->get_id();
                                $val = isset($product_features[$pid][$feat_label]) ? $product_features[$pid][$feat_label] : '-';
                                
                            ?>
                            <td class="compare-col compare-value">
                                <span><?php echo esc_html( $val ); ?></span>
                            </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- زر الشراء -->
                    <tr class="compare-row">
                        <td class="compare-col compare-title">Buy</td>
                        <?php foreach ( $products as $p ) :
                            // زر add to cart
                            ob_start();
                            woocommerce_template_single_add_to_cart();
                            $btn_html = ob_get_clean();
                        ?>
                        <td class="compare-col compare-value p-0">
                            <div class="w-100 rounded-0">
                                <?php
                                // بديل سريع: رابط إضافة للسلة (للسيمبل)
                                if ( $p->is_type( 'simple' ) ) {
                                    $add_url = esc_url( $p->add_to_cart_url() );
                                    $text    = esc_html__( 'Add to cart', 'woocommerce' );
                                    echo '<a href="' . $add_url . '" class="tf-btn style-transparent w-100 rounded-0 add_to_cart_button ajax_add_to_cart" data-product_id="' . esc_attr( $p->get_id() ) . '">' . $text . ' <i class="icon icon-shopping-cart-simple"></i></a>';
                                } else {
                                    // للمنتجات المعقدة، اعرض زر افتراضي يفتح صفحة المنتج
                                    echo '<a href="' . esc_url( get_permalink( $p->get_id() ) ) . '" class="tf-btn style-transparent w-100 rounded-0">' . esc_html__( 'View options', 'woocommerce' ) . '</a>';
                                }
                                ?>
                            </div>
                        </td>
                        <?php endforeach; ?>
                    </tr>
                </tbody>
            </table>

            <!-- أزرار عامة -->
            <div class="d-flex gap-2 mt-3">
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="tf-btn btn-white line">Continue Shopping</a>
                <button class="tf-btn btn-white line tf-compare-clear-all">Clear All</button>
            </div>
        </div>
    </div>
</div>
<!-- /Compare -->

<script>
jQuery(function($){
    // إزالة منتج واحد
    $(document).on('click', '.compare-remove-btn', function(e){
        e.preventDefault();
        const $th = $(this).closest('th.compare-col.compare-head');
        const pid = $th.data('product-id');

        $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
            action: 'remove_from_compare',
            product_id: pid
        }).done(function(resp){
            if (resp && resp.success) {
                // احذف العمود بالكامل (header + كل الخانات الخاصة بيه)
                const colIndex = $th.index(); // ترتيب العمود
                $('table tr').each(function(){
                    $(this).children().eq(colIndex).remove();
                });

                // لو مفيش أعمدة منتجات تاني، اظهر فاضي
                if ($('table thead tr th.compare-head').length === 0) {
                    $('.tf-table-compare').replaceWith('<p class="box-text_empty h6 text-main">Your Compare is currently empty</p>');
                }
            }
        });
    });

    // حذف الكل
    $(document).on('click', '.tf-compare-clear-all', function(){
        $.post('<?php echo admin_url( 'admin-ajax.php' ); ?>', {
            action: 'clear_compare'
        }).done(function(){
            $('.tf-table-compare').replaceWith('<p class="box-text_empty h6 text-main">Your Compare is currently empty</p>');
        });
    });
});
</script>

<?php get_footer();
