<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package ecom
 */

get_header();
?>

	<!-- Page Title -->
<section class="s-page-title">
    <div class="container">
        <div class="content">
            <h1 class="title-page">404</h1>
            <ul class="breadcrumbs-page">
                <li><a href="<?= home_url(); ?>" class="h6 link">Home</a></li>
					<li class="d-flex"><i class="icon icon-caret-right"></i></li>
					<li>
						<h6 class="current-page fw-normal">404</h6>
					</li>
				</ul>
			</div>
		</div>
	</section>
	<!-- /Page Title -->
	
	<div class="container py-5">
		<p>Page Not Found</p>

	</div>

<?php
get_footer();
