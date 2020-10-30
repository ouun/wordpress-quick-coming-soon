<?php
/**
 * Plugin Name:         Quick Coming Soon
 * Plugin URI:          https://ouun.io
 * Description:         Quick & simple "Coming Soon" page with redirects
 * Author:              Philipp Wellmer <philipp@ouun.io>
 * License:             MIT
 * License URI:         https://opensource.org/licenses/MIT
 * Requires PHP:        5.6
 * Text Domain:         quick-cs
 * Domain Path:         /languages
 */

// Useful global constants.
define('QCS_WP_VERSION', '1.0.0');
define('QCS_WP_URL', plugin_dir_url(__FILE__));
define('QCS_WP_PATH', plugin_dir_path(__FILE__));
define('QCS_WP_INC', QCS_WP_PATH . 'includes/');

require_once(QCS_WP_INC . 'quickcs.php');

// Require Composer autoloader if it exists.
if (file_exists(QCS_WP_PATH . '/vendor/autoload.php')) {
    require_once QCS_WP_PATH . 'vendor/autoload.php';
}

// Activate & Deactivation
register_activation_hook(__FILE__, 'quickcs_create_landing_page');
register_deactivation_hook(__FILE__, 'quickcs_unpublish_page_on_deactivation');
