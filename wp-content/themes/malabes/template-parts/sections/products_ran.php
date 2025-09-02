<?php
function get_product_data() {
    $product_id = $_POST['product_id'];
    $product = wc_get_product($product_id);
    $product_data = array(
        'name' => $product->get_name(),
        'price' => $product->get_price(),
        'image' => $product->get_image('full'),
    );
    echo json_encode($product_data);
    wp_die();
}
add_action('wp_ajax_get_product_data', 'get_product_data');


$pid = 483;

if (get_post_meta($pid, '_wcgp_enabled', true) !== '1')
    return;

$rows = get_post_meta($pid, '_wcgp_rows', true);
if (!is_array($rows) || empty($rows))
    return;

$mins = array_map(function ($r) {
    return (float) wc_clean($r['price']); }, $rows);
$min = $mins ? min($mins) : 0;

if ($min > 0) {
    echo '<div class="wcgp-start-from" style="margin-top:6px; font-size:12px; opacity:.85">'
        . esc_html__('ابتداءً من ', '') . wp_kses_post(wc_price($min))
        . '</div>';
}

// متفعّل؟
if (get_post_meta($pid, '_wcgp_enabled', true) !== '1')
    return;

$rows = get_post_meta($pid, '_wcgp_rows', true);
if (!is_array($rows) || empty($rows))
    return;

// ترتيب حسب الجرام تصاعدي
usort($rows, function ($a, $b) {
    return intval($a['g']) <=> intval($b['g']); });

// كم عنصر نعرضه؟
$max_show = 3;
$to_show = array_slice($rows, 0, $max_show);
$more = max(0, count($rows) - $max_show);

echo '<div class="wcgp-loop-pills" aria-label="' . esc_attr__('خيارات الجرامات', '') . '">';

foreach ($to_show as $r) {
    $g = isset($r['g']) ? (int) $r['g'] : 0;
    $p = isset($r['price']) ? (float) wc_clean($r['price']) : 0;
    if ($g <= 0)
        continue;

    echo '<span class="wcgp-pill" title="' . esc_attr($g . 'g') . '">'
        . esc_html($g . 'g') . ' – ' . wp_kses_post(wc_price($p))
        . '</span>';
}

if ($more > 0) {
    echo '<span class="wcgp-pill wcgp-more" title="' . esc_attr__('المزيد من الخيارات', '') . '">+'
        . esc_html($more . ' خيارات')
        . '</span>';
}

echo '</div>';
?>


<script>
   jQuery(document).ready(function() {
    var $pinBtns = jQuery('.tf-pin-btn');
    var $dropdownMenus = jQuery('.dropdown-menu');

    for (var i = 0; i < $pinBtns.length; i++) {
        $pinBtns.eq(i).data('product-id', i);
    }
    
    $pinBtns.mouseover(function() {
        $dropdownMenus.attr('class', 'dropdown-menu show');
    });

    $pinBtns.mouseout(function() {
        $dropdownMenus.attr('class', 'dropdown-menu');
    });

    $.ajax({
        url: "<?php echo admin_url('admin-ajax.php'); ?>",
        type: "POST",
        data: {
            action: "get_product_data",
            product_id:$(this).data('product-id')
        },
        success: function(response) {
            $dropdownMenus.html(response);
            console.log(response);
        }
    });
   });
</script>


<!-- Banner Product -->
<section class="flat-spacing tf-lookbook-hover">
    <div class="container">
        <div class="row align-items-center d-flex">

            <div class="col-lg-6">
                <div class="banner-lookbook wrap-lookbook_hover">

                    <img class="lazyload img-banner" src="<?php echo get_template_directory_uri(); ?>/assets/images/banner/banner-1.jpg"
                        data-src="<?php echo get_template_directory_uri(); ?>/assets/images/banner/banner-1.jpg" alt="Banners">

                        <?php 
                            $product_id = 438;
                            $product = wc_get_product($product_id);
                        ?>
                    <div class="lookbook-item position3" data-product-id="<?php echo $product_id; ?>">
                        <div class="dropdown dropup-center dropdown-custom dropstart">
                            <div role="dialog" class="tf-pin-btn bundle-pin-item swiper-button" data-slide="0" id="pin1"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span></span>
                            </div>
                            <div class="dropdown-menu p-0 d-lg-none">
                                <div class="lookbook-product style-row">
                                    <div class="content">
                                        <span class="tag">Skincare</span>
                                        <h6 class="name-prd">
                                            <a href="<?php echo get_permalink($product_id); ?>" class="link">
                                                <?php echo $product->get_name(); ?>
                                            </a>
                                        </h6>
                                        <div class="price-wrap">
                                            <span class="price-new h6"><?php echo $product->get_price_html(); ?></span>
                                            <span class="text-third h6">In Stock</span>
                                        </div>
                                    </div>
                                    <a href="<?php echo get_permalink($product_id); ?>" class="image">
                                        <?php echo $product->get_image('full'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="lookbook-item position4">
                        <div class="dropdown dropup-center dropdown-custom">
                            <div role="dialog" class="tf-pin-btn bundle-pin-item swiper-button" data-slide="1" id="pin2"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <span></span>
                            </div>
                            <div class="dropdown-menu p-0 d-lg-none">
                                <div class="lookbook-product style-row">
                                    <div class="content">
                                        <span class="tag">Skincare</span>
                                        <h6 class="name-prd">
                                            <a href="product-detail.html" class="link">
                                                Natural Multipurpose Oil
                                            </a>
                                        </h6>
                                        <div class="price-wrap">
                                            <span class="price-new h6">$99,99</span>
                                            <span class="text-third h6">In Stock</span>
                                        </div>
                                    </div>
                                    <a href="product-detail.html" class="image">
                                        <img class="lazyload" src="images/products/cosmetic/product-1.jpg"
                                            data-src="images/products/cosmetic/product-1.jpg" alt="Product">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="flat-spacing p-lg-0 pb-0">
                    <div class="sect-title wow fadeInUp">
                        <h1 class="s-title mb-8">Shop This Look</h1>
                        <p class="s-subtitle h6">Up to 50% off Lorem ipsum dolor sit amet, consectetur adipiscing elit
                        </p>
                    </div>
                    <div dir="ltr" class="swiper tf-sw-lookbook tf-sw-lookbook bundle-hover-wrap" data-preview="2"
                        data-tablet="2" data-mobile="2" data-space-lg="48" data-space-md="24" data-space="16"
                        data-pagination="1" data-pagination-md="1" data-pagination-lg="1">
                        <div class="swiper-wrapper">
                            <!-- item 1 -->
                            <div class="swiper-slide">
                                <div class="wow fadeInUp">
                                    <div class="card-product bundle-hover-item pin1">
                                        <div class="card-product_wrapper d-flex">
                                            <a href="product-detail.html" class="product-img">
                                                <img class="lazyload img-product"
                                                    src="images/products/cosmetic/product-9.jpg"
                                                    data-src="images/products/cosmetic/product-9.jpg" alt="Product">
                                                <img class="lazyload img-hover"
                                                    src="images/products/cosmetic/product-9.jpg"
                                                    data-src="images/products/cosmetic/product-9.jpg" alt="Product">
                                            </a>
                                            <ul class="product-action_list">
                                                <li>
                                                    <a href="#shoppingCart" data-bs-toggle="offcanvas"
                                                        class="hover-tooltip tooltip-left box-icon">
                                                        <span class="icon icon-shopping-cart-simple"></span>
                                                        <span class="tooltip">Add to cart</span>
                                                    </a>
                                                </li>
                                                <li class="wishlist">
                                                    <a href="javascript:void(0);"
                                                        class="hover-tooltip tooltip-left box-icon">
                                                        <span class="icon icon-heart"></span>
                                                        <span class="tooltip">Add to Wishlist</span>
                                                    </a>
                                                </li>
                                                <li class="compare">
                                                    <a href="#compare" data-bs-toggle="offcanvas"
                                                        class="hover-tooltip tooltip-left box-icon ">
                                                        <span class="icon icon-compare"></span>
                                                        <span class="tooltip">Compare</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#quickView" data-bs-toggle="modal"
                                                        class="hover-tooltip tooltip-left box-icon">
                                                        <span class="icon icon-view"></span>
                                                        <span class="tooltip">Quick view</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-product_info">
                                            <a href="product-detail.html" class="name-product h4 link">Hydrocolloid Acne
                                                Patches</a>
                                            <div class="price-wrap mb-0">
                                                <span class="price-old h6 fw-normal">$119,99</span>
                                                <span class="price-new h6">$84,99</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- item 2 -->
                            <div class="swiper-slide">
                                <div class="wow fadeInUp">
                                    <div class="card-product bundle-hover-item pin2">
                                        <div class="card-product_wrapper d-flex">
                                            <a href="product-detail.html" class="product-img">
                                                <img class="lazyload img-product"
                                                    src="images/products/cosmetic/product-1.jpg"
                                                    data-src="images/products/cosmetic/product-1.jpg" alt="Product">
                                                <img class="lazyload img-hover"
                                                    src="images/products/cosmetic/product-1.jpg"
                                                    data-src="images/products/cosmetic/product-1.jpg" alt="Product">
                                            </a>
                                            <ul class="product-action_list">
                                                <li>
                                                    <a href="#shoppingCart" data-bs-toggle="offcanvas"
                                                        class="hover-tooltip tooltip-left box-icon">
                                                        <span class="icon icon-shopping-cart-simple"></span>
                                                        <span class="tooltip">Add to cart</span>
                                                    </a>
                                                </li>
                                                <li class="wishlist">
                                                    <a href="javascript:void(0);"
                                                        class="hover-tooltip tooltip-left box-icon">
                                                        <span class="icon icon-heart"></span>
                                                        <span class="tooltip">Add to Wishlist</span>
                                                    </a>
                                                </li>
                                                <li class="compare">
                                                    <a href="#compare" data-bs-toggle="offcanvas"
                                                        class="hover-tooltip tooltip-left box-icon ">
                                                        <span class="icon icon-compare"></span>
                                                        <span class="tooltip">Compare</span>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#quickView" data-bs-toggle="modal"
                                                        class="hover-tooltip tooltip-left box-icon">
                                                        <span class="icon icon-view"></span>
                                                        <span class="tooltip">Quick view</span>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="card-product_info">
                                            <a href="product-detail.html" class="name-product h4 link">Natural
                                                Multipurpose Oil</a>
                                            <div class="price-wrap mb-0">
                                                <span class="price-old h6 fw-normal">$99,99</span>
                                                <span class="price-new h6">$59,99</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="sw-dot-default sw-pagination-lookbook"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- Banner Product -->