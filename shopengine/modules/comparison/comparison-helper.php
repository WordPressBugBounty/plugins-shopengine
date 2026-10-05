<?php


namespace ShopEngine\Modules\Comparison;


use ShopEngine\Core\Register\Module_List;

class Comparison_Helper {

	public static function get_html($content = [], $comparison_page =  false) {

		if(empty($content)) {
			return '<h1 class="shopengine-no-comparison-product">'.esc_html__('No product is added for comparison, please add some product to compare', 'shopengine').'</h1>';
		}

		$settings       = Module_List::instance()->get_active_settings( 'comparison' );

		$default_fields = [ 'url', 'image', 'price' ];

		$fields = array_merge($default_fields, $settings['shop_field_in_table']['value'] ?? []);

		$field_value_manager =  new Comparison_Field_Value();

		$displayable_fields = [];
		$product_ids        = [];

		foreach ( $content as $pid ) {
			if ( empty( $pid ) ) {
				continue;
			}

			$product = wc_get_product( $pid );
			if(!$product || $product->get_status() !== 'publish' ) {
			    continue;
			}


			if ( empty( $product ) ) {
				continue;
			}

			$product_ids[] = $pid;

			foreach ( $fields as $slug ) {
				$displayable_fields[ $slug ][ $pid ] = $field_value_manager->get_value( $product, $slug );
			}
		}

		// group first tr values
		$first_tr = [];
		$first_tr['first_tr']['url'] = $displayable_fields['url'] ?? [];
		$first_tr['first_tr']['image'] = $displayable_fields['image'] ?? [];
		$first_tr['first_tr']['title'] = $displayable_fields['title'] ?? [];
		$first_tr['first_tr']['price'] = $displayable_fields['price'] ?? [];

		$displayable_fields = $first_tr + $displayable_fields ;

		unset($displayable_fields['url'], $displayable_fields['image'], $displayable_fields['title'], $displayable_fields['price']);

		/**
		 * Rows added here are printed by the `shopengine/module/comparison/render_table_row/{$slug}` action.
		 */
		$displayable_fields = apply_filters('shopengine/module/comparison/table_rows', $displayable_fields, $product_ids, $settings);

		?>
		<div class="shopengine-modal-wrap">
			<div class="shopengine-comparison-container">
				<div class="shopengine-comparison">
					<h2><?php echo esc_html__('Product comparison', 'shopengine') ?> </h2>

					<?php do_action('shopengine/module/comparison/after_title', $content, $settings); ?>

					<div class="comparison-table-wrap">
						<table class="table table-bordered <?php echo $comparison_page ? 'comparison-page' : '' ?>">
							<tbody> <?php

							foreach ($displayable_fields as $slug => $data){
								$field_value_manager->get_html($slug, $data);
							}
							?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}