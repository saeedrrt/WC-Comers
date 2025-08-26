<?php
/**
 * ecom functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package ecom
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function ecom_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on ecom, use a find and replace
		* to change 'ecom' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'ecom', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'ecom' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'ecom_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	    // إضافة دعم للغة
    load_theme_textdomain('custom-woo-theme', get_template_directory() . '/languages');
    
    // إضافة دعم للعنوان التلقائي
    add_theme_support('title-tag');
    
    // إضافة دعم للصور المميزة
    add_theme_support('post-thumbnails');
    
    // إضافة دعم للشعار المخصص
    add_theme_support('custom-logo', array(
        'height' => 100,
        'width' => 400,
        'flex-height' => true,
        'flex-width' => true,
    ));
    
}
add_action( 'after_setup_theme', 'ecom_setup' );

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function ecom_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'ecom_content_width', 640 );
}
add_action( 'after_setup_theme', 'ecom_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function ecom_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'ecom' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'ecom' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'ecom_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function ecom_scripts() {
	wp_enqueue_style( 'ecom-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'ecom-style', 'rtl', 'replace' );

	wp_enqueue_script( 'ecom-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'ecom_scripts' );

function add_custom_body_class($classes) {
    // أضف الكلاس اللي انت عايزه
    $classes[] = 'homepage1-body';
    return $classes;
}
add_filter('body_class', 'add_custom_body_class');

// Saeed
// 2. تسجيل وتهيئة سكربت البحث بـ AJAX
// function enqueue_custom_ajax_search_script() {
//   wp_enqueue_script(
//     'wc-ajax-search-js',
//     get_stylesheet_directory_uri() . '/js/wc-ajax-search.js',
//     array( 'jquery' ),
//     '1.0',
//     true
//   );
//   wp_localize_script( 'wc-ajax-search-js', 'wcSearch', array(
//     'ajax_url' => admin_url( 'admin-ajax.php' ),
//   ) );
// }
// add_action( 'wp_enqueue_scripts', 'enqueue_custom_ajax_search_script' );


/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

// Woocommerce
require get_template_directory() . '/func/modific.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

