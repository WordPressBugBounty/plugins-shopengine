<?php

namespace ShopEngine\Core\Page_Templates\Hooks;

use ShopEngine;
use ShopEngine\Widgets\Widget_Helper;

defined('ABSPATH') || exit;

class Checkout extends Base {

	protected $page_type = 'checkout';
	protected $template_part = 'content-checkout.php';

	public function init(): void {

		add_filter('wc_get_template', function ( $template ) {
			if ( strpos( $template, 'checkout/review-order.php' ) !== false ) {
				return ShopEngine::widget_dir() . 'checkout-review-order/screens/review-order-template.php';
			}
			return $template;
		});

		/**
		 * Fires when a ShopEngine checkout template takes over the checkout page.
		 *
		 * @param Checkout $checkout
		 */
		do_action('shopengine/templates/checkout/init', $this);

		Widget_Helper::instance()->wc_template_replace_multiple(
			[
				'checkout/review-order.php',
				'checkout/payment-method.php',
			]
		);

		remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10);

		remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form');


        /**
         * Remove checkout template extra markup;
         */
        remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);

		do_action( 'woocommerce_check_cart_items' );


		$this->delayed_hook_conflicts();

		add_action('wp_enqueue_scripts', function () {

			if(is_plugin_active('auxin-shop/auxin-shop.php')) {

				wp_dequeue_style('auxin-shop');
			
			}
			
			$themeName = get_template();
			if ($themeName == 'phlox-pro') {

				wp_dequeue_style('auxin-elementor-base');
			}

		}, 20);
	} 

	public function delayed_hook_conflicts() {

		if ( is_plugin_active( 'auxin-shop/auxin-shop.php' ) ) {

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
		}

		// Brevo (WooCommerce Email Marketing) registers its opt-in via the Blocks API when
		// WooCommerce Blocks checkout is set as the default page. ShopEngine renders the
		// classic checkout, so the Blocks-registered field never appears. Re-register
		// Brevo's classic hooks so the newsletter consent checkbox shows up.
		$brevo_plugin = 'woocommerce-sendinblue-newsletter-subscription/woocommerce-sendinblue.php';
		$brevo_blocks_path_active =
			is_plugin_active( $brevo_plugin ) &&
			class_exists( 'Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils' ) &&
			method_exists( 'Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils', 'is_checkout_block_default' ) &&
			\Automattic\WooCommerce\Blocks\Utils\CartCheckoutUtils::is_checkout_block_default() &&
			class_exists( 'SendinblueWoocommerce\Managers\ApiManager' );

		if ( $brevo_blocks_path_active ) {
			// Brevo took the Blocks code path so classic hooks were never registered.
			// ShopEngine renders classic checkout, so register them now.
			( new \SendinblueWoocommerce\Managers\ApiManager() )->add_conditional_hooks();
		}
	}
	
	protected function get_page_type_option_slug(): string {
		if(!empty($_REQUEST['nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_REQUEST['nonce'])), 'wp_rest')) {
			return !empty($_REQUEST['shopengine_quick_checkout']) && $_REQUEST['shopengine_quick_checkout'] === 'modal-content' ? 'quick_checkout' : $this->page_type;
		}
		return $this->page_type;
	}

	protected function template_include_pre_condition(): bool {
		//phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's a fronted user part, not possible to verify nonce here
		return (is_checkout() && !is_wc_endpoint_url('order-received') && !is_checkout_pay_page()) || (isset($_REQUEST['wc-ajax']) &&  $_REQUEST['wc-ajax'] == 'update_order_review');
	}
}
