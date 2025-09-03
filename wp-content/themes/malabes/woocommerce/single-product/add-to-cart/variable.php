<?php
/**
 * Variable product add to cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/variable.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.6.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$attribute_keys  = array_keys( $attributes );
$variations_json = wp_json_encode( $available_variations );
$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : _wp_specialchars( $variations_json, ENT_QUOTES, 'UTF-8', true );

do_action( 'woocommerce_before_add_to_cart_form' ); ?>

<form class="variations_form cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data' data-product_id="<?php echo absint( $product->get_id() ); ?>" data-product_variations="<?php echo $variations_attr; // WPCS: XSS ok. ?>">
	<?php do_action( 'woocommerce_before_variations_form' ); ?>

	<?php if ( empty( $available_variations ) && false !== $available_variations ) : ?>
		<p class="stock out-of-stock"><?php echo esc_html( apply_filters( 'woocommerce_out_of_stock_message', __( 'This product is currently out of stock and unavailable.', 'woocommerce' ) ) ); ?></p>
		
	<?php else : ?>
		<table class="variations" cellspacing="0" role="presentation">
			<tbody>
				<?php foreach ( $attributes as $attribute_name => $options ) : ?>
					<tr>
						<th class="label d-none"><label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); // WPCS: XSS ok. ?></label></th>
						<td class="value tf-grid-layout md-col-12">
							<?php
								// wc_dropdown_variation_attribute_options(
								// 	array(
								// 		'options'   => $options,
								// 		'attribute' => $attribute_name,
								// 		'product'   => $product,
								// 	)
								// );
					
							// wc_dropdown_variation_attribute_options(array(
							// 	'options' => $options,
							// 	'attribute' => $attribute_name,
							// 	'product' => $product,
							// 	'show_option_none' => wc_attribute_label($attribute_name), // نص افتراضي
							// 	'class' => 'mt-5 wc-variation-select form-select',     // ستايلك
							// 	'id' => 'select-' . sanitize_title($attribute_name), // ID ثابت مفيد للـ JS
							// ));

								
								/**
								 * Filters the reset variation button.
								 *
								 * @since 2.5.0
								 *
								 * @param string  $button The reset variation button HTML.
								 */
								//echo end( $attribute_keys ) === $attribute_name ? wp_kses_post( apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'Clear options', 'woocommerce' ) . '">' . esc_html__( 'Clear', 'woocommerce' ) . '</a>' ) ) : '';
							?>

							<?php
								foreach ($attributes as $attribute_name => $options):
									$selected = isset($_REQUEST['attribute_' . sanitize_title($attribute_name)]) ?
										wc_clean(stripslashes(urldecode($_REQUEST['attribute_' . sanitize_title($attribute_name)]))) :
										$product->get_variation_default_attribute($attribute_name);
									?>
								<div class="wc-variation-boxes mt-5" id="select-<?php echo esc_attr(sanitize_title($attribute_name)); ?>">
									<label class="variation-label"><?php echo esc_html(wc_attribute_label($attribute_name)); ?></label>
									<div class="variation-boxes"
										data-attribute_name="attribute_<?php echo esc_attr(sanitize_title($attribute_name)); ?>">
										<?php foreach ($options as $option): ?>
											<?php
											$is_selected = (sanitize_title($option) === sanitize_title($selected)) ? 'selected' : '';
											?>
											<div class="variation-box <?php echo $is_selected; ?>" data-value="<?php echo esc_attr($option); ?>">
												<?php echo esc_html(apply_filters('woocommerce_variation_option_name', $option)); ?>
											</div>
										<?php endforeach; ?>
									</div>
									<?php
									// الاحتفاظ بـ <select> الأصلي (مخفي)
									wc_dropdown_variation_attribute_options(array(
										'options' => $options,
										'attribute' => $attribute_name,
										'product' => $product,
										'selected' => $selected,
										'show_option_none' => wc_attribute_label($attribute_name),
										'class' => 'mt-5 wc-variation-select form-select hidden', // إضافة كلاس hidden
										'id' => 'select-' . sanitize_title($attribute_name),
									));
									?>
								</div>
							<?php endforeach; ?>
<style>
	.wc-variation-boxes {
    margin-top: 1.5rem;
}
.variation-label {
    display: block;
    font-weight: bold;
    margin-bottom: 0.5rem;
}
.variation-boxes {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.variation-box {
    padding: 10px 20px;
    border: 2px solid #ccc;
    border-radius: 5px;
    cursor: pointer;
    background-color: #f9f9f9;
    transition: all 0.3s ease;
    text-align: center;
    min-width: 60px;
}
.variation-box:hover {
    background-color: #e0e0e0;
}
.variation-box.selected {
    background-color: #007cba;
    color: #fff;
    border-color: #007cba;
}
.wc-variation-select.hidden {
    display: none !important;
}
</style>

<script>
	jQuery(document).ready(function($) {
    jQuery('.variation-box').on('click', function() {
        var $this = $(this);
        var $parent = $this.closest('.variation-boxes');
        var attribute_name = $parent.data('attribute_name');
        var value = $this.data('value');

        // إزالة الكلاس selected من الخيارات الأخرى
        $parent.find('.variation-box').removeClass('selected');
        // إضافة الكلاس selected للخيار المضغوط
        $this.addClass('selected');

        // تحديث قيمة <select> المخفي
        var $select = $parent.siblings('.wc-variation-select');
        $select.val(value).trigger('change');

        // إطلاق حدث تحديث التباينات
        jQuery('form.variations_form').trigger('check_variations');
    });

    // تحديث التباينات عند تحميل الصفحة
    jQuery('form.variations_form').on('wc_variation_form', function() {
        jQuery(this).trigger('check_variations');
    });
});
</script>
							
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
		<div class="reset_variations_alert screen-reader-text" role="alert" aria-live="polite" aria-relevant="all"></div>
		<?php do_action( 'woocommerce_after_variations_table' ); ?>

		<div class="single_variation_wrap">
			<?php
				/**
				 * Hook: woocommerce_before_single_variation.
				 */
				do_action( 'woocommerce_before_single_variation' );

				/**
				 * Hook: woocommerce_single_variation. Used to output the cart button and placeholder for variation data.
				 *
				 * @since 2.4.0
				 * @hooked woocommerce_single_variation - 10 Empty div for variation data.
				 * @hooked woocommerce_single_variation_add_to_cart_button - 20 Qty and cart button.
				 */
				do_action( 'woocommerce_single_variation' );

				/**
				 * Hook: woocommerce_after_single_variation.
				 */
				do_action( 'woocommerce_after_single_variation' );
			?>
		</div>
	<?php endif; ?>

	<?php do_action( 'woocommerce_after_variations_form' ); ?>
</form>

<?php
do_action( 'woocommerce_after_add_to_cart_form' );
