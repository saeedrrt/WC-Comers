<?php
$lango = pll_current_language();

/* 1️⃣ هات كل الـ IDs بتاعة المنتجات اللى عليها خصم (جارية أو مُجدولة) */
$sale_ids = wc_get_product_ids_on_sale();
if (empty($sale_ids)) {
    return; // مافيش عروض
}

/* 2️⃣ استخرج الكاتيجورى اللى فيها الخصومات بس */
$cat_ids = [];
foreach ($sale_ids as $pid) {
    $cat_ids = array_merge(
        $cat_ids,
        wp_get_post_terms($pid, 'product_cat', ['fields' => 'ids'])
    );
}
$cat_ids = array_unique($cat_ids);
$sale_cats = get_terms([
    'taxonomy' => 'product_cat',
    'include' => $cat_ids,
    'hide_empty' => true,
    'parent' => 0,
    'orderby' => 'name',
]);

/* 3️⃣ لو فيه كاتيجورى نبدأ نطبع */
if (!empty($sale_cats)): ?>
    <section class="flat-spacing flat-animate-tab">
        <div class="container">
            <!-- العنوان و التـابز -->
            <div class="sect-title type-3 type-2 flex-wrap wow fadeInUp">
                <h2 class="s-title text-nowrap"><?= $lango == 'ar' ? 'عرض اليوم' : 'Deal of the day'; ?></h2>
                <ul class="tab-product_list-2" role="tablist">
                    <?php
                    $first = true;
                    foreach ($sale_cats as $cat):
                        $cat_slug = 'cat-' . $cat->slug;
                        $cat_link = get_term_link($cat);
                        ?>
                        <li class="nav-tab-item" role="presentation">
                            <a href="#<?php echo esc_attr($cat_slug); ?>" data-bs-toggle="tab"
                                data-cat-url="<?php echo esc_url($cat_link); ?>"
                                class="tf-btn-tab-2 h6 <?php echo $first ? 'active' : ''; ?>">
                                <?php echo esc_html($cat->name); ?>
                            </a>
                        </li>
                        <?php
                        $first = false;
                    endforeach;
                    ?>
                </ul>


                <a id="viewAllBtn" href="#" class="tf-btn-icon h6 fw-medium text-nowrap">
                    <?= $lango == 'ar' ? 'عرض كل المنتجات' : 'View All Products'; ?>
                    <i class="icon icon-caret-circle-right"></i>
                </a>


            </div><!-- /sect-title -->

            <!-- التـاب كونـتنت -->
            <div class="tab-content">
                <?php
                $first = true;
                foreach ($sale_cats as $cat):
                    $cat_slug = 'cat-' . $cat->slug;

                    /* 4️⃣ جيب المنتجات المخفَّضة داخل الكاتيجورى */
                    $query = new WP_Query([
                        'post_type' => 'product',
                        'post_status' => 'publish',
                        'posts_per_page' => 12,
                        'post__in' => $sale_ids,
                        'tax_query' => [
                            [
                                'taxonomy' => 'product_cat',
                                'field' => 'term_id',
                                'terms' => $cat->term_id,
                            ],
                        ],
                    ]);
                    ?>
                    <div class="tab-pane <?php echo $first ? 'active show' : ''; ?>" id="<?php echo esc_attr($cat_slug); ?>"
                        role="tabpanel">

                        <div dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" class="swiper tf-swiper wow fadeInUp" data-preview="4" data-tablet="3" data-mobile-sm="2"
                            data-mobile="2" data-space-lg="48" data-space-md="24" data-space="12" data-pagination="2"
                            data-pagination-sm="2" data-pagination-md="3" data-pagination-lg="4">

                            <div class="swiper-wrapper">
                                <?php
                                if ($query->have_posts()):
                                    while ($query->have_posts()):
                                        $query->the_post();
                                        $product = wc_get_product(get_the_ID());
                                        $regular_price = (float) $product->get_regular_price();
                                        $sale_price = (float) $product->get_sale_price();
                                        $percentage = $regular_price ? round((($regular_price - $sale_price) / $regular_price) * 100) : 0;
                                        $sale_end = $product->get_date_on_sale_to();
                                        $seconds_to_end = $sale_end ? max(0, $sale_end->getTimestamp() - current_time('timestamp')) : 0;
                                        ?>
                                        <div class="swiper-slide">
                                            <div class="card-product style-5">

                                                <div class="card-product_wrapper aspect-ratio-0 d-flex">
                                                    <a href="<?php the_permalink(); ?>" class="product-img">
                                                        <?php
                                                        echo $product->get_image('woocommerce_thumbnail', ['class' => 'img-product']);
                                                        /* صورة تانية للهُوفر لو موجودة */
                                                        $gallery = $product->get_gallery_image_ids();
                                                        if (!empty($gallery)) {
                                                            echo wp_get_attachment_image($gallery[0], 'woocommerce_thumbnail', false, ['class' => 'img-hover']);
                                                        }
                                                        ?>
                                                    </a>

                                                    <!-- أكشنز -->
                                                    <ul class="product-action_list">
                                                        <li>

                                                            <a href="?add-to-cart=<?php echo $product->get_id(); ?>" class="add_to_cart_button ajax_add_to_cart product_type_simple
          box-icon hover-tooltip tooltip-left" data-quantity="1"
                                                                data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                                                                data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                                                                aria-label="<?php echo esc_attr(sprintf(__('Add “%s” to your cart', 'woocommerce'), $product->get_name())); ?>">
                                                                <span class="icon icon-shopping-cart-simple"></span>
                                                                <span class="tooltip"><?php _e('Add to cart', 'textdomain'); ?></span>
                                                            </a>

                                                        </li>
                                                        <li class="wishlist">

                                                            <?= do_shortcode("[ti_wishlists_addtowishlist loop=yes]"); ?>

                                                        </li>

                                                        <li class="compare">
                                                            <a href="#compare" data-bs-toggle="offcanvas"
                                                                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                                                                class="compare-btn hover-tooltip tooltip-left box-icon">
                                                                <span class="icon icon-compare"></span>
                                                                <span class="tooltip">Compare</span>
                                                            </a>
                                                        </li>


                                                        <li class="d-none">
                                                            <a href="#quickView" data-bs-toggle="modal" data-bs-target="#quickView"
                                                                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                                                                class="hover-tooltip tooltip-left box-icon">
                                                                <span class="icon icon-view"></span>
                                                                <span class="tooltip">Quick view</span>
                                                            </a>
                                                        </li>


                                                    </ul>

                                                    <!-- بادچ الخصم + العدّاد -->
                                                    <ul class="product-badge_list">
                                                        <?php if ($percentage): ?>
                                                            <li class="product-badge_item h6 sale">-<?php echo esc_html($percentage); ?> %
                                                            </li>
                                                        <?php endif; ?>

                                                        <?php
                                                        // الحصول على التاجات الخاصة بالمنتج
                                                        $product_tags = wp_get_post_terms($product->get_id(), 'product_tag');

                                                        if (!empty($product_tags) && !is_wp_error($product_tags)): ?>

                                                            <?php foreach ($product_tags as $tag):
                                                                // تحديد الكلاس والخلفية حسب اسم التاج
                                                                $tag_class = '';
                                                                $tag_style = '';

                                                                switch (strtolower($tag->name)) {
                                                                    case 'flash sale':
                                                                        $tag_class = 'flash-sale';
                                                                        $tag_style = 'background-color: #000000; color: #ffffff;';
                                                                        break;
                                                                    case 'new':
                                                                    case 'new arrival':
                                                                        $tag_class = 'new-arrival';
                                                                        $tag_style = 'background-color: #28a745; color: #ffffff;';
                                                                        break;
                                                                    case 'limited':
                                                                    case 'محدود':
                                                                        $tag_class = 'limited-edition';
                                                                        $tag_style = 'background-color: #dc3545; color: #ffffff;';
                                                                        break;
                                                                    case 'exclusive':
                                                                    case 'حصري':
                                                                        $tag_class = 'exclusive';
                                                                        $tag_style = 'background-color: #6f42c1; color: #ffffff;';
                                                                        break;
                                                                    case 'bestseller':
                                                                    case 'Hot':
                                                                        $tag_class = 'hot';
                                                                        $tag_style = 'background-color: #fd7e14; color: #ffffff;';
                                                                        break;
                                                                    default:
                                                                        $tag_class = 'default-tag';
                                                                        $tag_style = 'background-color: #6c757d; color: #ffffff;';
                                                                        break;
                                                                }
                                                                ?>
                                                                <li class="product-badge_item h6 <?php echo esc_attr($tag_class); ?>"
                                                                    style="<?php echo esc_attr($tag_style); ?>">
                                                                    <?php echo esc_html($tag->name); ?>
                                                                </li>
                                                            <?php endforeach; ?>

                                                        <?php endif; ?>
                                                    </ul>


                                                    <?php if ($seconds_to_end): ?>
                                                        <div class="product-countdown">
                                                            <div class="js-countdown cd-has-zero"
                                                                data-timer="<?php echo esc_attr($seconds_to_end); ?>"
                                                                data-labels="d : ,h : ,m : ,s"></div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div><!-- /wrapper -->

                                                <!-- معلومات المنتج -->
                                                <div class="card-product_info d-grid">
                                                    <?php
                                                    $cats = wc_get_product_category_list($product->get_id(), ', ', '<p class="tag-product text-small">', '</p>');
                                                    echo wp_kses_post($cats);
                                                    ?>
                                                    <h6 class="name-product">
                                                        <a href="<?php the_permalink(); ?>" class="link"><?php the_title(); ?></a>
                                                    </h6>
                                                    <div class="rate_wrap w-100">
                                                        <?php echo wc_get_rating_html($product->get_average_rating()); ?>
                                                    </div>
                                                    <div class="price-wrap mb-0">
                                                        <h4 class="price-new">
                                                            <?= (float) $product->get_sale_price(); ?><small
                                                                style="font-size:17px;">SAR</small>
                                                        </h4>
                                                        <span class="price-old h6">
                                                            <?= (float) $product->get_regular_price(); ?><small
                                                                style="font-size:17px;">SAR</small>
                                                        </span>
                                                    </div>
                                                </div>

                                            </div><!-- /card -->
                                        </div><!-- /slide -->
                                        <?php
                                    endwhile;
                                    wp_reset_postdata();
                                endif;
                                ?>
                            </div><!-- /swiper-wrapper -->
                            <div class="sw-dot-default tf-sw-pagination d-xl-none"></div>
                        </div><!-- /swiper -->
                    </div><!-- /tab-pane -->
                    <?php
                    $first = false;
                endforeach;
                ?>
            </div><!-- /tab-content -->
        </div><!-- /container -->
    </section>
<?php endif; ?>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var viewAll = document.getElementById('viewAllBtn');
        var tabs = document.querySelectorAll('.tab-product_list-2 .tf-btn-tab-2');

        function setHrefFromTab(tabEl) {
            if (!tabEl || !viewAll) return;
            var url = tabEl.getAttribute('data-cat-url');
            if (url) viewAll.setAttribute('href', url);
        }

        // أول تحميل: خُد التاب الـ active أو أول تاب
        var activeTab = document.querySelector('.tab-product_list-2 .tf-btn-tab-2.active') || tabs[0];
        setHrefFromTab(activeTab);

        // عند تغيير التاب (Bootstrap event)
        tabs.forEach(function (tab) {
            tab.addEventListener('shown.bs.tab', function (e) {
                // e.target هو التاب اللي بقى Active
                setHrefFromTab(e.target);
            });
        });
    });
</script>