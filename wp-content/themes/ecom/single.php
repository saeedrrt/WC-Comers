<?php
/**
 * Single Post Template
 */

get_header(); ?>

<?php while (have_posts()):
	the_post(); ?>

	<!-- Page Title -->
	<section class="page-title-blog parallaxie" style='background-image: url("<?= the_post_thumbnail_url(get_the_ID(), 'full'); ?>")'>
		<div class="container position-relative z-5">
			<div class="content">
				<?php
				// Get primary category
				$categories = get_the_category();
				if (!empty($categories)):
					$primary_category = $categories[0];
					?>
					<div class="entry_tag name-tag h6"><?= esc_html($primary_category->name); ?></div>
				<?php endif; ?>
			
				<h1 class="heading" style="background: #fffefe54;
    padding: 12px 0;">
					<?= get_the_title(); ?>
				</h1>
			
				<div class="entry_author">
					<span class="h6">Written by:</span>
					<h6 class="name-author"><?= get_the_author(); ?></h6>
				</div>
			</div>
		</div>
	</section>
	<!-- /Page Title -->

	<!-- Blog Detail -->
	<section class="s-blog-detail flat-spacing">
		<div class="container">
			<div class="row flex-wrap-reverse">
				<div class="col-xl-3">
					<div class="blog-detail_info mt-xl-0 sticky-top">
						<!-- Date -->
						<div class="date-post">
							<p class="title-label h6">Date</p>
							<h6 class="entry_date"><?= get_the_date('F j, Y'); ?></h6>
						</div>
					
						<!-- Share -->
						<div class="share-post">
							<p class="title-label h6">Share</p>
							<ul class="tf-social-icon">
								<li>
									<a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(get_permalink()); ?>" 
									   target="_blank" class="social-facebook">
										<span class="icon"><i class="icon-fb"></i></span>
									</a>
								</li>
								<li>
									<a href="https://www.instagram.com/" target="_blank" class="social-instagram">
										<span class="icon"><i class="icon-instagram-logo"></i></span>
									</a>
								</li>
								<li>
									<a href="https://twitter.com/intent/tweet?url=<?= urlencode(get_permalink()); ?>&text=<?= urlencode(get_the_title()); ?>" 
									   target="_blank" class="social-x">
										<span class="icon"><i class="icon-x"></i></span>
									</a>
								</li>
								<li>
									<a href="https://www.tiktok.com/" target="_blank" class="social-tiktok">
										<span class="icon"><i class="icon-tiktok"></i></span>
									</a>
								</li>
							</ul>
						</div>
					
						<!-- Tags -->
						<?php
						$post_tags = get_the_tags();
						if ($post_tags): ?>
							<div class="tag-post">
								<p class="title-label">Tags</p>
								<ul class="tag-list">
									<?php foreach ($post_tags as $tag): ?>
										<li>
											<a href="<?= get_tag_link($tag->term_id); ?>" class="link">
												#<?= esc_html($tag->name); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					
						<!-- Categories -->
						<?php if (!empty($categories)): ?>
							<div class="category-post">
								<p class="title-label">Categories</p>
								<ul class="tag-list">
									<?php foreach ($categories as $category): ?>
										<li>
											<a href="<?= get_category_link($category->term_id); ?>" class="link">
												<?= esc_html($category->name); ?>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
			
				<div class="col-xl-9">
					<div class="blog-detail_content tf-grid-layout">
						<?php the_content(); ?>
					
						<?php
						// Get previous and next posts
						$prev_post = get_previous_post();
						$next_post = get_next_post();

						if ($prev_post || $next_post): ?>
							<span class="br-line"></span>
							<div class="group-direc">
								<?php if ($prev_post): ?>
									<a href="<?= get_permalink($prev_post->ID); ?>" class="btn-direc prev link">
										<?php if (has_post_thumbnail($prev_post->ID)): ?>
											<img src="<?= get_the_post_thumbnail_url($prev_post->ID, 'thumbnail'); ?>" 
												 data-src="<?= get_the_post_thumbnail_url($prev_post->ID, 'thumbnail'); ?>" 
												 alt="<?= esc_attr($prev_post->post_title); ?>" class="lazyload">
										<?php else: ?>
											<img src="<?= get_template_directory_uri(); ?>/images/placeholder.jpg" 
												 alt="<?= esc_attr($prev_post->post_title); ?>">
										<?php endif; ?>
										<div class="content">
											<p class="fw-medium text-uppercase">Previous post</p>
											<p class="name-post h6"><?= esc_html($prev_post->post_title); ?></p>
										</div>
									</a>
								<?php endif; ?>
						
								<?php if ($prev_post && $next_post): ?>
									<span class="br-line"></span>
								<?php endif; ?>
						
								<?php if ($next_post): ?>
									<a href="<?= get_permalink($next_post->ID); ?>" class="btn-direc next link">
										<div class="content">
											<p class="fw-medium text-uppercase">Next post</p>
											<p class="name-post h6"><?= esc_html($next_post->post_title); ?></p>
										</div>
										<?php if (has_post_thumbnail($next_post->ID)): ?>
											<img src="<?= get_the_post_thumbnail_url($next_post->ID, 'thumbnail'); ?>" 
												 data-src="<?= get_the_post_thumbnail_url($next_post->ID, 'thumbnail'); ?>" 
												 alt="<?= esc_attr($next_post->post_title); ?>" class="lazyload">
										<?php else: ?>
											<img src="<?= get_template_directory_uri(); ?>/images/placeholder.jpg" 
												 alt="<?= esc_attr($next_post->post_title); ?>">
										<?php endif; ?>
									</a>
								<?php endif; ?>
							</div>
							<span class="br-line"></span>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!-- /Blog Detail -->

<?php endwhile; ?>

<!-- Related Articles -->
<?php
// Get related posts based on categories or tags
$current_post_id = get_the_ID();
$categories = get_the_category();
$tags = get_the_tags();

$related_args = array(
	'post_type' => 'post',
	'posts_per_page' => 6,
	'post__not_in' => array($current_post_id),
	'post_status' => 'publish',
	'orderby' => 'date',
	'order' => 'DESC'
);

// Try to get related posts by categories first
if (!empty($categories)) {
	$category_ids = array_map(function ($cat) {
		return $cat->term_id; }, $categories);
	$related_args['category__in'] = $category_ids;
} elseif (!empty($tags)) {
	// If no categories, try by tags
	$tag_ids = array_map(function ($tag) {
		return $tag->term_id; }, $tags);
	$related_args['tag__in'] = $tag_ids;
}

$related_posts = new WP_Query($related_args);

// If no related posts found, get latest posts
if (!$related_posts->have_posts()) {
	unset($related_args['category__in'], $related_args['tag__in']);
	$related_posts = new WP_Query($related_args);
}

if ($related_posts->have_posts()):
	$posts_count = $related_posts->found_posts;

	// Dynamic preview settings
	$preview_desktop = min($posts_count, 3);
	$preview_tablet = min($posts_count, 2);
	$preview_mobile = 1;

	// Show pagination if more than preview count
	$show_pagination = $posts_count > $preview_desktop;
	?>

	<section class="flat-spacing pt-0">
		<div class="container">
			<div class="sect-title">
				<h1>Related Articles</h1>
			</div>
		
			<div dir="ltr" class="swiper tf-swiper" 
				 data-preview="<?= $preview_desktop; ?>" 
				 data-tablet="<?= $preview_tablet; ?>" 
				 data-mobile="<?= $preview_mobile; ?>" 
				 data-space-lg="48"
				 data-space-md="30" 
				 data-space="15" 
			 	<?= $show_pagination ? 'data-pagination="1"' : ''; ?>>
			
				<div class="swiper-wrapper">
					<?php while ($related_posts->have_posts()):
						$related_posts->the_post(); ?>
						<div class="swiper-slide">
							<div class="article-blog hover-img4">
								<div class="blog-image">
									<a href="<?= get_permalink(); ?>" class="entry_image img-style4">
										<?php if (has_post_thumbnail()): ?>
											<img src="<?= get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" 
												 data-src="<?= get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" 
												 alt="<?= esc_attr(get_the_title()); ?>" 
												 class="lazyload">
										<?php else: ?>
											<img src="<?= get_template_directory_uri(); ?>/images/placeholder.jpg" 
												 alt="<?= esc_attr(get_the_title()); ?>">
										<?php endif; ?>
									</a>
								</div>
								<div class="blog-content p-0">
									<a href="<?= get_permalink(); ?>" class="entry_name link h4">
										<?= get_the_title(); ?>
									</a>
									<p class="entry_date"><?= get_the_date('F j, Y'); ?></p>
								</div>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
			
				<?php if ($show_pagination): ?>
					<div class="sw-dot-default tf-sw-pagination"></div>
				<?php endif; ?>
			</div>
		</div>
	</section>

<?php
endif;
wp_reset_postdata();
?>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		// Initialize enhanced related posts swiper
		const relatedSwiper = document.querySelector('.tf-swiper');
		if (relatedSwiper) {
			const preview = parseInt(relatedSwiper.dataset.preview) || 1;
			const tablet = parseInt(relatedSwiper.dataset.tablet) || 1;
			const mobile = parseInt(relatedSwiper.dataset.mobile) || 1;
			const spaceLg = parseInt(relatedSwiper.dataset.spaceLg) || 0;
			const spaceMd = parseInt(relatedSwiper.dataset.spaceMd) || 0;
			const space = parseInt(relatedSwiper.dataset.space) || 0;
			const pagination = relatedSwiper.dataset.pagination === '1';
			const navigation = relatedSwiper.dataset.navigation === '1';
			const loop = relatedSwiper.dataset.loop === 'true';

			new Swiper(relatedSwiper, {
				slidesPerView: mobile,
				spaceBetween: space,
				loop: loop && preview < count,
				autoplay: false,
				grabCursor: true,
				watchOverflow: true,
				breakpoints: {
					576: {
						slidesPerView: mobile,
						spaceBetween: space,
					},
					768: {
						slidesPerView: tablet,
						spaceBetween: spaceMd,
					},
					992: {
						slidesPerView: Math.min(preview, 2),
						spaceBetween: spaceMd,
					},
					1200: {
						slidesPerView: preview,
						spaceBetween: spaceLg,
					}
				},
				pagination: pagination ? {
					el: '.tf-sw-pagination',
					clickable: true,
					type: 'bullets',
					dynamicBullets: true,
					dynamicMainBullets: 3,
				} : false,
				navigation: navigation ? {
					nextEl: '.tf-sw-button-next',
					prevEl: '.tf-sw-button-prev',
				} : false,
				on: {
					init: function () {
						// Add fade-in animation to slides
						this.slides.forEach((slide, index) => {
							slide.style.opacity = '0';
							slide.style.transform = 'translateY(20px)';
							setTimeout(() => {
								slide.style.transition = 'all 0.6s ease';
								slide.style.opacity = '1';
								slide.style.transform = 'translateY(0)';
							}, index * 100);
						});
					}
				}
			});
		}

		// Lazy load enhancement for related posts
		const lazyImages = document.querySelectorAll('.tf-swiper img[data-src]');
		if ('IntersectionObserver' in window) {
			const imageObserver = new IntersectionObserver((entries, observer) => {
				entries.forEach(entry => {
					if (entry.isIntersecting) {
						const img = entry.target;
						img.src = img.dataset.src;
						img.classList.remove('lazyload');
						img.classList.add('lazyloaded');
						imageObserver.unobserve(img);
					}
				});
			});

			lazyImages.forEach(img => imageObserver.observe(img));
		}
	});
</script>
<!-- Comments Section -->
<?php if (comments_open() || get_comments_number()): ?>
		<section class="flat-spacing pt-0">
			<div class="container">
				<div class="row">
					<div class="">
						<div class="comments-section">
							<?php comments_template(); ?>
						</div>
					</div>
				</div>
			</div>
		</section>
<?php endif; ?>


<style>

	.comments-section{
  background: var(--c-bg);
  padding: clamp(16px,2vw,28px);
  border-radius: 16px;
}

.comments-area{
  max-width: 860px;
  margin-inline: auto;
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 16px;
  padding: clamp(16px,2.2vw,28px);
  box-shadow: 0 4px 18px rgba(0,0,0,.05);
}

/* Title */
.comments-title{
  font-size: clamp(22px,2.4vw,30px);
  line-height: 1.25;
  margin: 0 0 18px;
  color: var(--c-text);
}
.comments-title span{ color: var(--c-accent); }

/* List */
.comment-list{
  list-style: none;
  margin: 0;
  padding: 0;
  display: grid;
  gap: 14px;
}
.comment-list > li{ list-style: none; }

/* Single comment */
.comment-body{
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 14px;
  padding: 16px 18px;
  position: relative;
  transition: box-shadow .2s ease, transform .2s ease;
  box-shadow: 0 1px 0 rgba(0,0,0,.02);
}

/* Accent bar at the start (RTL/LTR logical) */
.comment-body{
  border-inline-start: 3px solid transparent;
}
.comment-body:hover{
  transform: translateY(-1px);
  box-shadow: 0 8px 22px rgba(0,0,0,.06);
  border-inline-start-color: var(--c-accent);
}

/* Author / meta */
.comment-author{
  display: flex;
  align-items: center;
  gap: 10px;
  margin: 0 0 6px;
}
.comment-author .avatar{
  width: 42px; height: 42px;
  border-radius: 50%;
  border: 2px solid var(--c-border);
  outline: 3px solid transparent;
}
.comment-author .fn a{
  color: var(--c-text);
  font-weight: 700;
  text-decoration: none;
}
.comment-author .says{ color: var(--c-muted); font-weight: 500; }

.comment-metadata{
  font-size: 12.5px;
  color: var(--c-muted);
  margin-bottom: 8px;
}
.comment-metadata a{
  color: var(--c-muted);
  text-decoration: none;
}
.comment-metadata .edit-link a{
  color: var(--c-accent);
  font-weight: 600;
}

/* Content */
.comment-content{
  color: var(--c-text);
  line-height: 1.75;
}
.comment-content p{ margin: 0; }

/* Reply link */
.reply{ margin-top: 10px; }
.reply .comment-reply-link{
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  border-radius: 10px;
  border: 1px solid var(--c-accent);
  color: var(--c-accent);
  font-weight: 600;
  text-decoration: none;
  transition: background .2s, color .2s, transform .2s;
}
.reply .comment-reply-link:hover{
  background: var(--c-accent);
  color: #fff;
  transform: translateY(-1px);
}

/* Nested replies (children) */
.comment-list .children{
  list-style: none;
  margin: 12px 0 0;
  padding: 0;
  border-inline-start: 2px dashed var(--c-border);
  margin-inline-start: 18px; /* works both RTL/LTR logically */
}

/* ===== Comment Form (#respond) ===== */
#respond{
  margin-top: 22px;
  background: var(--c-card);
  border: 1px solid var(--c-border);
  border-radius: 14px;
  padding: 16px 18px;
}
#respond .comment-reply-title{
  margin: 0 0 10px;
  font-size: clamp(18px,1.9vw,22px);
  color: var(--c-text);
}
#respond .logged-in-as{
  margin: 0 0 10px;
  color: var(--c-muted);
  font-size: 13.5px;
}
.comment-form-comment label{
  display: block;
  margin: 0 0 6px;
  font-weight: 700;
  color: var(--c-text);
}
.comment-form textarea{
  width: 100%;
  min-height: 170px;
  padding: 14px 16px;
  border-radius: 12px;
  border: 1px solid var(--c-border);
  background: #f6f7f9;
  color: var(--c-text);
  outline: none;
  resize: vertical;
  transition: border-color .2s, background .2s, box-shadow .2s;
}
.comment-form textarea:focus{
  background: #fff;
  border-color: var(--c-accent);
  box-shadow: 0 0 0 4px color-mix(in oklab, #DDD 18%, transparent);
}

/* Submit button */
#respond .form-submit{ margin-top: 12px; }
#submit.submit{
  appearance: none;
  border: none;
  background: #000;
  color: #fff;
  padding: 10px 14px;
  border-radius: 10px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 2px 0 rgba(0,0,0,.05);
  transition: transform .15s ease, filter .15s ease;
}
#submit:hover{ filter: brightness(1.05); transform: translateY(-1px); }
#submit:active{ transform: translateY(0); }

/* Small tweaks */
.comment-respond a,
.comment-body a{ text-decoration: none; }
.comment-respond a:hover,
.comment-body a:hover{ text-decoration: underline; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
	// Initialize related posts swiper
	const relatedSwiper = document.querySelector('.tf-swiper');
	if (relatedSwiper) {
		const preview = parseInt(relatedSwiper.dataset.preview) || 1;
		const tablet = parseInt(relatedSwiper.dataset.tablet) || 1;
		const mobile = parseInt(relatedSwiper.dataset.mobile) || 1;
		const spaceLg = parseInt(relatedSwiper.dataset.spaceLg) || 0;
		const spaceMd = parseInt(relatedSwiper.dataset.spaceMd) || 0;
		const space = parseInt(relatedSwiper.dataset.space) || 0;
		const pagination = relatedSwiper.dataset.pagination === '1';
		
		new Swiper(relatedSwiper, {
			slidesPerView: mobile,
			spaceBetween: space,
			loop: false,
			autoplay: false,
			breakpoints: {
				576: {
					slidesPerView: mobile,
					spaceBetween: space,
				},
				768: {
					slidesPerView: tablet,
					spaceBetween: spaceMd,
				},
				992: {
					slidesPerView: Math.min(preview, 2),
					spaceBetween: spaceMd,
				},
				1200: {
					slidesPerView: preview,
					spaceBetween: spaceLg,
				}
			},
			pagination: pagination ? {
				el: '.tf-sw-pagination',
				clickable: true,
				type: 'bullets',
			} : false,
		});
	}
	
	// Enhanced social sharing
	document.querySelectorAll('.tf-social-icon a').forEach(link => {
		link.addEventListener('click', function(e) {
			if (this.target === '_blank') {
				e.preventDefault();
				const width = 600;
				const height = 400;
				const left = (screen.width - width) / 2;
				const top = (screen.height - height) / 2;
				
				window.open(
					this.href,
					'share',
					`width=${width},height=${height},left=${left},top=${top},scrollbars=yes,resizable=yes`
				);
			}
		});
	});
});
</script>

<?php get_footer(); ?>