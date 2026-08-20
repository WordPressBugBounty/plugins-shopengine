<?php

namespace ShopEngine\Core\Export_Import;

use ShopEngine\Core\Builders\Action;

class Import {

	public function init() {

		add_action('import_start', [$this, 'rum_importer']);
	}

	/**
	 * Option names this importer is allowed to write.
	 *
	 * ShopEngine's exporter (see Export::add_options_in_export_file) only ever
	 * writes shopengine_activated_templates, so that single option is all we
	 * restore. Anything else in the uploaded file is ignored.
	 *
	 * @return string[]
	 */
	private function get_allowed_options() {

		$allowed = [Action::ACTIVATED_TEMPLATES];

		return (array) apply_filters('shopengine/export_import/allowed_import_options', $allowed);
	}

	public function rum_importer() {

		// Writing options is an administrator-level operation. The WordPress
		// importer flow that fires import_start is reachable by any role holding
		// the core 'import' capability (e.g. the WooCommerce shop_manager role,
		// which lacks manage_options), so gate the option write on manage_options
		// to prevent privilege escalation via a crafted import file.
		if(!current_user_can('manage_options')) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing  -- import_start runs inside the nonce-verified WordPress importer flow
		if(empty($_POST['import_id'])) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing  -- import_start runs inside the nonce-verified WordPress importer flow
		$im_id = intval($_POST['import_id']);
		$file  = get_attached_file($im_id);

		if(!$file || !is_readable($file)) {
			return;
		}

		$dom     = new \DOMDocument;
		$success = $dom->loadXML(file_get_contents($file));

		if(!($success || isset($dom->doctype))) {

			return;
		}

		$xml = simplexml_import_dom($dom);
		unset($dom);

		if(!$xml) {

			return;
		}

		$options = $xml->xpath('/rss/channel/wp_options/wp_option');

		if(empty($options)) {
			return;
		}

		$allowed = $this->get_allowed_options();

		foreach($options as $option) {

			$nm = trim((string)$option->name);
			$vl = trim((string)$option->val);

			// Only restore ShopEngine's own allowlisted options. Never allow
			// arbitrary WordPress options (users_can_register, default_role, ...)
			// to be written from an attacker-supplied import file.
			if(!in_array($nm, $allowed, true)) {
				continue;
			}

			update_option($nm, $vl);
		}
	}
}
