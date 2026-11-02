<?php

namespace ShopEngine\Core\Template_Library;

defined( 'ABSPATH' ) || exit;

/**
 * Read-only Elementor source for the ShopEngine template library.
 *
 * Extends Elementor's Source_Base so remote content goes through the very same
 * pipeline Elementor uses for its own library (fresh element IDs + `on_import`
 * callbacks), instead of the old base64 "file import" that required enabling
 * unfiltered uploads.
 *
 * Must only be autoloaded when Elementor is active (it extends an Elementor class).
 */
class Source extends \Elementor\TemplateLibrary\Source_Base {

	const SOURCE_ID = 'shopengine-template-library';

	public static function is_elementor_ready() {
		return class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance );
	}

	public static function instance() {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	public function get_id() {
		return self::SOURCE_ID;
	}

	public function get_title() {
		return esc_html__( 'ShopEngine Templates', 'shopengine' );
	}

	public function register_data() {}

	public function get_items( $args = [] ) {
		return [];
	}

	public function get_item( $template_id ) {
		return [];
	}

	public function get_data( array $args ) {
		return [ 'content' => [], 'type' => 'page', 'page_settings' => [] ];
	}

	/**
	 * Run Elementor's import pipeline over a raw element tree.
	 */
	public function prepare_content( array $content ) {
		if ( empty( $content ) || ! self::is_elementor_ready() ) {
			return $content;
		}

		$content = $this->replace_elements_ids( $content );
		$content = $this->process_export_import_content( $content, 'on_import' );

		return is_array( $content ) ? $content : [];
	}

	/**
	 * Let the created document apply its own element defaults and migrations.
	 */
	public function normalize_document_content( $post_id, array $content ) {
		if ( empty( $content ) || ! self::is_elementor_ready() ) {
			return $content;
		}

		$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );

		if ( ! $document ) {
			return $content;
		}

		try {
			$normalized = $document->get_elements_raw_data( $content );
		} catch ( \Exception $exception ) {
			return $content;
		}

		return ( is_array( $normalized ) && ! empty( $normalized ) ) ? $normalized : $content;
	}

	/* --- Read-only source --- */

	public function save_item( $template_data ) {
		return $this->read_only_error();
	}

	public function update_item( $new_data ) {
		return $this->read_only_error();
	}

	public function delete_template( $template_id ) {
		return $this->read_only_error();
	}

	public function export_template( $template_id ) {
		return $this->read_only_error();
	}

	private function read_only_error() {
		return new \WP_Error( 'shopengine_tpl_read_only', esc_html__( 'The ShopEngine template library is read-only.', 'shopengine' ), [ 'status' => 403 ] );
	}
}
