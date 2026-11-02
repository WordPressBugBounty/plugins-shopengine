<?php

namespace ShopEngine\Core\Template_Library;

use ShopEngine\Core\Builders\Templates;

defined( 'ABSPATH' ) || exit;

/**
 * Local REST proxy for the template library. The browser never talks to the
 * remote API: it only receives server-validated items (IDs, text, https URLs).
 *
 *   GET  shopengine/v1/library/list?type=&editor=
 *   POST shopengine/v1/library/refresh
 */
class Api {

	const NAMESPACE        = 'shopengine/v1';
	const REFRESH_THROTTLE = 'shopengine_tpl_refresh_lock';

	public function __construct() {
		add_action( 'rest_api_init', [ $this, 'register_routes' ] );
		// New plugin version: re-validate the library against the new code.
		add_action( 'upgrader_process_complete', [ Remote_Api::class, 'flush_cache' ] );
	}

	public function register_routes() {
		register_rest_route( self::NAMESPACE, '/library/list', [
			'methods'             => \WP_REST_Server::READABLE,
			'callback'            => [ $this, 'get_list' ],
			'permission_callback' => function () {
				return current_user_can( 'install_plugins' );
			},
			'args'                => [
				'type'   => [
					'type'              => 'string',
					'required'          => true,
					'validate_callback' => function ( $value ) {
						return is_string( $value ) && array_key_exists( $value, Templates::get_template_types() );
					},
				],
				'editor' => [
					'type'     => 'string',
					'required' => true,
					'enum'     => Validator::EDITORS,
				],
			],
		] );

		register_rest_route( self::NAMESPACE, '/library/refresh', [
			'methods'             => \WP_REST_Server::CREATABLE,
			'callback'            => [ $this, 'refresh' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_options' );
			},
		] );
	}

	public function get_list( \WP_REST_Request $request ) {
		$items = Remote_Api::get_listing();

		if ( is_wp_error( $items ) ) {
			return $items;
		}

		$type   = $request->get_param( 'type' );
		$editor = $request->get_param( 'editor' );
		$out    = [];

		foreach ( $items as $item ) {
			if ( $item['template_type'] !== $type || $item['editor'] !== $editor ) {
				continue;
			}

			$lock_reason = Access::lock_reason( $item['package'] );

			$out[] = [
				'id'          => $item['id'],
				'title'       => $item['title'],
				'package'     => $item['package'],
				'editor'      => $item['editor'],
				'thumbnail'   => $item['thumbnail'],
				'demo_url'    => $item['demo_url'],
				'locked'      => Access::LOCK_NONE !== $lock_reason,
				'lock_reason' => $lock_reason,
			];
		}

		return rest_ensure_response( [ 'items' => $out ] );
	}

	public function refresh() {
		if ( get_transient( self::REFRESH_THROTTLE ) ) {
			return new \WP_Error( 'shopengine_tpl_throttled', esc_html__( 'The library was refreshed a moment ago. Please wait a minute.', 'shopengine' ), [ 'status' => 429 ] );
		}
		set_transient( self::REFRESH_THROTTLE, 1, MINUTE_IN_SECONDS );

		Remote_Api::flush_cache();
		delete_transient( Remote_Api::BACKOFF_KEY );

		$items = Remote_Api::get_listing( true );

		if ( is_wp_error( $items ) ) {
			return $items;
		}

		return rest_ensure_response( [ 'refreshed' => true, 'count' => count( $items ) ] );
	}
}
