<div class="card-product grid" data-availability="<?php echo $product_stock_status; ?>"
                data-brand="<?php echo !empty($product_brands) ? $product_brands[0]->slug : ''; ?>">
                <div class="card-product_wrapper">
                    <a href="<?php echo $product_permalink; ?>" class="product-img"
                        style="position: relative; display: block; overflow: hidden;">
                        <?php if ($product_image): ?>
                            <img class="lazyload img-product main-product-image-<?php echo $product_id; ?>"
                                src="<?php echo $product_image[0]; ?>" alt="<?php echo $product_title; ?>"
                                style="width: 100%; height: auto; display: block;">
                        <?php endif; ?>

                        <?php if ($hover_image): ?>
                            <img class="lazyload img-hover hover-product-image-<?php echo $product_id; ?>"
                                src="<?php echo $hover_image[0]; ?>" alt="<?php echo $product_title; ?>"
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.3s ease;">
                        <?php endif; ?>

                        <!-- صورة اللون المحدد -->
                        <img class="lazyload color-product-image color-image-<?php echo $product_id; ?>" src="" alt=""
                            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover; opacity: 0; transition: opacity 0.3s ease; z-index: 2;">
                    </a>

                    <?php
                    // التحقق من وجود ألوان المنتج
                    if (have_rows('product_size2', $product_id)): ?>
                        <div class="variant-box">
                            <ul class="product-size_list" data-product-id="<?php echo $product_id; ?>">


                                <?php
                                $size_index = 0;
                                while (have_rows('product_size2', $product_id)):
                                    the_row();
                                    $size_name = get_sub_field('size_name');
                                    $size_code = get_sub_field('size_code');

                                    ?>
                                    <li class="size-item h6" data-product-id="<?php echo $product_id; ?>"
                                        data-size-name="<?php echo esc_attr($size_name); ?>" data-size-index="<?php echo $size_code; ?>">
                                        <?php echo $size_name; ?>
                                    </li>
                                    <?php
                                    $size_index++;
                                endwhile; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <ul class="product-action_list">
                        <li>
                            <a href="?add-to-cart=<?php echo $product->get_id(); ?>"
                                class="add_to_cart_button ajax_add_to_cart product_type_simple box-icon hover-tooltip tooltip-left"
                                data-quantity="1" data-product_id="<?php echo esc_attr($product->get_id()); ?>"
                                data-product_sku="<?php echo esc_attr($product->get_sku()); ?>"
                                aria-label="<?php echo esc_attr(sprintf(__('Add "%s" to your cart', 'woocommerce'), $product->get_name())); ?>">
                                <span class="icon icon-shopping-cart-simple"></span>
                                <span class="tooltip"><?php _e('Add to cart', 'textdomain'); ?></span>
                            </a>
                        </li>
                        <li class="wishlist">
                            <?= do_shortcode('[ti_wishlists_addtowishlist loop=yes]'); ?>
                        </li>
                    </ul>

                    <?php if ($product_stock_status === 'instock'): ?>
                        <ul class="product-badge_list">
                            <li class="product-badge_item h6 hot">متاح</li>
                        </ul>
                    <?php endif; ?>

                    <?php if ($product_sale_price): ?>
                        <ul class="product-badge_list">
                            <li class="product-badge_item h6 sale">تخفيض</li>
                        </ul>
                    <?php endif; ?>
                </div>

                <div class="card-product_info">
                    <a href="<?php echo $product_permalink; ?>" class="name-product h4 link"><?php echo $product_title; ?></a>

                    <div class="price-wrap">
                        <?php if ($product_sale_price): ?>
                            <span class="price-old h6 fw-normal"><?php echo wc_price($product_regular_price); ?></span>
                            <span class="price-new h6"><?php echo wc_price($product_sale_price); ?></span>
                        <?php else: ?>
                            <span class="price-new h6"><?php echo wc_price($product_price); ?></span>
                        <?php endif; ?>
                    </div>

                    <?php
                    // التحقق من وجود ألوان المنتج
                    if (have_rows('product_attributes3', $product_id)): ?>
                        <ul class="product-color_list" data-product-id="<?php echo $product_id; ?>">
                            <?php
                            $color_index = 0;
                            while (have_rows('product_attributes3', $product_id)):
                                the_row();
                                $color_name = get_sub_field('color_name');
                                $color_code = get_sub_field('color_code'); // كود اللون (هيكس)
                                $color_image = get_sub_field('color_image'); // صورة اللون
            
                                // الحصول على رابط الصورة
                                $color_image_url = '';
                                if ($color_image) {
                                    if (is_array($color_image)) {
                                        $color_image_url = $color_image['url'];
                                    } else {
                                        $attachment_url = wp_get_attachment_image_src($color_image, 'medium');
                                        $color_image_url = $attachment_url ? $attachment_url[0] : '';
                                    }
                                }

                                $is_active = $color_index === 0 ? 'active' : '';
                                $display_color = $color_code ? $color_code : $color_name;
                                ?>
                                <li class="product-color-item color-swatch hover-tooltip tooltip-bot <?php //echo $is_active; ?>"
                                    style="background-color: <?php echo esc_attr($display_color); ?>;"
                                    data-product-id="<?php echo $product_id; ?>" data-color-image="<?php echo esc_url($color_image_url); ?>"
                                    data-color-name="<?php echo esc_attr($color_name); ?>" data-color-index="<?php echo $color_index; ?>">
                                    <span class="tooltip color-filter"><?php echo esc_html($color_name); ?></span>
                                    <span class="swatch-value"></span>
                                </li>
                                <?php
                                $color_index++;
                            endwhile; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>