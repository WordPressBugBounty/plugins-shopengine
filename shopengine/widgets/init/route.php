<?php

namespace ShopEngine\Widgets\Init;

use ShopEngine\Base\Api;
defined('ABSPATH') || exit;
class Route extends Api
{

	public function config() {

		$this->prefix = 'widgets_partials';
		$this->param = "";
	}

	public function get_filter_cat_products() {

		$data = $this->request->get_params();

		$products = $data['products'];
		$order = isset($data['order']) ? $data['order'] : 'DESC';
		$order_by = isset($data['order_by']) ? $data['order_by'] : 'date';
		$settings = isset($data['settings']) ? $data['settings'] : null;

		if(empty($products)) {

			echo "<p class='error'>" . esc_html__('No products were found.', 'shopengine') . "</p>";

			exit();
		}

		$args = array(
			'post_type'      => 'product',
			'order'          => $order,
			'orderby'        => $order_by,
			'post__in'       => $products,
			'posts_per_page' => 50
		);


		$query = new \WP_Query($args);

		if($query->have_posts()) :
			while($query->have_posts()) : $query->the_post();

			$default_content = [
				'image'     => 0,
				'category'  => 0,
				'title'     => 0,
				'rating'    => 0,
				'price'     => 0,
				'description' => 0,
				'buttons'     => 0,
		  ];



				$content =  (!empty($view) ? $view : $default_content);
				asort($content, SORT_NUMERIC);

				if( $settings['shopengine_is_cats'] != 'yes' ) unset($content['category']);
				if( $settings['shopengine_is_details'] != 'yes' ) {
					unset($content['description']);
				}
				if( $settings['shopengine_is_product_rating'] != 'yes' ) unset($content['rating']);


				?> <div class='shopengine-single-product-item'> <?php
					foreach($content as $key => $value) {
						$function = '_product_' .  $key;
						\ShopEngine\Utils\Helper::$function($settings);
					}
				?> </div> <?php
				
			endwhile;
		endif;

		wp_reset_postdata();

		exit();
	}

	public function post_checkout_login() {

		// Verify nonce for CSRF protection
		$nonce = $this->request->get_header('X-WP-Nonce');
		if (empty($nonce) || !wp_verify_nonce($nonce, 'wp_rest')) {
			return new \WP_Error('rest_forbidden', esc_html__('Invalid nonce.', 'shopengine'), array('status' => 403));
		}

		// The wp_rest nonce above is not session-bound for logged-out requests, so it
		// can be harvested from any public page and replayed cross-origin. Require the
		// request to actually originate from this site as the real CSRF guard.
		$source = $this->request->get_header('Origin');
		if (empty($source)) {
			$source = $this->request->get_header('Referer');
		}
		$source_host = $source ? wp_parse_url($source, PHP_URL_HOST) : '';
		if (empty($source_host) || strcasecmp($source_host, wp_parse_url(home_url(), PHP_URL_HOST)) !== 0) {
			return new \WP_Error('rest_forbidden', esc_html__('Invalid request origin.', 'shopengine'), array('status' => 403));
		}

		$data = $this->request->get_params();
		if(isset($data['rememberme'])) {
			$data['rememberme'] = $data['rememberme'] == 'true' ? true : false;
		}
		$user = wp_signon($data);
		if(is_wp_error($user)) {
			return new \WP_Error('failed_login', $user->get_error_message(), ['status' => 404]);
		}
		return true;
	}
}
