<?php
/*
 * Plugin Name: IDN
 * Plugin URI: http://wordpress.org/plugins/idn/
 * Description: Add support for Internationalized Domain Name
 * Version: 0.1.0
 * Author: Daisuke Takahashi(Extend Wings)
 * Author URI: http://www.extendwings.com
 * License: AGPLv3 or later
 * Text Domain: idn
 * Domain Path: /languages/
*/

if(!function_exists('add_action')) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if(version_compare(get_bloginfo('version'), '3.8', '<')) {
	require_once(ABSPATH.'wp-admin/includes/plugin.php');
	deactivate_plugins(__FILE__);
}

add_action('init', array('IDN', 'init'));

class IDN {
	static $instance;

	static function init() {
		if(!self::$instance) {
			self::$instance = new IDN;
		}
		return self::$instance;
	}

	private function __construct() {
		add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta'), 10, 2);
	}

	function plugin_row_meta($links, $file) {
		if(plugin_basename(__FILE__) === $file) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url('http://www.extendwings.com/donate/'),
				__('Donate', 'idn')
			);
		}
		return $links;
	}
}

if(!function_exists('wp_sanitize_redirect')) {
	function wp_sanitize_redirect($location) {
		if(preg_match('|^https?://|i', $location)) {
			require_once plugin_dir_path( __FILE__ ) . 'pear-Net_IDNA2/IDNA2.php';
			$idna = Net_IDNA2::getInstance(array(
				'version' => '2008'
			));
			$location = $idna->encode($location);
		}

		$location = preg_replace('|[^a-z0-9-~+_.?#=&;,/:%!*]|i', '', $location);
		$location = wp_kses_no_null($location);

		// remove %0d and %0a from location
		$strip = array('%0d', '%0a', '%0D', '%0A');
		$location = _deep_replace($strip, $location);
		return $location;
	}
}
