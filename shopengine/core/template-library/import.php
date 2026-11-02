<?php

namespace ShopEngine\Core\Template_Library;

use ShopEngine\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

/**
 * Imports a remote library design into an existing `shopengine-template` post:
 * fetch → validate → gate → sanitize → save.
 */
class Import {

	use Singleton;

	/**
	 * @param array $args {
	 *     @type int    $post_id       Target shopengine-template post.
	 *     @type int    $layout_id     Remote layout ID.
	 *     @type string $template_type ShopEngine template type the user picked.
	 *     @type string $editor        elementor | gutenberg.
	 * }
	 * @return true|\WP_Error
	 */
	public function run( array $args ) {
		$post_id       = isset( $args['post_id'] ) ? absint( $args['post_id'] ) : 0;
		$layout_id     = isset( $args['layout_id'] ) ? absint( $args['layout_id'] ) : 0;
		$template_type = isset( $args['template_type'] ) ? (string) $args['template_type'] : '';
		$editor        = isset( $args['editor'] ) ? (string) $args['editor'] : '';

		if ( ! $post_id || ! $layout_id || ! in_array( $editor, Validator::EDITORS, true ) ) {
			return new \WP_Error( 'shopengine_tpl_args', esc_html__( 'Invalid template import request.', 'shopengine' ), [ 'status' => 400 ] );
		}

		$body = Remote_Api::get_layout( $layout_id );
		if ( is_wp_error( $body ) ) {
			return $body;
		}

		$layout = Validator::layout( $body, $layout_id, $template_type, $editor );
		if ( is_wp_error( $layout ) ) {
			return $layout;
		}

		// The API refuses designs this site may not import too; this keeps the
		// gate in place even if an API site runs without enforcement.
		if ( ! Access::can_import( $layout['package'] ) ) {
			return Access::denied_error( $layout['package'] );
		}

		return 'elementor' === $editor
			? $this->import_elementor( $post_id, $layout )
			: $this->import_gutenberg( $post_id, $layout );
	}

	private function import_elementor( $post_id, array $layout ) {
		if ( ! class_exists( '\Elementor\TemplateLibrary\Source_Base' ) || ! Source::is_elementor_ready() ) {
			return new \WP_Error( 'shopengine_tpl_elementor', esc_html__( 'Elementor must be active to import this template.', 'shopengine' ), [ 'status' => 409 ] );
		}

		$content = Validator::decode_unicode_escapes( $layout['content'] );
		$content = Validator::sanitize_elementor_markup( Validator::neutralize_foreign_media( $content ) );

		// SVG icons can only be sideloaded with unfiltered uploads on. Allow that
		// strictly for this import, for users who may add unfiltered HTML anyway,
		// and only for media already restricted to the library host above.
		$allow_svg = current_user_can( 'unfiltered_html' );
		if ( $allow_svg ) {
			add_filter( 'elementor/files/allow_unfiltered_upload', '__return_true' );
		}

		try {
			$content = Source::instance()->prepare_content( $content );
		} finally {
			if ( $allow_svg ) {
				remove_filter( 'elementor/files/allow_unfiltered_upload', '__return_true' );
			}
		}

		$this->save_elementor_data( $post_id, $content );

		$normalized = Source::instance()->normalize_document_content( $post_id, $content );
		if ( $normalized !== $content ) {
			$this->save_elementor_data( $post_id, $normalized );
		}

		$settings = $layout['settings'];
		unset( $settings['template'] ); // ShopEngine picks the page template per type itself.
		if ( ! empty( $settings ) ) {
			update_post_meta( $post_id, '_elementor_page_settings', array_map( [ $this, 'clean_setting' ], $settings ) );
		}

		return true;
	}

	private function save_elementor_data( $post_id, array $content ) {
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $content ) ) );

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			update_post_meta( $post_id, '_elementor_version', ELEMENTOR_VERSION );
		}
	}

	private function clean_setting( $value ) {
		if ( is_array( $value ) ) {
			return array_map( [ $this, 'clean_setting' ], $value );
		}

		return is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
	}

	private function import_gutenberg( $post_id, array $layout ) {
		$html = $this->serialize_blocks( $layout['content'] );

		// Checked on the rebuilt markup, i.e. exactly what gets saved.
		$blocks = Validator::assert_allowed_blocks( $html );
		if ( is_wp_error( $blocks ) ) {
			return $blocks;
		}

		$css = Validator::css( $layout['css'] );
		if ( is_wp_error( $css ) ) {
			return $css;
		}

		// Core's content_save_pre (kses) still runs for users without unfiltered_html.
		$result = wp_update_post( wp_slash( [ 'ID' => $post_id, 'post_content' => $html ] ), true );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		update_post_meta( $post_id, 'gutenova_css', wp_slash( $css ) );

		return true;
	}

	/**
	 * Core's serialize_blocks(), except that object `attrs` are kept: core
	 * replaces any non-array `attrs` with [], and arrays would write `{}` as `[]`.
	 */
	private function serialize_blocks( array $blocks ) {
		$html = '';

		foreach ( $blocks as $block ) {
			$content = '';
			$index   = 0;
			foreach ( $block['innerContent'] as $chunk ) {
				$content .= is_string( $chunk ) ? $chunk : $this->serialize_blocks( [ $block['innerBlocks'][ $index++ ] ] );
			}

			$html .= get_comment_delimited_block_content( $block['blockName'], $block['attrs'], $content );
		}

		return $html;
	}
}
