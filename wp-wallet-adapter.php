<?php
/**
 * Plugin Name: Wallet Adapter
 * Plugin URI: nsukonny.agency/wp-wallet-adapter
 * Description: Using @solana/wallet-adapter for auth in the website
 * Version: 1.0.0
 * Author: NSukonny
 * Author URI: nsukonny.agency
 * Text Domain: wallet-adapter
 * Domain Path: /languages
 */

namespace WP_Wallet_Adapter;

use NSukonny\NFramework\Loader;

defined('ABSPATH') || exit;

require_once(plugin_dir_path(__FILE__) . '/n-framework.php');
require_once(plugin_dir_path(__FILE__) . '/vendor/autoload.php');
Loader::init_autoload(__NAMESPACE__, __DIR__);

//Now you can star the plugin
add_action('init', array(Init::class, 'instance'));
