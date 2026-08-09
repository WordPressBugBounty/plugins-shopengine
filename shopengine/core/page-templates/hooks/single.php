<?php

namespace ShopEngine\Core\Page_Templates\Hooks;

use ShopEngine\Core\Builders\Templates;

defined('ABSPATH') || exit;

class Single extends Base {

	protected $page_type = 'single';
	protected $template_part = 'content-single-product.php';

	public function init() : void {
		// nothing is going on here
		add_action('wp_enqueue_scripts', [$this,'single_page_css_conflict_remove'], 20);

		$this->delayed_hook_conflicts();
	}

	protected function get_page_type_option_slug(): string {
		if(!empty($_REQUEST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'wp_rest')) {
			return !empty($_REQUEST['shopengine_quickview']) && $_REQUEST['shopengine_quickview'] === 'modal-content' ? 'quick_view' : $this->page_type;
		}
		return $this->page_type;
	}

	protected function template_include_pre_condition() : bool {

		return is_product();
	}

	public function before_hooks() {
		do_action( 'woocommerce_before_single_product' );

		$this->generate_product_structured_data();
	}

	/**
	 * Emit WooCommerce Product JSON-LD (schema.org) structured data.
	 *
	 * ShopEngine renders the single product from builder widgets and never fires
	 * the `woocommerce_single_product_summary` action, which is where WooCommerce
	 * normally hooks WC_Structured_Data::generate_product_data() (priority 60).
	 * Without this call the collected structured-data array stays empty and
	 * WooCommerce's wp_footer output prints nothing, so the Product schema
	 * disappears whenever a ShopEngine single template is active. Generating it
	 * here lets WooCommerce's own output_structured_data() render it as usual.
	 *
	 * @return void
	 */
	protected function generate_product_structured_data() {

		// Skip Quick View: its markup is loaded into a modal and would inject
		// duplicate/misplaced product schema into the host page's footer.
		if ( $this->get_page_type_option_slug() === 'quick_view' ) {
			return;
		}

		if ( ! function_exists( 'WC' ) || empty( WC()->structured_data ) ) {
			return;
		}

		global $product;

		if ( ! $product instanceof \WC_Product ) {
			$product = wc_get_product( get_queried_object_id() );
		}

		if ( $product instanceof \WC_Product ) {
			WC()->structured_data->generate_product_data( $product );
		}
	}

	public function after_hooks()
	{
		do_action( 'woocommerce_after_single_product' );
		$themeName = get_template();
			if ( $themeName == 'eduma' ) {
				wp_dequeue_script('thim-main');
				wp_dequeue_script('thim-custom-script');
			}
	}

	public function single_page_css_conflict_remove() {

		// Remove style and script for astra addon single product layout
		if(is_plugin_active('astra-addon/astra-addon.php')) {

			wp_dequeue_style('astra-addon-css');
		}

		if (function_exists('wp_get_theme')) {
			$theme = wp_get_theme();
			$active_theme = $theme->get('Name');

			if ($active_theme === 'PHOX' || $active_theme === 'PHOX Child') {

				wp_dequeue_style('wdes-woocommerce');
				wp_dequeue_script('bootstrap');
			}
		}

		$themeName = get_template();
		// Remove Auxin Shop CSS if Auxin Shop is active
		if(is_plugin_active('auxin-shop/auxin-shop.php')) {

			wp_dequeue_style('auxin-shop');
			
		}  if ($themeName == 'phlox-pro') {

			wp_dequeue_style('auxin-elementor-base');
		}

		/**
		 * Remove Envo Shop JS if Envo Shop is active
		 * This is to prevent conflicts with ShopEngine's single product template
		 */

		if($themeName == 'envo-shop') {

			// Stop Envo Shop from rendering its own duplicate plus/minus buttons
			remove_action('woocommerce_before_add_to_cart_quantity', 'envo_shop_display_quantity_minus');
			remove_action('woocommerce_after_add_to_cart_quantity', 'envo_shop_display_quantity_plus');

			// Envo Shop's click handler is bound with the generic 'button.plus, button.minus'
			// selector, which also matches ShopEngine's own quantity buttons (same markup),
			// so it still double-fires alongside ShopEngine's handler even without its own
			// buttons rendered. Unbind it specifically, leaving ShopEngine's handler intact.
			wp_add_inline_script(
				'envo-shop-theme-js',
				"jQuery(function($){ $('form.cart').off('click', 'button.plus, button.minus'); });",
				'after'
			);
		}

	}

	public function delayed_hook_conflicts() {

		if (is_plugin_active('auxin-shop/auxin-shop.php')) {

			remove_filter('wc_get_template', 'auxshp_get_wc_template', 11, 2);

			// Remove Auxin Shop template loader filter
			global $wp_filter;
			if (isset($wp_filter['woocommerce_locate_template'])) {
				foreach ($wp_filter['woocommerce_locate_template']->callbacks as $priority => $callbacks) {
					foreach ($callbacks as $key => $callback) {
						if (is_array($callback['function']) && 
							is_object($callback['function'][0]) && 
							get_class($callback['function'][0]) === 'AUXSHP_Template_Loader' && 
							$callback['function'][1] === 'load_templates') {
							unset($wp_filter['woocommerce_locate_template']->callbacks[$priority][$key]);
						}
					}
				}
			}

			if (isset($wp_filter['woocommerce_after_single_product']) && 
				isset($wp_filter['woocommerce_after_single_product']->callbacks[20]) && 
				is_array($wp_filter['woocommerce_after_single_product']->callbacks[20])) {
				foreach ($wp_filter['woocommerce_after_single_product']->callbacks[20] as $key => $callback) {
					if (is_array($callback['function']) && 
						is_object($callback['function'][0]) && 
						get_class($callback['function'][0]) === 'AUXSHP_Template_Loader' && 
						$callback['function'][1] === 'auxshp_related_products') {
						unset($wp_filter['woocommerce_after_single_product']->callbacks[20][$key]);
					}
				}
			}
		}
	}
}
