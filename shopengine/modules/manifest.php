<?php

namespace ShopEngine\Modules;

defined('ABSPATH') || exit;

use ShopEngine\Core\Register\Module_List;
use ShopEngine\Utils\Helper;

class Manifest
{
	public function init() {

		add_action('init', [$this, 'manifest_modules'], 0);
	}

	public function manifest_modules() {

		foreach(Module_List::instance()->get_list(true, 'active') as $module) {

			if($module['status'] != 'active') {
				continue;
			}
			if($module['package'] === 'pro-disabled') {
				continue;
			}

			if(isset($module['path'])) {

				$fl = $module['path'] . '/' . $module['slug'] . '.php';

				if(file_exists($fl)) {

					require_once $fl;
				}
			}

			$module['base_class']::instance()->init();

		}

		// Outdated ShopEngine Pro versions use comparison classes that ShopEngine no longer ships
 		if ( !Helper::is_pro_outdated() && !wp_doing_ajax() && !empty($_SERVER['REQUEST_URI']) && strpos(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), 'wp-json/') === false ) {
		    do_action('shopengine/module/comparison-module-pro-support');
 		}
	}
}
