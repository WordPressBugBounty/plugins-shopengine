<?php

namespace ShopEngine\Core\Template_Library;

defined( 'ABSPATH' ) || exit;

/**
 * Which library designs this site may import. The React `locked` flag is
 * display-only; imports are checked here (and by the API).
 *
 * ShopEngine itself only knows the free package. An extension that unlocks
 * more designs says so through the filters below.
 */
class Access {

	const PACKAGE_FREE = 'free';
	const PACKAGE_PRO  = 'pro';

	// Why a design is locked; see lock_reason().
	const LOCK_NONE    = '';
	const LOCK_PRO     = 'pro';     // ShopEngine Pro is not active.
	const LOCK_LICENCE = 'licence'; // Pro is active, but its licence is not.

	const LOCK_REASONS = [ self::LOCK_NONE, self::LOCK_PRO, self::LOCK_LICENCE ];

	public static function get_package() {
		/**
		 * Package the library is requested for.
		 *
		 * @param string $package One of Validator::PACKAGES.
		 */
		$package = apply_filters( 'shopengine/template_library/package', self::PACKAGE_FREE );

		return in_array( $package, Validator::PACKAGES, true ) ? $package : self::PACKAGE_FREE;
	}

	/**
	 * Why a design of the given package is locked, or LOCK_NONE.
	 */
	public static function lock_reason( $package ) {
		/**
		 * Whether a design of the given package may be imported on this site.
		 *
		 * @param bool   $can_import
		 * @param string $package    Package of the design.
		 */
		$can_import = (bool) apply_filters( 'shopengine/template_library/can_import', self::PACKAGE_PRO !== $package, $package );
		$reason     = $can_import ? self::LOCK_NONE : self::LOCK_PRO;

		/**
		 * Why a design is locked. An extension that knows more than the import
		 * decision (for example, that Pro is active but its licence is not)
		 * returns one of the Access::LOCK_* constants.
		 *
		 * @param string $reason  Access::LOCK_NONE when the design may be imported.
		 * @param string $package Package of the design.
		 */
		$filtered = apply_filters( 'shopengine/template_library/lock_reason', $reason, $package );

		return in_array( $filtered, self::LOCK_REASONS, true ) ? $filtered : $reason;
	}

	public static function can_import( $package ) {
		return self::LOCK_NONE === self::lock_reason( $package );
	}

	public static function denied_error( $package = self::PACKAGE_PRO ) {
		if ( self::LOCK_LICENCE === self::lock_reason( $package ) ) {
			return new \WP_Error(
				'shopengine_tpl_requires_licence',
				esc_html__( 'This template needs an active ShopEngine Pro licence. Activate your licence, then import it again.', 'shopengine' ),
				[ 'status' => 402 ]
			);
		}

		return new \WP_Error(
			'shopengine_tpl_requires_pro',
			esc_html__( 'This template requires ShopEngine Pro.', 'shopengine' ),
			[ 'status' => 402 ]
		);
	}
}
