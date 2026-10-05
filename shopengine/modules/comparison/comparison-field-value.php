<?php


namespace ShopEngine\Modules\Comparison;


use ShopEngine\Core\Register\Module_List;
use ShopEngine\Modules\Swatches\Helper;
use ShopEngine\Modules\Swatches\Swatches;
use ShopEngine\Utils\Helper as UtilsHelper;
use WC_Product;

class Comparison_Field_Value {

	private $generated_attributes = [];

	public function get_value( WC_Product $product, $slug ) {

		return $this->set_value( $product, $slug );
	}

	private function set_value( WC_Product $product, $slug ) {
		switch ( $slug ) {
			case 'url':
				return $product->add_to_cart_url();
			case 'image':
				return $product->get_image();
			case 'title':
				return $product->get_title();
			case 'price':
				$price['regular'] = $product->get_regular_price();
				$price['sale']    = $product->get_sale_price();
				$price['price']   = $product->get_price();
				$price['htm']     = $product->get_price_html();

				return $price;
			case 'description':
				return $product->get_description();
			case 'availability':
				return $product->get_stock_status();
			case 'sku':
				return $product->get_sku();
			case 'weight':
				return $product->get_weight();
			case 'dimension':
				return $product->get_dimensions( false );
			case 'height':
				return $product->get_height();
		}

		return '';
	}

	public function get_html( $slug, $data ) {

		// Rows added through the `shopengine/module/comparison/table_rows` filter
		if ( has_action( 'shopengine/module/comparison/render_table_row/' . $slug ) ) {
			do_action( 'shopengine/module/comparison/render_table_row/' . $slug, $data );

			return;
		}

		switch ( $slug ) {
			case 'first_tr':
				?>
                <tr>
                    <th style="vertical-align: middle;">  <?php
						esc_html_e( 'Product Name', 'shopengine' ) ?> </th>
					<?php
					$this->print_first_tr( $data ); ?>
                </tr>


				<?php
				break;
			case 'color':
				?>
                <tr>
                    <th style="vertical-align: middle;">  <?php
						echo esc_html(ucwords( str_replace( '_', ' ', $slug ) )) ?> </th>
					<?php
					$this->print_color_attribute( $slug, $data ); ?>
                </tr>
				<?php
				break;
			case 'availability':
				?>
				<tr>
					<th style="vertical-align: middle;">  <?php
						echo esc_html__( 'Availability', 'shopengine' ) ?> </th>
					<?php
					$this->print_tr( $slug, $data ); ?>
				</tr>
				<?php
				break;
			case 'weight':
				?>
				<tr>
					<th style="vertical-align: middle;">  <?php
						echo esc_html__( 'Weight', 'shopengine' ) ?> </th>
					<?php
					$this->print_tr( $slug, $data ); ?>
				</tr>
				<?php
				break;
			case 'description':
				?>
				<tr>
					<th style="vertical-align: middle;">  <?php
						echo esc_html__( 'Description', 'shopengine' ) ?> </th>
					<?php
					$this->print_tr( $slug, $data ); ?>
				</tr>
				<?php
				break;
			case 'height':
				?>
				<tr>
					<th style="vertical-align: middle;">  <?php
						echo esc_html__( 'Height', 'shopengine' ) ?> </th>
					<?php
					$this->print_tr( $slug, $data ); ?>
				</tr>
				<?php
				break;
			case 'dimension':
				?>
				<tr>
					<th style="vertical-align: middle;">  <?php
						echo esc_html__( 'Dimension', 'shopengine' ) ?> </th>
					<?php
					$this->print_tr( $slug, $data ); ?>
				</tr>
				<?php
				break;
			default:				
				?>
                <tr>
                    <th style="vertical-align: middle;">  <?php
							echo  esc_html(ucwords( str_replace( '_', ' ', $slug ) ));
						?> 
					</th>
					<?php
					$this->print_tr( $slug, $data ); ?>
                </tr>
				<?php
				break;
		}
	}

	public function get_add_to_cart( WC_Product $product , $args = array() ) {

		if ( $product ) {
			$defaults = array(
				'quantity'   => 1,
				'class'      => implode(
					' ',
					array_filter(
						array(
							'compare-cart-btn',
							'button',
							'product_type_' . $product->get_type(),
							$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
							$product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ? 'ajax_add_to_cart' : '',
						)
					)
				),
				'attributes' => array(
					'data-product_id'  => $product->get_id(),
					'data-product_sku' => $product->get_sku(),
					'aria-label'       => $product->add_to_cart_description(),
					'rel'              => 'nofollow',
				),
			);

			$args = apply_filters( 'woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

			if ( isset( $args['attributes']['aria-label'] ) ) {
				$args['attributes']['aria-label'] = wp_strip_all_tags( $args['attributes']['aria-label'] );
			}
			$cart = esc_html__("Go Cart Page", "shopengine");

			$html = apply_filters(
				'woocommerce_modal_add_to_cart_link', // WPCS: XSS ok.
				sprintf(
					'<a title="' . $cart . '" href="%s" data-quantity="%s" class="%s" %s>%s</a>',
					esc_url( $product->add_to_cart_url() ),
					esc_attr( isset( $args['quantity'] ) ? $args['quantity'] : 1 ),
					esc_attr( isset( $args['class'] ) ? $args['class'] : 'button' ),
					isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
					esc_html( $product->add_to_cart_text() )
				),
				$product,
				$args
			);

			echo wp_kses($html, UtilsHelper::get_kses_array());
		}
	}

	private function print_first_tr( $data ) {
		foreach ( $data['image'] as $product_id => $datum ) {
			$product = wc_get_product( $product_id );
			 ?>
			<td class="first--row">
				<?php
				$remove_button = sprintf( '<a class="shopengine-remove-action badge-comparison" data-pid="%s"><i class="eicon-close"></i> %s</a>', esc_attr( $product_id ), esc_html__( 'Remove', 'shopengine' ) );
				echo wp_kses($remove_button, UtilsHelper::get_kses_array());
				echo wp_kses($datum, UtilsHelper::get_kses_array());
				echo wp_kses((isset($data['title'][ $product_id ]) ? '<h4>'.$data['title'][ $product_id ].'</h4>': ''), UtilsHelper::get_kses_array());
				echo wp_kses((isset($data['price'][ $product_id ]['htm']) ? '<div>'.$data['price'][ $product_id ]['htm'].'</div>': ''), UtilsHelper::get_kses_array());
				?>
				<div class="comparison-add-to-cart">
					<?php $this->get_add_to_cart( $product );?>
				</div>
			</td>
			<?php
		}
	}

	private function print_color_attribute( $slug, $data ) {
		foreach ( $data as $product_id => $datum ) {
			?>
            <td class="first--row">
				<?php

				foreach ( $datum as $attribute ) {
					foreach ( $attribute['value'] as $value ) {
						echo wp_kses('<span class="comparison-color-badge" style="background-color:' . $value . '"></span> ', UtilsHelper::get_kses_array());
					}
				}
				?>
            </td>

			<?php
		}
	}

	private function print_tr( $slug, $data ) {
		foreach ( $data as $product_id => $datum ) {
			if ( $slug == 'dimension' ) {
				$datum = wc_format_dimensions( $datum );
			}
			?>
            <td class="first--row">
				<?php echo wp_kses($datum, UtilsHelper::get_kses_array()) ?>
            </td>

			<?php
		}
	}

}