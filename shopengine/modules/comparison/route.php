<?php

namespace ShopEngine\Modules\Comparison;

use ShopEngine\Base\Api;
use ShopEngine\Utils\Helper;

class Route extends Api {

	public function config() {

		$this->prefix = 'comparison';
		$this->param  = "";
	}


	public function get_comparison_table() {
		$data       = $this->request->get_params();
		$product_id = !empty($data['pid']) ? $data['pid'] : "";

		if( $product_id ) Comparison_Cookie::add_product_id($product_id);
		$product_ids = Comparison_Cookie::get_product_ids($product_id);

		Comparison_Helper::get_html($product_ids);
		exit();
	}

	public function post_remove() {

		$data = $this->request->get_params();
		$pid  = $data['pid'];

		Comparison_Cookie::remove_product_id($pid);

		wp_send_json([
			"status" => "Success",
		]);
	}
}
