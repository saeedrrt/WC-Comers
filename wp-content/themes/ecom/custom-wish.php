<?php
/**
 * Template Name: Custom Wishlist
 */

get_header(); 
$lang = pll_current_language();
?>

<!-- Page Title -->
<section class="s-page-title">
    <div class="container">
        <div class="content">
            <h1 class="title-page"><?php the_title(); ?></h1>
            <ul class="breadcrumbs-page">
                <li><a href="<?= home_url(); ?>" class="h6 link"><?= $lang == 'ar' ? 'الرئيسية' : 'Home'; ?></a></li>
                <li class="d-flex"><i class="icon icon-caret-right"></i></li>
                <li>
                    <h6 class="current-page fw-normal"><?php the_title(); ?></h6>
                </li>
            </ul>
        </div>
    </div>
</section>
<!-- /Page Title -->

<div class="container">
    <?= do_shortcode('[ti_wishlistsview]'); ?>
</div>

<style>
    .flat-spacing {
        padding: 2rem;
    }
    .tinv-header {
        display: none;
    }
    .tinv-wishlist {
        padding: 2rem;
    }
    .tinv-wishlist .tinv-wishlist-content {
        padding: 2rem;
    }
</style>
 
<?php get_footer(); ?>