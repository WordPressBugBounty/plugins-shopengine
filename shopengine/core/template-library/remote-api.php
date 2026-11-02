<?php

namespace ShopEngine\Core\Template_Library;

defined( 'ABSPATH' ) || exit;

/**
 * HTTP client for the remote ShopEngine template library (shopengine-layout-api).
 *
 * - The endpoint is a class constant with no override (constant or filter), so
 *   no other code can redirect requests (and the headers extensions add to
 *   them) to another host.
 * - https only, wp_safe_remote_get, no redirects, response size cap, JSON depth cap.
 * - The validated listing is cached, and a last-known-good copy is kept for when
 *   the API is unreachable; failures back off for a few minutes.
 */
class Remote_Api {

	const API_URL       = 'https://api.wpmet.com/shopengine/wp-json/se-layout/v1/layout-manager-api';
	const ALLOWED_HOSTS = [ 'api.wpmet.com' ];

	const CACHE_PREFIX = 'shopengine_tpl_';
	const LISTING_TTL  = 6 * HOUR_IN_SECONDS;
	const LAYOUT_TTL   = HOUR_IN_SECONDS;
	const BACKOFF_TTL  = 10 * MINUTE_IN_SECONDS;
	const MAX_BYTES    = 5 * MB_IN_BYTES;

	const OPTION_LKG  = 'shopengine_tpl_listing_lkg';
	const BACKOFF_KEY = 'shopengine_tpl_backoff';

	public static function get_api_url() {
		return self::API_URL;
	}

	/**
	 * Hosts the library (and its media) may be served from.
	 *
	 * @return string[]
	 */
	public static function allowed_hosts() {
		return self::ALLOWED_HOSTS;
	}

	/**
	 * Validated listing items (see Validator::listing_item()).
	 *
	 * @param bool $force Bypass the cache.
	 * @return array|\WP_Error
	 */
	public static function get_listing( $force = false ) {
		$cache_key = self::CACHE_PREFIX . 'list_' . md5( self::get_api_url() . '|' . Access::get_package() );

		if ( ! $force ) {
			$cached = get_transient( $cache_key );
			if ( is_array( $cached ) ) {
				return $cached;
			}

			// The API failed recently: serve the last good copy without waiting on it again.
			if ( get_transient( self::BACKOFF_KEY ) ) {
				return self::last_known_good( new \WP_Error( 'shopengine_tpl_unavailable', esc_html__( 'The template library is temporarily unavailable.', 'shopengine' ), [ 'status' => 503 ] ) );
			}
		}

		$body = self::get_json( add_query_arg( [
			'action'         => 'get_layout_list',
			'plugin_package' => Access::get_package(),
		], self::get_api_url() ) );

		if ( is_wp_error( $body ) ) {
			set_transient( self::BACKOFF_KEY, 1, self::BACKOFF_TTL );
			return self::last_known_good( $body );
		}

		$items = [];
		foreach ( ( isset( $body['layouts'] ) && is_array( $body['layouts'] ) ? $body['layouts'] : [] ) as $raw ) {
			$item = Validator::listing_item( $raw );
			if ( $item ) {
				$items[] = $item;
			}
		}

		delete_transient( self::BACKOFF_KEY );
		set_transient( $cache_key, $items, self::LISTING_TTL );
		update_option( self::OPTION_LKG, $items, false );

		return $items;
	}

	private static function last_known_good( \WP_Error $error ) {
		$lkg = get_option( self::OPTION_LKG );

		return is_array( $lkg ) && ! empty( $lkg ) ? $lkg : $error;
	}

	/**
	 * Raw (unvalidated) layout payload; callers must run Validator::layout().
	 *
	 * @param int $layout_id Remote layout ID.
	 * @return array|\WP_Error
	 */
	public static function get_layout( $layout_id ) {
		$layout_id = absint( $layout_id );
		if ( ! $layout_id ) {
			return new \WP_Error( 'shopengine_tpl_id', esc_html__( 'Invalid template ID.', 'shopengine' ), [ 'status' => 400 ] );
		}

		$url = add_query_arg( [
			'action'         => 'get_layout_data',
			'layout_id'      => $layout_id,
			'plugin_package' => Access::get_package(),
		], self::get_api_url() );

		$cache_key = self::CACHE_PREFIX . 'layout_' . md5( $url . '|' . implode( '|', self::get_request_headers() ) );
		$cached    = get_transient( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$body = self::get_json( $url, false );
		if ( is_wp_error( $body ) ) {
			return $body;
		}

		$body = self::layout_from_json( $body );

		set_transient( $cache_key, $body, self::LAYOUT_TTL );

		return $body;
	}

	/**
	 * Drop every cached listing/layout (the last-known-good copy is kept).
	 */
	public static function flush_cache() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
			$wpdb->esc_like( '_transient_' . self::CACHE_PREFIX ) . '%',
			$wpdb->esc_like( '_transient_timeout_' . self::CACHE_PREFIX ) . '%'
		) );

		if ( wp_using_ext_object_cache() && function_exists( 'wp_cache_flush_group' ) ) {
			wp_cache_flush_group( 'transient' );
		}
	}

	/**
	 * @return array|\WP_Error Decoded JSON body.
	 */
	/**
	 * The layout body as arrays, like every other response, except the `attrs`
	 * of Gutenberg blocks: those stay objects so an empty `{}` is saved back
	 * as `{}` by serialize_blocks(), not as `[]`.
	 *
	 * @param object $body Decoded get_layout_data response.
	 * @return array
	 */
	private static function layout_from_json( $body ) {
		$content = isset( $body->content ) ? $body->content : null;
		$out     = json_decode( wp_json_encode( $body ), true );

		if ( isset( $body->metadata->editor ) && 'gutenberg' === $body->metadata->editor && is_array( $content ) ) {
			$out['content'] = self::blocks_from_json( $content );
		}

		return $out;
	}

	private static function blocks_from_json( array $blocks ) {
		foreach ( $blocks as $i => $block ) {
			if ( ! is_object( $block ) ) {
				continue; // Refused by Validator::layout().
			}

			$block = get_object_vars( $block );

			if ( isset( $block['innerBlocks'] ) && is_array( $block['innerBlocks'] ) ) {
				$block['innerBlocks'] = self::blocks_from_json( $block['innerBlocks'] );
			}

			// No attributes: serialize_blocks() then writes none, as parse_blocks() read them.
			if ( isset( $block['attrs'] ) && is_object( $block['attrs'] ) && ! get_object_vars( $block['attrs'] ) ) {
				$block['attrs'] = [];
			}

			$blocks[ $i ] = $block;
		}

		return $blocks;
	}

	private static function get_json( $url, $assoc = true ) {
		if ( ! self::is_allowed_url( $url ) ) {
			return new \WP_Error( 'shopengine_tpl_host', esc_html__( 'Template library host not allowed.', 'shopengine' ), [ 'status' => 500 ] );
		}

		$response = wp_safe_remote_get( $url, [
			'timeout'             => 15,
			'redirection'         => 0,
			'limit_response_size' => self::MAX_BYTES,
			'headers'             => array_merge( [ 'Accept' => 'application/json' ], self::get_request_headers() ),
		] );

		if ( is_wp_error( $response ) ) {
			self::debug_log( 'request failed: ' . $response->get_error_message() );
			return new \WP_Error( 'shopengine_tpl_http', esc_html__( 'Could not reach the template library.', 'shopengine' ), [ 'status' => 502 ] );
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$raw  = (string) wp_remote_retrieve_body( $response );

		if ( 200 !== $code ) {
			$decoded = json_decode( $raw, true, 8 );
			$message = is_array( $decoded ) && isset( $decoded['message'] ) && is_string( $decoded['message'] )
				? mb_substr( sanitize_text_field( $decoded['message'] ), 0, 200 )
				: esc_html__( 'The template library returned an error.', 'shopengine' );

			self::debug_log( "HTTP {$code} for {$url}" );

			return new \WP_Error( 403 === $code ? 'shopengine_tpl_denied' : 'shopengine_tpl_status', $message, [ 'status' => 403 === $code ? 403 : 502 ] );
		}

		if ( strlen( $raw ) >= self::MAX_BYTES ) {
			return new \WP_Error( 'shopengine_tpl_too_large', esc_html__( 'The template is too large.', 'shopengine' ), [ 'status' => 502 ] );
		}

		$body = json_decode( $raw, $assoc, 64 );
		if ( ! ( $assoc ? is_array( $body ) : is_object( $body ) ) ) {
			return new \WP_Error( 'shopengine_tpl_json', esc_html__( 'The template library returned an invalid response.', 'shopengine' ), [ 'status' => 502 ] );
		}

		return $body;
	}

	private static function is_allowed_url( $url ) {
		$parts = wp_parse_url( $url );

		if ( ! isset( $parts['scheme'], $parts['host'] ) || ! empty( $parts['user'] ) || ! empty( $parts['pass'] ) ) {
			return false;
		}

		return 'https' === $parts['scheme'] && in_array( strtolower( $parts['host'] ), self::allowed_hosts(), true );
	}

	/**
	 * Extra request headers added by extensions. They are only ever sent to an
	 * allowed host (get_json() refuses anything else before a request is made).
	 *
	 * @return array
	 */
	private static function get_request_headers() {
		/**
		 * Extra headers for requests to the template library API.
		 *
		 * @param array $headers Header name => value.
		 */
		$headers = apply_filters( 'shopengine/template_library/request_headers', [] );

		if ( ! is_array( $headers ) ) {
			return [];
		}

		return array_filter( $headers, function ( $value, $name ) {
			return is_string( $name ) && is_string( $value ) && '' !== $value;
		}, ARRAY_FILTER_USE_BOTH );
	}

	private static function debug_log( $message ) {
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( '[ShopEngine template library] ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
		}
	}
}
