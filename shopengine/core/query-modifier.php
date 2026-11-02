<?php

namespace ShopEngine\Core;

use ShopEngine\Traits\Singleton;

class Query_Modifier
{

    use Singleton;

    private $custom_query = [];

    public function init()
    {

        add_action('pre_get_posts', [$this, 'modify_query']);
    }

    public function modify_query($query)
    {


        if (is_admin() || !$query->is_main_query() || $query->is_single === true) {
            return;
        }

        // Only proceed when it's the product query from widgets OR we're on the shop/product archive
        $is_product_query_flag = isset($query->query_vars['wc_query']) && $query->query_vars['wc_query'] == 'product_query';
        $is_shop_archive = ( function_exists('is_shop') && is_shop() ) || ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'product' ) || ( isset($query->is_post_type_archive) && $query->is_post_type_archive === true );

        if ( ! $is_product_query_flag && ! $is_shop_archive ) {
            return;
        }

        // query filter begins

        // update query for product per page filter
        //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's a fronted user part, not possible to verify nonce here
        if (!empty($_GET['shopengine_products_per_page'])) {
            //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $query->set('posts_per_page', absint(intval($_GET['shopengine_products_per_page'])));
        }

        // `filter_pa_*` params (attribute links of the Product Tabs widget) are handled on the shop archive
        $has_filter_params = false;
        if ( $is_shop_archive ) {
            foreach ( $_GET as $k => $v ) {
                if ( strpos( $k, 'filter_pa_' ) === 0 && ! empty( $v ) ) {
                    $taxonomy = substr( $k, strlen('filter_') ); // e.g. pa_color
                    $values = explode(',', trim( $v ));

                    $this->custom_query['relation'] = 'AND';
                    $this->custom_query[] = [
                        'taxonomy' => $taxonomy,
                        'field'    => 'slug',
                        'terms'    => $values,
                        'operator' => 'IN',
                    ];

                    $has_filter_params = true;
                }
            }
        }

        /**
         * Whether the product query is filtered on every request, not only when
         * the attribute links above are used. Extensions that add their own
         * filters through the two hooks below turn this on.
         *
         * @param bool      $enabled
         * @param \WP_Query $query
         */
        if (!apply_filters('shopengine/query_modifier/enabled', false, $query) && !$has_filter_params) {
            return;
        }

        $meta_query = ['relation' => 'AND'];

        //phpcs:ignore WordPress.Security.NonceVerification.Recommended -- It's a fronted user part, not possible to verify nonce here
        foreach ($_GET as $key => $value) {

            // Support custom attribute filtering (filter_custom_{slug})
            if (strpos($key, 'filter_custom_') === 0) {
                $values = array_map('sanitize_text_field', explode(',', trim($value)));

                // Search in serialized _product_attributes meta for custom attributes
                foreach ($values as $val) {
                    $meta_query[] = [
                        'key' => '_product_attributes',
                        'value' => '"' . $val . '"',
                        'compare' => 'LIKE'
                    ];
                }
            }
        }

        /**
         * Meta query clauses for the main product query.
         *
         * @param array     $meta_query
         * @param \WP_Query $query
         */
        $meta_query = apply_filters('shopengine/query_modifier/meta_query', $meta_query, $query);

        /**
         * Taxonomy query clauses for the main product query.
         *
         * @param array     $tax_query
         * @param \WP_Query $query
         */
        $this->custom_query = apply_filters('shopengine/query_modifier/tax_query', $this->custom_query, $query);

        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }

        $product_visibility_terms  = wc_get_product_visibility_term_ids();
        $product_visibility_not_in = array( $product_visibility_terms['exclude-from-catalog'] );

		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$product_visibility_not_in[] = $product_visibility_terms['outofstock'];
		}
        $this->custom_query['tax_query'][] = apply_filters('shopengine-product-visibility-modifier',[
            'taxonomy'  => 'product_visibility',
            'terms'     =>  $product_visibility_not_in,
            'field'     => 'term_taxonomy_id',
            'operator'  => 'NOT IN',
        ]);

        $query->set('tax_query', apply_filters('shopengine-tax-query-modifier', $this->custom_query));
    }
}
