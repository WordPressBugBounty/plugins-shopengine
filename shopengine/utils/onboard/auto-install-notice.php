<?php

namespace ShopEngine\Utils\Onboard;

defined( 'ABSPATH' ) || exit;

class Auto_Install_Notice {

	const DISMISSED_KEY       = 'shopengine_auto_install_notice_dismissed';
	const EMAIL_COLLECTED_KEY = 'wpmet_onboard_email_collected';

	public static function init(): void {
		add_action( 'admin_footer', [ __CLASS__, 'render' ] );
		add_action( 'wp_ajax_shopengine_dismiss_auto_install_notice', [ __CLASS__, 'handle_dismiss' ] );
		add_action( 'wp_ajax_shopengine_confirm_auto_install_notice', [ __CLASS__, 'handle_confirm' ] );
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( get_option( self::DISMISSED_KEY ) ) {
			return;
		}

		if ( \ShopEngine\Utils\Util::get_settings( 'shopengine_user_consent_for_banner', 'true' ) !== 'true' ) {
			return;
		}

		// Only show when ShopEngine was auto-installed by another wpmet plugin.
		$registry       = Auto_Install_Tracker::get_registry();
		$auto_installed = isset( $registry['shopengine/shopengine.php'] );

		if ( ! $auto_installed ) {
			return;
		}

		// Only show on ShopEngine's own admin pages.
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'shopengine' ) === false ) {
			return;
		}

		$nonce        = wp_create_nonce( 'shopengine_auto_install_notice_nonce' );
		$ajax_url     = esc_js( admin_url( 'admin-ajax.php' ) );
		$heading      = esc_js( __( 'Get more from your new plugins 🚀', 'shopengine' ) );
		$desc         = esc_js( __( 'Allow us to use non-sensitive data from your site to improve our products and get personalized performance tips.', 'shopengine' ) );
		$skip_label   = esc_js( __( 'Skip for Now', 'shopengine' ) );
		$confirm_label = esc_js( __( 'Confirm & Apply', 'shopengine' ) );
		$applying     = esc_js( __( 'Applying…', 'shopengine' ) );
		?>
		<style>
			@property --angle {
				syntax: '<angle>';
				initial-value: 162.14deg;
				inherits: false;
			}
			#wpmet-shopengine-notice-skip,
			#wpmet-shopengine-notice-confirm {
				display: flex;
				align-items: center;
				justify-content: center;
				min-height: unset;
				line-height: unset;
				box-sizing: border-box;
				padding: 9px 20px;
				border-radius: 8px;
				flex: none;
				font-size: 13px;
				white-space: nowrap;
				cursor: pointer;
			}
			#wpmet-shopengine-notice-skip {
				background: #fff;
				border: 1px solid #d7d7db;
				color: #000;
				transition: background 250ms ease, border-color 250ms ease, color 250ms ease;
			}
			#wpmet-shopengine-notice-skip:hover {
				background: #dbeafe;
				border: 1px solid #dbeafe;
				color: #000;
			}
			#wpmet-shopengine-notice-confirm {
				position: relative;
				overflow: hidden;
				isolation: isolate;
				--angle: 162.14deg;
				background: linear-gradient(var(--angle), #4371F8 7.19%, #2B58DE 44.76%);
				border: none;
				color: #fff;
			}
			#wpmet-shopengine-notice-confirm::after {
				content: "";
				position: absolute;
				top: -60%;
				right: -10%;
				width: 65%;
				height: 200%;
				background: rgba(255, 255, 255, 0.10);
				border-radius: 60% 0 0 10%;
				transform: rotate(20deg);
				transform-origin: bottom left;
				z-index: -1;
				pointer-events: none;
				transition: top 300ms ease, right 300ms ease, opacity 300ms ease;
			}
			#wpmet-shopengine-notice-confirm:hover::after {
				top: -70%;
				right: 0%;
				opacity: 0.7;
			}
			.ant-notification {
				z-index: 9999999 !important;
			}
		</style>
		<script>
		(function() {
			var NONCE   = '<?php echo esc_js( $nonce ); ?>';
			var AJAXURL = '<?php echo $ajax_url; ?>';
			var dismissed = false;

			function setContentPadding( height ) {
				var el = document.getElementById( 'wpcontent' );
				if ( el ) { el.style.setProperty( 'padding-top', height + 'px', 'important' ); }
			}

			function clearContentPadding() {
				var el = document.getElementById( 'wpcontent' );
				if ( el ) { el.style.removeProperty( 'padding-top' ); }
			}

			function removeNotice() {
				dismissed = true;
				clearContentPadding();
				var el = document.querySelector( '.wpmet-shopengine-auto-install-notice' );
				if ( el ) {
					el.style.opacity = '0';
					el.style.transition = 'opacity 0.3s';
					setTimeout( function() { if ( el.parentNode ) { el.parentNode.removeChild( el ); } }, 320 );
				}
			}

			function doPost( action, cb ) {
				var xhr = new XMLHttpRequest();
				xhr.open( 'POST', AJAXURL );
				xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
				xhr.onload = function() { if ( cb ) { cb(); } };
				xhr.send( 'action=' + encodeURIComponent( action ) + '&nonce=' + encodeURIComponent( NONCE ) );
			}

			function isBlockedPage() {
				var h = window.location.hash;
				var s = window.location.search;
				return h === '#shopengine-modules' || h === '#shopengine-widgets' || h === '#getting-started'
					|| s.indexOf( 'page=shopengine_wpmet_plugins' ) !== -1;
			}

			function injectNotice() {
				if ( dismissed ) { return; }
				if ( isBlockedPage() ) { return; }
				if ( document.querySelector( '.wpmet-shopengine-auto-install-notice' ) ) { return; }

				var div = document.createElement( 'div' );
				div.className = 'wpmet-shopengine-auto-install-notice';
				div.style.cssText = [
					'display:flex', 'align-items:center', 'justify-content:space-between',
					'gap:20px', 'min-height:104px', 'box-sizing:border-box',
					'position:fixed', 'top:32px', 'left:160px', 'right:0',
					'z-index:999999', 'padding:16px 20px',
					'background:#fff', 'border-left:4px solid #E91E8C',
					'box-shadow:0 2px 8px rgba(0,0,0,.15)'
				].join( ';' );

				div.innerHTML =
					'<div style="flex:1 1 auto;">' +
						'<h3 style="margin:0 0 6px;font-family:Inter,sans-serif;font-size:24px;font-weight:700;line-height:24px;letter-spacing:0;"><?php echo $heading; ?></h3>' +
						'<p style="margin:0;color:#50575E;font-family:Inter,sans-serif;font-weight:500;font-size:16px;line-height:24px;letter-spacing:0;"><?php echo $desc; ?></p>' +
					'</div>' +
					'<div style="flex:0 0 auto;display:flex;gap:10px;">' +
						'<button class="button" id="wpmet-shopengine-notice-skip"><?php echo $skip_label; ?></button>' +
						'<button class="button button-primary" id="wpmet-shopengine-notice-confirm"><?php echo $confirm_label; ?></button>' +
					'</div>';

				document.body.appendChild( div );

				var noticeHeight = div.offsetHeight || 104;
				setContentPadding( noticeHeight );

				document.getElementById( 'wpmet-shopengine-notice-skip' ).addEventListener( 'click', function() {
					doPost( 'shopengine_dismiss_auto_install_notice' );
					removeNotice();
				} );

				document.getElementById( 'wpmet-shopengine-notice-confirm' ).addEventListener( 'click', function() {
					var btn = this;
					btn.disabled = true;
					btn.textContent = '<?php echo $applying; ?>';
					doPost( 'shopengine_confirm_auto_install_notice', function() {
						removeNotice();
					} );
				} );
			}

			function tryInject() {
				setTimeout( injectNotice, 300 );
			}

			if ( document.readyState === 'loading' ) {
				document.addEventListener( 'DOMContentLoaded', tryInject );
			} else {
				tryInject();
			}

			window.addEventListener( 'hashchange', function() {
				if ( dismissed ) { return; }
				var existing = document.querySelector( '.wpmet-shopengine-auto-install-notice' );
				if ( existing && existing.parentNode ) { existing.parentNode.removeChild( existing ); }
				clearContentPadding();
				setTimeout( injectNotice, 400 );
			} );

			document.addEventListener( 'visibilitychange', function() {
				if ( document.visibilityState === 'visible' ) { tryInject(); }
			} );

			window.addEventListener( 'pageshow', function( e ) {
				if ( e.persisted ) { tryInject(); }
			} );
		})();
		</script>
		<?php
	}

	public static function handle_dismiss(): void {
		check_ajax_referer( 'shopengine_auto_install_notice_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}
		update_option( self::DISMISSED_KEY, true, false );
		wp_send_json_success();
	}

	public static function handle_confirm(): void {
		check_ajax_referer( 'shopengine_auto_install_notice_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error();
		}

		$stored_email = get_option( 'wpmet_onboard_collected_email' );
		$email        = $stored_email
			? sanitize_email( $stored_email )
			: sanitize_email( get_option( 'admin_email' ) );

		\ShopEngine\Core\Onboard\Plugin_Data_Sender::instance()->sendEmailSubscribeData( 'plugin-subscribe', [
			'email' => $email,
			'slug'  => 'shopengine',
		] );

		// Also send for auto-installed plugins that don't have their own notice banner.
		$registry = Auto_Install_Tracker::get_registry();
		$slug_map = [
			'elementskit-lite/elementskit-lite.php'           => 'elementskit-lite',
			'emailkit/EmailKit.php'                           => 'emailkit',
			'gutenkit-blocks-addon/gutenkit-blocks-addon.php' => 'gutenkit-blocks-addon',
			'getgenie/getgenie.php'                           => 'getgenie',
			'popup-builder-block/popup-builder-block.php'     => 'popupkit',
		];
		foreach ( array_keys( $registry ) as $plugin_file ) {
			if ( isset( $slug_map[ $plugin_file ] ) ) {
				\ShopEngine\Core\Onboard\Plugin_Data_Sender::instance()->sendEmailSubscribeData( 'plugin-subscribe', [
					'email' => $email,
					'slug'  => $slug_map[ $plugin_file ],
				] );
			}
		}

		update_option( self::EMAIL_COLLECTED_KEY, true, false );
		update_option( self::DISMISSED_KEY, true, false );
		wp_send_json_success();
	}
}
