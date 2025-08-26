<?php
/* ==================== 1) Helpers ==================== */
function compare_get_ids() : array {
    return WC()->session->get( 'compare_products', [] );
}
function compare_set_ids( array $ids ) : void {
    WC()->session->set( 'compare_products', $ids );
}

/* ==================== 2) AJAX: add ==================== */
add_action( 'wp_ajax_add_to_compare',        'compare_add' );
add_action( 'wp_ajax_nopriv_add_to_compare', 'compare_add' );
function compare_add() {
    $id   = intval( $_POST['product_id'] ?? 0 );
    $ids  = compare_get_ids();

    if ( $id && ! in_array( $id, $ids, true ) ) {
        $ids[] = $id;
        compare_set_ids( $ids );
    }
    wp_send_json_success( [ 'count' => count( $ids ) ] );
}

/* ==================== 3) AJAX: remove single ==================== */
add_action( 'wp_ajax_remove_from_compare',        'compare_remove' );
add_action( 'wp_ajax_nopriv_remove_from_compare', 'compare_remove' );
function compare_remove() {
    $id  = intval( $_POST['product_id'] ?? 0 );
    $ids = array_diff( compare_get_ids(), [ $id ] );
    compare_set_ids( $ids );
    wp_send_json_success( [ 'count' => count( $ids ) ] );
}

/* ==================== 4) AJAX: clear all ==================== */
add_action( 'wp_ajax_clear_compare',        'compare_clear' );
add_action( 'wp_ajax_nopriv_clear_compare', 'compare_clear' );
function compare_clear() {
    compare_set_ids( [] );
    wp_send_json_success();
}

/* ==================== 5) AJAX: markup for ONE item ==================== */
add_action( 'wp_ajax_get_compare_item',        'compare_markup' );
add_action( 'wp_ajax_nopriv_get_compare_item', 'compare_markup' );
function compare_markup() {
    $id      = intval( $_POST['product_id'] ?? 0 );
    $product = wc_get_product( $id );
    if ( ! $product ) wp_send_json_error();

    $img = wp_get_attachment_image_url( $product->get_image_id(), 'thumbnail' );
    ob_start(); ?>
    <div class="tf-compare-item" data-product-id="<?= esc_attr( $id ); ?>">
        <a href="<?= esc_url( get_permalink( $id ) ); ?>">
            <div class="icon remove compare-remove-btn"><i class="icon-close"></i></div>
            <img class="radius-3" src="<?= esc_url( $img ); ?>" alt="<?= esc_attr( $product->get_name() ); ?>">
        </a>
    </div>
    <?php wp_send_json_success( ob_get_clean() );
}

/* ==================== 6) زرّ Compare فى قائمة المنتجات ==================== */
add_action( 'woocommerce_after_shop_loop_item', 'compare_button_loop', 25 );
function compare_button_loop() {
    global $product; ?>
    <button
        class="compare-btn box-icon"
        data-product-id="<?= esc_attr( $product->get_id() ); ?>">
        <i class="icon icon-shuffle"></i>
    </button>
<?php }

/* ==================== 7) سكربت و localize ==================== */
add_action( 'wp_enqueue_scripts', function () {
  wp_enqueue_script( 'my-compare', get_stylesheet_directory_uri() . '/js/compare.js', [ 'jquery' ], null, true );
  wp_localize_script( 'my-compare', 'compareVars', [
      'ajax' => admin_url( 'admin-ajax.php' ),
      'nonce'  => wp_create_nonce( 'comp' ),
  ] );
} );


// Blog
/**
 * Add these functions to your theme's functions.php file
 */

// Enable post thumbnails if not already enabled
if (!current_theme_supports('post-thumbnails')) {
    add_theme_support('post-thumbnails');
}

// Add custom image sizes for blog
add_image_size('blog-featured', 800, 400, true);
add_image_size('blog-thumbnail', 300, 200, true);

/**
 * Get post reading time
 */
function get_reading_time($post_id = null) {
    $post = get_post($post_id);
    if (!$post) return 0;
    
    $word_count = str_word_count(strip_tags($post->post_content));
    $reading_time = ceil($word_count / 200); // Average reading speed: 200 words per minute
    
    return $reading_time;
}

/**
 * Get better related posts with multiple fallback strategies
 */
function get_better_related_posts($post_id = null, $limit = 6) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $related_posts = array();
    
    // Strategy 1: Posts with same categories
    $categories = get_the_category($post_id);
    if (!empty($categories)) {
        $category_ids = array_map(function($cat) { return $cat->term_id; }, $categories);
        
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => $limit,
            'post__not_in' => array($post_id),
            'category__in' => $category_ids,
            'post_status' => 'publish',
            'orderby' => 'rand'
        );
        
        $related_posts = get_posts($args);
    }
    
    // Strategy 2: If not enough, try by tags
    if (count($related_posts) < $limit) {
        $tags = get_the_tags($post_id);
        if (!empty($tags)) {
            $tag_ids = array_map(function($tag) { return $tag->term_id; }, $tags);
            $needed = $limit - count($related_posts);
            $exclude_ids = array_merge(array($post_id), array_map(function($p) { return $p->ID; }, $related_posts));
            
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => $needed,
                'post__not_in' => $exclude_ids,
                'tag__in' => $tag_ids,
                'post_status' => 'publish',
                'orderby' => 'rand'
            );
            
            $tag_related = get_posts($args);
            $related_posts = array_merge($related_posts, $tag_related);
        }
    }
    
    // Strategy 3: If still not enough, get recent posts from same author
    if (count($related_posts) < $limit) {
        $author_id = get_post_field('post_author', $post_id);
        $needed = $limit - count($related_posts);
        $exclude_ids = array_merge(array($post_id), array_map(function($p) { return $p->ID; }, $related_posts));
        
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => $needed,
            'post__not_in' => $exclude_ids,
            'author' => $author_id,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $author_posts = get_posts($args);
        $related_posts = array_merge($related_posts, $author_posts);
    }
    
    // Strategy 4: Fill remaining with latest posts
    if (count($related_posts) < $limit) {
        $needed = $limit - count($related_posts);
        $exclude_ids = array_merge(array($post_id), array_map(function($p) { return $p->ID; }, $related_posts));
        
        $args = array(
            'post_type' => 'post',
            'posts_per_page' => $needed,
            'post__not_in' => $exclude_ids,
            'post_status' => 'publish',
            'orderby' => 'date',
            'order' => 'DESC'
        );
        
        $latest_posts = get_posts($args);
        $related_posts = array_merge($related_posts, $latest_posts);
    }
    
    return $related_posts;
}

/**
 * Enhanced social sharing URLs
 */
function get_social_share_urls($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $post_url = get_permalink($post_id);
    $post_title = get_the_title($post_id);
    $post_excerpt = get_the_excerpt($post_id);
    
    return array(
        'facebook' => 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($post_url),
        'twitter' => 'https://twitter.com/intent/tweet?url=' . urlencode($post_url) . '&text=' . urlencode($post_title),
        'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url=' . urlencode($post_url),
        'whatsapp' => 'https://wa.me/?text=' . urlencode($post_title . ' ' . $post_url),
        'telegram' => 'https://t.me/share/url?url=' . urlencode($post_url) . '&text=' . urlencode($post_title),
        'pinterest' => 'https://pinterest.com/pin/create/button/?url=' . urlencode($post_url) . '&description=' . urlencode($post_title),
    );
}

/**
 * Add schema markup for blog posts
 */
function add_blog_schema_markup() {
    if (is_single() && get_post_type() === 'post') {
        $post_id = get_the_ID();
        $author = get_the_author_meta('display_name');
        $published_date = get_the_date('c');
        $modified_date = get_the_modified_date('c');
        $featured_image = get_the_post_thumbnail_url($post_id, 'full');
        
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => get_the_title(),
            'description' => get_the_excerpt(),
            'image' => $featured_image,
            'author' => array(
                '@type' => 'Person',
                'name' => $author
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo('name'),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => get_site_icon_url()
                )
            ),
            'datePublished' => $published_date,
            'dateModified' => $modified_date,
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id' => get_permalink()
            )
        );
        
        echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>';
    }
}
add_action('wp_head', 'add_blog_schema_markup');

/**
 * Add Open Graph meta tags for better social sharing
 */
function add_og_meta_tags() {
    if (is_single()) {
        $post_id = get_the_ID();
        $title = get_the_title();
        $description = get_the_excerpt() ?: wp_trim_words(get_the_content(), 20);
        $url = get_permalink();
        $image = get_the_post_thumbnail_url($post_id, 'full');
        $site_name = get_bloginfo('name');
        
        echo '<meta property="og:type" content="article">';
        echo '<meta property="og:title" content="' . esc_attr($title) . '">';
        echo '<meta property="og:description" content="' . esc_attr($description) . '">';
        echo '<meta property="og:url" content="' . esc_url($url) . '">';
        echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">';
        
        if ($image) {
            echo '<meta property="og:image" content="' . esc_url($image) . '">';
        }
        
        // Twitter Card
        echo '<meta name="twitter:card" content="summary_large_image">';
        echo '<meta name="twitter:title" content="' . esc_attr($title) . '">';
        echo '<meta name="twitter:description" content="' . esc_attr($description) . '">';
        if ($image) {
            echo '<meta name="twitter:image" content="' . esc_url($image) . '">';
        }
    }
}
add_action('wp_head', 'add_og_meta_tags');

/**
 * Customize excerpt length for blog posts
 */
function custom_excerpt_length($length) {
    if (is_single()) {
        return 30;
    }
    return $length;
}
add_filter('excerpt_length', 'custom_excerpt_length');

/**
 * Add post views counter
 */
function set_post_views($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $count_key = 'post_views_count';
    $count = get_post_meta($post_id, $count_key, true);
    
    if ($count == '') {
        $count = 0;
        delete_post_meta($post_id, $count_key);
        add_post_meta($post_id, $count_key, '0');
    } else {
        $count++;
        update_post_meta($post_id, $count_key, $count);
    }
}

function get_post_views($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }
    
    $count = get_post_meta($post_id, 'post_views_count', true);
    return $count ? $count : 0;
}

// Track post views (only for single posts, not admin)
function track_post_views() {
    if (is_single() && !is_admin()) {
        set_post_views();
    }
}
add_action('wp_head', 'track_post_views');

/**
 * Add breadcrumbs support
 */
function get_post_breadcrumbs() {
    if (!is_single()) return;
    
    $breadcrumbs = array();
    $breadcrumbs[] = '<a href="' . home_url() . '">Home</a>';
    $breadcrumbs[] = '<a href="' . get_permalink(get_option('page_for_posts')) . '">Blog</a>';
    
    // Add categories
    $categories = get_the_category();
    if (!empty($categories)) {
        $category = $categories[0];
        $breadcrumbs[] = '<a href="' . get_category_link($category->term_id) . '">' . $category->name . '</a>';
    }
    
    $breadcrumbs[] = '<span>' . get_the_title() . '</span>';
    
    return implode(' / ', $breadcrumbs);
}




