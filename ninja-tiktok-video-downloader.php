<?php
/**
 * Plugin Name: Tiktok video downloader
 * Plugin URI: https://ninjateam.org
 * Description: Internal template for plugin with OOP, namespace, autoload function.
 * Version: 0.1.0
 * Author: Ninja Team
 * Author URI: https://ninjateam.org
 * Text Domain: big-ninja
 * Domain Path: /i18n/languages/
 *
 * @package BigPlugin
 */

namespace BigNinja;

defined('ABSPATH') || exit;

define('BN_PREFIX', 'big-ninja');
define('BN_VERSION', '0.1.0');
define('NJT_TK_BN_DOMAIN', 'big-ninja');
define('BN_PLUGIN_DIR', basename(__DIR__));
define('BN_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BN_PLUGIN_PATH', plugin_dir_path(__FILE__));

spl_autoload_register(function ($class) {
    $prefix = __NAMESPACE__; // project-specific namespace prefix
    $base_dir = __DIR__ . '/includes'; // base directory for the namespace prefix

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) { // does the class use the namespace prefix?
        return; // no, move to the next registered autoloader
    }

    $relative_class_name = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class_name) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

function init()
{
    Plugin::getInstance();
    I18n::getInstance();

    TiktokDownloader\TiktokDownloader::getInstance();
    TiktokDownloader\TiktokAPI::getInstance();
}
add_action('plugins_loaded', 'BigNinja\\init');

register_activation_hook(__FILE__, array('BigNinja\\Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('BigNinja\\Plugin', 'deactivate'));
