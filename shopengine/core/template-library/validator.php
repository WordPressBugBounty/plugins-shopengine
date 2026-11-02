<?php

namespace ShopEngine\Core\Template_Library;

use ShopEngine\Core\Builders\Templates;

defined( 'ABSPATH' ) || exit;

/**
 * Schema validation for everything the remote library returns. Listing items
 * that fail validation are dropped; layout payloads that fail are refused.
 */
class Validator {

	const EDITORS  = [ 'elementor', 'gutenberg' ];
	const PACKAGES = [ 'free', 'pro' ];

	/** Hosts a "Live Preview" link may point to (plus the API hosts). */
	const DEMO_HOSTS = [ 'wpmet.com', 'demo.xpeedstudio.com', 'api.wpmet.com' ];

	/** Extra hosts CSS may @import / url() from (fonts). */
	const CSS_HOSTS = [ 'fonts.googleapis.com', 'fonts.gstatic.com' ];

	/** Block name prefixes allowed in Gutenberg layouts. */
	const BLOCK_PREFIXES = [ 'core/', 'shopengine/', 'gutenkit/', 'gutenova/' ];

	const MAX_CSS_BYTES = MB_IN_BYTES;

	/**
	 * Elementor settings that write raw HTML attributes or CSS (Pro's custom
	 * attributes / custom CSS, and a link's custom attributes). Dropped for
	 * users without `unfiltered_html`.
	 */
	const ELEMENTOR_UNSAFE_SETTINGS = [ '_attributes', 'custom_attributes', 'custom_css' ];

	/**
	 * Validate one listing item and map it to the shape the admin UI uses.
	 *
	 * @param mixed $raw Remote item.
	 * @return array|null Null when the item is invalid.
	 */
	public static function listing_item( $raw ) {
		if ( ! is_array( $raw ) ) {
			return null;
		}

		$id            = isset( $raw['ID'] ) ? absint( $raw['ID'] ) : 0;
		$template_type = isset( $raw['template_type'] ) && is_string( $raw['template_type'] ) ? $raw['template_type'] : '';
		$editor        = isset( $raw['editor'] ) && is_string( $raw['editor'] ) ? $raw['editor'] : '';
		$package       = isset( $raw['package'] ) && in_array( $raw['package'], self::PACKAGES, true ) ? $raw['package'] : 'free';

		if ( ! $id || ! self::is_template_type( $template_type ) || ! in_array( $editor, self::EDITORS, true ) ) {
			return null;
		}

		return [
			'id'            => $id,
			'title'         => self::text( isset( $raw['title'] ) ? $raw['title'] : '', 120 ),
			'template_type' => $template_type,
			'editor'        => $editor,
			'package'       => $package,
			'thumbnail'     => self::library_url( isset( $raw['thumbnail'] ) ? $raw['thumbnail'] : '' ),
			'demo_url'      => self::demo_url( isset( $raw['live_demo_url'] ) ? $raw['live_demo_url'] : '' ),
		];
	}

	/**
	 * Validate a get_layout_data payload against what was requested.
	 *
	 * @return array|\WP_Error { content, css, settings, package, title } where
	 *                         content is the Elementor element tree or the
	 *                         Gutenberg parse_blocks() tree.
	 */
	public static function layout( $body, $layout_id, $template_type, $editor ) {
		$invalid = new \WP_Error( 'shopengine_tpl_invalid', esc_html__( 'The template library returned an invalid template.', 'shopengine' ), [ 'status' => 502 ] );

		if ( ! is_array( $body ) || ! isset( $body['metadata'] ) || ! is_array( $body['metadata'] ) ) {
			return $invalid;
		}

		$meta = $body['metadata'];

		if ( ! isset( $meta['id'] ) || absint( $meta['id'] ) !== absint( $layout_id ) ) {
			return $invalid;
		}

		if ( ! isset( $meta['template_type'], $meta['editor'] ) || $meta['template_type'] !== $template_type || $meta['editor'] !== $editor ) {
			return new \WP_Error( 'shopengine_tpl_mismatch', esc_html__( 'The selected design does not match this template type or editor.', 'shopengine' ), [ 'status' => 400 ] );
		}

		if ( ! isset( $body['content'] ) || ! is_array( $body['content'] ) || empty( $body['content'] ) ) {
			return $invalid;
		}

		$out = [
			'content'  => $body['content'],
			'css'      => '',
			'settings' => isset( $body['settings'] ) && is_array( $body['settings'] ) ? $body['settings'] : [],
			'package'  => isset( $meta['package'] ) && in_array( $meta['package'], self::PACKAGES, true ) ? $meta['package'] : 'free',
			'title'    => self::text( isset( $meta['title'] ) ? $meta['title'] : '', 120 ),
		];

		if ( 'gutenberg' === $editor ) {
			if ( ! self::is_block_list( $body['content'] ) ) {
				return $invalid;
			}
			$extras = isset( $body['shopengine_content'] ) && is_array( $body['shopengine_content'] ) ? $body['shopengine_content'] : [];
			$css    = isset( $extras['css'] ) ? $extras['css'] : '';
			if ( ! is_string( $css ) || strlen( $css ) > self::MAX_CSS_BYTES ) {
				return $invalid;
			}
			$out['css'] = $css;
		}

		return $out;
	}

	/**
	 * Whether $blocks has the shape parse_blocks() produces (attrs may be
	 * objects), so the markup can be rebuilt from it without errors.
	 */
	private static function is_block_list( $blocks ) {
		if ( ! is_array( $blocks ) ) {
			return false;
		}

		foreach ( $blocks as $block ) {
			if (
				! is_array( $block )
				|| ! array_key_exists( 'blockName', $block ) || ! ( null === $block['blockName'] || is_string( $block['blockName'] ) )
				|| ! isset( $block['attrs'], $block['innerBlocks'], $block['innerHTML'], $block['innerContent'] )
				|| ! ( is_array( $block['attrs'] ) || $block['attrs'] instanceof \stdClass )
				|| ! is_string( $block['innerHTML'] ) || ! is_array( $block['innerContent'] )
				|| ! self::is_block_list( $block['innerBlocks'] )
			) {
				return false;
			}

			// Each null chunk in innerContent is where the next inner block goes.
			$slots = 0;
			foreach ( $block['innerContent'] as $chunk ) {
				if ( null === $chunk ) {
					$slots++;
				} elseif ( ! is_string( $chunk ) ) {
					return false;
				}
			}
			if ( count( $block['innerBlocks'] ) !== $slots ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * RTL library designs carry their text escaped twice, so after JSON decoding
	 * a string still reads `\uXXXX\uXXXX` and Elementor would show it as is.
	 * Resolve those escapes to the text they stand for.
	 *
	 * Runs before the sanitizers. ASCII escapes are left alone: this text never
	 * needs them, and an escaped `<` must not turn into markup.
	 */
	public static function decode_unicode_escapes( array $data ) {
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$data[ $key ] = self::decode_unicode_escapes( $value );
			} elseif ( is_string( $value ) && false !== strpos( $value, '\u' ) ) {
				$data[ $key ] = preg_replace_callback(
					'/(?:\\\\u(?!00[0-7])[0-9a-fA-F]{4})++/',
					function ( $m ) {
						$text = json_decode( '"' . $m[0] . '"' );

						return is_string( $text ) ? $text : $m[0];
					},
					$value
				);
			}
		}

		return $data;
	}

	/**
	 * Neutralize media (image/background/SVG-icon controls: arrays with `url`
	 * and `id`) that points anywhere but the library host. Elementor's
	 * `on_import` would otherwise download those URLs into the media library.
	 */
	public static function neutralize_foreign_media( array $data ) {
		foreach ( $data as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			if ( array_key_exists( 'url', $value ) && array_key_exists( 'id', $value ) && is_string( $value['url'] ) && '' !== $value['url'] ) {
				if ( '' === self::library_url( $value['url'] ) ) {
					$value['url'] = '';
					$value['id']  = '';
				}
			}

			$data[ $key ] = self::neutralize_foreign_media( $value );
		}

		return $data;
	}

	/**
	 * For users without `unfiltered_html`: make an Elementor tree as safe as if
	 * they had built it themselves. In every element's settings (any widget,
	 * any depth), HTML goes through wp_kses_post(), link URLs lose script
	 * protocols, and free-form attribute / CSS settings are dropped.
	 *
	 * Sanitizing (rather than pattern-matching for "bad" markup) leaves nothing
	 * to slip past a regex.
	 *
	 * @return array
	 */
	public static function sanitize_elementor_markup( array $elements ) {
		if ( current_user_can( 'unfiltered_html' ) ) {
			return $elements;
		}

		foreach ( $elements as $index => $element ) {
			if ( ! is_array( $element ) ) {
				continue;
			}

			if ( isset( $element['settings'] ) && is_array( $element['settings'] ) ) {
				$element['settings'] = self::sanitize_elementor_settings( $element['settings'] );
			}

			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$element['elements'] = self::sanitize_elementor_markup( $element['elements'] );
			}

			$elements[ $index ] = $element;
		}

		return $elements;
	}

	private static function sanitize_elementor_settings( array $settings ) {
		foreach ( $settings as $key => $value ) {
			if ( is_string( $key ) && in_array( $key, self::ELEMENTOR_UNSAFE_SETTINGS, true ) ) {
				unset( $settings[ $key ] );
				continue;
			}

			if ( is_array( $value ) ) {
				if ( isset( $value['url'] ) && is_string( $value['url'] ) ) {
					$value['url'] = wp_kses_bad_protocol( $value['url'], wp_allowed_protocols() );
				}
				$settings[ $key ] = self::sanitize_elementor_settings( $value );
			} elseif ( is_string( $value ) && false !== strpos( $value, '<' ) ) {
				$settings[ $key ] = wp_kses_post( $value );
			}
		}

		return $settings;
	}

	/**
	 * Every block must be core / ShopEngine / GutenKit / Gutenova.
	 *
	 * @return true|\WP_Error
	 */
	public static function assert_allowed_blocks( $html ) {
		$check = function ( array $blocks ) use ( &$check ) {
			foreach ( $blocks as $block ) {
				$name = isset( $block['blockName'] ) ? $block['blockName'] : null;
				if ( null !== $name ) {
					$ok = false;
					foreach ( self::BLOCK_PREFIXES as $prefix ) {
						if ( 0 === strpos( $name, $prefix ) ) {
							$ok = true;
							break;
						}
					}
					if ( ! $ok ) {
						return $name;
					}
				}
				if ( ! empty( $block['innerBlocks'] ) ) {
					$bad = $check( $block['innerBlocks'] );
					if ( $bad ) {
						return $bad;
					}
				}
			}
			return false;
		};

		$bad = $check( parse_blocks( $html ) );

		if ( $bad ) {
			return new \WP_Error( 'shopengine_tpl_block', esc_html__( 'The template contains an unsupported block.', 'shopengine' ), [ 'status' => 422 ] );
		}

		return true;
	}

	/**
	 * Sanitize layout CSS: strip tags, refuse script-capable constructs, and
	 * allow @import / url() only to the library and font hosts.
	 *
	 * @return string|\WP_Error
	 */
	public static function css( $css ) {
		$css    = wp_strip_all_tags( (string) $css );
		$unsafe = new \WP_Error( 'shopengine_tpl_css', esc_html__( 'The template contains unsafe CSS.', 'shopengine' ), [ 'status' => 422 ] );

		// Checked on the decoded form (escapes resolved, comments removed), so
		// `\75 rl(`, `java\script:` or `@\69mport` can't hide a construct.
		$plain = self::css_plain( $css );

		// @import is only allowed as @import url('https://allowed-host/...');
		// src() is the url() alternative that takes a string.
		if ( preg_match( '/<\/?style|expression\s*\(|javascript\s*:|vbscript\s*:|behaviou?r\s*:|-moz-binding|@import\s+(?!url\()|(?<![\w-])src\s*\(/i', $plain ) ) {
			return $unsafe;
		}

		$css = preg_replace_callback(
			'/url\(\s*([\'"]?)(.*?)\1\s*\)/i',
			function ( $m ) {
				$url = trim( $m[2] );
				if ( '' === $url || 0 === strpos( $url, '#' ) ) {
					return $m[0];
				}

				return self::css_url_allowed( $url ) ? 'url("' . str_replace( [ '"', '\\', "\n", "\r" ], '', $url ) . '")' : 'url("")';
			},
			$css
		);

		// What the rewrite above didn't see (url() spelled with escapes, plain
		// strings in image-set()) must already point to an allowed host.
		$plain = self::css_plain( $css );
		$urls  = [];

		if ( preg_match_all( '/url\(\s*([\'"]?)(.*?)\1\s*\)/i', $plain, $matches ) ) {
			$urls = $matches[2];
		}
		if ( preg_match_all( '/image-set\s*\(([^;{}]*)/i', $plain, $sets ) ) {
			foreach ( $sets[1] as $set ) {
				if ( preg_match_all( '/([\'"])(.*?)\1/', $set, $strings ) ) {
					$urls = array_merge( $urls, $strings[2] );
				}
			}
		}

		foreach ( $urls as $url ) {
			$url = trim( $url );
			if ( '' !== $url && 0 !== strpos( $url, '#' ) && ! self::css_url_allowed( $url ) ) {
				return $unsafe;
			}
		}

		return $css;
	}

	private static function css_url_allowed( $url ) {
		$host   = strtolower( (string) wp_parse_url( $url, PHP_URL_HOST ) );
		$scheme = strtolower( (string) wp_parse_url( $url, PHP_URL_SCHEME ) );

		return 'https' === $scheme && in_array( $host, array_merge( Remote_Api::allowed_hosts(), self::CSS_HOSTS ), true );
	}

	/**
	 * CSS as a browser tokenizes it, for checking only: comments removed and
	 * escapes (`\75`, `\u`, `\` + newline) resolved. Non-ASCII code points
	 * become U+FFFD, since only ASCII keywords are looked for.
	 */
	private static function css_plain( $css ) {
		$css = preg_replace( '#/\*.*?(?:\*/|$)#s', '', $css );

		return preg_replace_callback(
			'/\\\\(?:([0-9a-fA-F]{1,6})[ \t\n\r\f]?|(.))/s',
			function ( $m ) {
				if ( isset( $m[1] ) && '' !== $m[1] ) {
					$code = hexdec( $m[1] );
					return ( $code > 0 && $code < 128 ) ? chr( $code ) : "\xEF\xBF\xBD";
				}

				return "\n" === $m[2] || "\r" === $m[2] || "\f" === $m[2] ? '' : $m[2];
			},
			$css
		);
	}

	/* ------------------------------------------------------------------ */

	private static function is_template_type( $type ) {
		return '' !== $type && array_key_exists( $type, Templates::get_template_types() );
	}

	private static function text( $value, $max ) {
		return is_string( $value ) ? mb_substr( sanitize_text_field( $value ), 0, $max ) : '';
	}

	/**
	 * URL on the library host (https only), else "".
	 */
	public static function library_url( $url ) {
		return self::url_on_hosts( $url, Remote_Api::allowed_hosts() );
	}

	private static function demo_url( $url ) {
		return self::url_on_hosts( $url, array_merge( self::DEMO_HOSTS, Remote_Api::allowed_hosts() ), true );
	}

	private static function url_on_hosts( $url, array $hosts, $allow_subdomains = false ) {
		if ( ! is_string( $url ) || '' === $url ) {
			return '';
		}

		$url   = esc_url_raw( $url, [ 'https', 'http' ] );
		$parts = wp_parse_url( $url );

		if ( ! $url || ! isset( $parts['scheme'], $parts['host'] ) || ! empty( $parts['user'] ) ) {
			return '';
		}

		$host = strtolower( $parts['host'] );

		if ( 'https' !== $parts['scheme'] ) {
			return '';
		}

		foreach ( $hosts as $allowed ) {
			if ( $host === $allowed || ( $allow_subdomains && substr( $host, -strlen( '.' . $allowed ) ) === '.' . $allowed ) ) {
				return $url;
			}
		}

		return '';
	}
}
