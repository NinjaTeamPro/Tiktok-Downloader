<?php
/**
 * Plugin Name: Tiktok Video Downloader
 * Plugin URI: https://ninjateam.org/tiktok-video-downloader
 * Description: Fastest TikTok video downloader from the URLs.
 * Version: 1.1
 * Author: Ninja Team
 * Author URI: https://ninjateam.org
 * Text Domain: njt-tiktok
 * Domain Path: /i18n/languages/
 *
 * @package NjtTiktok
 */

namespace NjtTiktok;

defined('ABSPATH') || exit;

define('NJT_TK_PREFIX', 'njt_tk');
define('NJT_TK_VERSION', '1.1');
define('NJT_TK_DOMAIN', 'njt-tiktok');
define('NJT_TK_PLUGIN_DIR', basename(__DIR__));
define('NJT_TK_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NJT_TK_PLUGIN_PATH', plugin_dir_path(__FILE__));

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
add_action('plugins_loaded', 'NjtTiktok\\init');

register_activation_hook(__FILE__, array('NjtTiktok\\Plugin', 'activate'));
register_deactivation_hook(__FILE__, array('NjtTiktok\\Plugin', 'deactivate'));
