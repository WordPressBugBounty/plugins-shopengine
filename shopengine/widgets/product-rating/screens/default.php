<?php defined('ABSPATH') || exit;

global $product;
if(!$product){
	return;
}

// return if review not available
$is_editor = ($post_type == \ShopEngine\Core\Template_Cpt::TYPE) ? true : false;
$rating_count = $product->get_rating_count();
$show_zero_reviews = isset($settings['shopengine_show_zero_reviews']) && $settings['shopengine_show_zero_reviews'] === 'yes';

if(!$is_editor && (!post_type_supports('product', 'comments') || !wc_review_ratings_enabled() || (!$show_zero_reviews && $rating_count <= 0) || !function_exists('woocommerce_template_single_rating'))) {
	return;
}

\ShopEngine\Widgets\Widget_Helper::instance()->wc_template_filter_by_match('woocommerce/single-product/rating.php', 'templates/single-product/rating.php');

$shopengine_rating_singular_label = !empty($settings['shopengine_rating_singular_label']) ? $settings['shopengine_rating_singular_label'] : __('Customer Review', 'shopengine');
$shopengine_rating_plural_label = !empty($settings['shopengine_rating_plural_label']) ? $settings['shopengine_rating_plural_label'] :  __('Customer Reviews', 'shopengine');

$rating_count = $product->get_rating_count();
$review_count = $product->get_review_count();
$average      = $product->get_average_rating();

/*
 * The labels are widget settings (user input), so they are escaped on output and never
 * used as a printf format. A count of 1 picks the singular label, any other count the
 * plural one - the same choice _n() made when the labels were joined into its format.
 */
$shopengine_rating_label = function($count) use ($shopengine_rating_singular_label, $shopengine_rating_plural_label) {
	return 1 === (int) $count ? $shopengine_rating_singular_label : $shopengine_rating_plural_label;
};
?>

<div class="shopengine-product-rating">

	<?php if($is_editor) : ?>

		<div class="woocommerce-product-rating">
			<?php shopengine_content_render( wc_get_rating_html( $average, $rating_count ) ); ?>
			<a title="<?php esc_html_e('Product Reviews','shopengine')?>" href="#reviews" class="woocommerce-review-link" rel="nofollow">
				(<span class="count"><?php echo esc_html( $review_count ); ?></span> <?php echo esc_html( $shopengine_rating_label( $rating_count ) ); ?>)
			</a>
		</div>

	<?php else :

		
		if ( ! wc_review_ratings_enabled() ) {
			return;
		}

		if ( $rating_count > 0 || ($show_zero_reviews && $rating_count <= 0) ) : ?>

			<div class="woocommerce-product-rating">
				<?php shopengine_content_render( wc_get_rating_html( $average, $rating_count ) ); ?>
				<?php if ( comments_open() ) : ?>
					<a title="<?php esc_html_e('Product Reviews','shopengine')?>" href="#reviews" class="woocommerce-review-link" rel="nofollow">(<span class="count"><?php echo esc_html( $review_count ); ?></span> <?php echo esc_html( $shopengine_rating_label( $review_count ) ); ?>)</a>
				<?php endif ?>
			</div>
		<?php
		endif; 
	endif;
	?>
</div>