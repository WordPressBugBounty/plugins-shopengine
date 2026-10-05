<?php

namespace Elementor;

defined('ABSPATH') || exit;

class ShopEngine_Product_Image_Config extends \ShopEngine\Base\Widget_Config {

	public function get_name() {
		return 'single-product-images';
	}


	public function get_title() {
		return esc_html__('Product Images', 'shopengine');
	}


	public function get_icon() {
		return 'shopengine-widget-icon shopengine-icon-product_images';
	}


	public function get_categories() {
		return ['shopengine-single'];
	}


	public function get_keywords() {
		return ['woocommerce', 'shop', 'shopengine', 'image', 'product', 'gallery', 'lightbox'];
	}


	public function get_template_territory() {
		return ['single', 'quick_view', 'quick_checkout'];
	}
}
