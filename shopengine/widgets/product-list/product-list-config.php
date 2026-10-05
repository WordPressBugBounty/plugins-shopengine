<?php

namespace Elementor;

defined('ABSPATH') || exit;

class ShopEngine_Product_List_Config extends \ShopEngine\Base\Widget_Config{

    public function get_name() {
		return 'product-list';
	}


	public function get_title() {
		return esc_html__('Product List', 'shopengine');
	}


	public function get_icon() {
		return 'shopengine-widget-icon shopengine-icon-archive_products';
	}


	public function get_categories() {
		return ['shopengine-archive'];
	}


	public function get_keywords() {
		return ['woocommerce', 'shopengine', 'product', 'product list'];
	}

	public function get_template_territory() {
		return [];
	}

	public function product_order_by() {

        return apply_filters('shopengine/widgets/product-list/order_by_options', [
            'ID'            => esc_html__('ID', 'shopengine'),
            'title'         => esc_html__('Title', 'shopengine'),
            'name'          => esc_html__('Name', 'shopengine'),
            'date'          => esc_html__('Date', 'shopengine'),
            'comment_count' => esc_html__('Popular', 'shopengine')
        ]);
    }

    public function product_query_by() {

        return apply_filters('shopengine/widgets/product-list/query_by_options', [
            'category'  => esc_html__('Category', 'shopengine'),
			'tag'       => esc_html__('Tag', 'shopengine'),
			'product'   => esc_html__('Product', 'shopengine'),
			'rating'    => esc_html__('Rating', 'shopengine'),
			'attribute' => esc_html__('Attribute', 'shopengine'),
			'author'    => esc_html__('Author', 'shopengine'),
        ]);
    }

}