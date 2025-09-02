<?php
/**
 * Template Name: Home 
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package ecom
 */

get_header();
?>
<?php
/**
 * slug  => acf_option_key
 */
$sections = [
  'hero'           => 'hero_section',
  'shaping_pha'    => 'shaping_pha',
  'category'       => 'category_section',
  'products_ran'   => 'products_ran',
  // 'custom_ord'     => 'custom_section',
  'boxy'           => 'boxy_section',
  'flash'          => 'flash_section',
  'deal'           => 'deal_section',
  'banner_sec'     => 'banner_sec',
  'blog-sec'       => 'blog_section',
];

foreach ( $sections as $slug => $acf_key ) {

    // 1) أتأكد إن ملفّ التمبليت موجود
    if ( ! locate_template( "template-parts/sections/{$slug}.php", false, false ) ) {
        error_log( "❌ template-parts/sections/{$slug}.php مش موجود" );
        continue;
    }

    // 2) أجيب بيانات الـ ACF
    // $data = get_field( $acf_key, 30 );

    // 4) مرّر للتمبليت واعرضه
    // set_query_var( 'section', $data );
    get_template_part( "template-parts/sections/{$slug}" );
}
?>

<?php
get_footer();
