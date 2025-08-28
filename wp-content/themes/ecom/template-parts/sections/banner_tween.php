<?php
$lango = pll_current_language();
?>

<section class="flat-spacing">


            <div class="container">
                <div class="sect-title text-center wow fadeInUp animated" style="visibility: visible; animation-name: fadeInUp;">
                    <h1 class="title mb-8"><?= $lango == 'ar' ? 'المنتجات الأكثر تداولا' : 'Product Trending'; ?></h1>
                    <p class="s-subtitle h6"><?= $lango == 'ar' ? 'upto 50% off' : 'Up to 50% off Lorem ipsum dolor sit amet, consectetur adipiscing elit'; ?></p>
                </div>
                <div class="tf-grid-layout md-col-2">
                    <?php
                    $args = array(
                        'post_type' => 'home_banner',
                        'posts_per_page' => -1,
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'banners_cat',
                                'field' => 'slug',
                                'terms' => 'home-one'
                            )
                        )
                    );
                    $the_query = new WP_Query($args);
                    if ($the_query->have_posts()) :
                        while ($the_query->have_posts()) : $the_query->the_post();
                            $image_id = get_post_thumbnail_id();
                            $image_url = wp_get_attachment_image_url($image_id, 'full');
                    ?>
                    <div class="box-image_V05 hover-img wow fadeInUp animated" style="visibility: visible; animation-name: fadeInUp;">
                        <p class="box-image_image img-style">
                            <img src="<?php echo $image_url; ?>" data-src="<?php echo $image_url; ?>" alt="" class=" ls-is-cached lazyloaded">
                        </p>
                        <div class="box-image_content">
                            <h2 class="title">
                            
                                <?= pll_current_language() == 'ar' ? the_field('arabic_title') : the_title(); ?>
                              
                            </h2>
                          
                        </div>
                    </div>
                    <?php endwhile; wp_reset_postdata(); endif; ?>
                </div>
            </div>
        </section>