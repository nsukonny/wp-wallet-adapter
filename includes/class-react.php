<?php
/**
 * Display the wrapper for integrate the app
 *
 * @since 1.0.0
 */

namespace WP_Wallet_Adapter;

use NSukonny\NFramework\Singleton;

defined('ABSPATH') || exit;

class React
{

    use Singleton;

    public $settings;

    /**
     * Init app
     *
     * @since 1.0.0
     */
    public function init()
    {
        add_action('wp_enqueue_scripts', array($this, 'parse_react_styles'));
    }

    /**
     * Parse React App styles from asset-manifest.json
     *
     * @param $hook
     *
     * @return false|void
     */
    public function parse_react_styles($hook)
    {
        $react_app_build = constant(__NAMESPACE__ . '\URL') . 'build/';
        $manifest_url    = $react_app_build . 'asset-manifest.json';
        $request         = file_get_contents($manifest_url);

        if ( ! $request) {
            return false;
        }

        $files_data = json_decode($request);
        if ($files_data === null) {
            return;
        }

        if ( ! property_exists($files_data, 'entrypoints')) {
            return false;
        }

        $assets_files = $files_data->entrypoints;
        $css_files    = array_filter($assets_files, array($this, 'rp_filter_css_files'));
        foreach ($css_files as $index => $css_file) {
            wp_enqueue_style('wp-wallet-adapter-' . $index, $react_app_build . $css_file);
        }

        $js_files = array_filter($assets_files, array($this, 'rp_filter_js_files'));
        foreach ($js_files as $index => $js_file) {
            wp_enqueue_script('wp-wallet-adapter-' . $index, $react_app_build . $js_file, array(), 1, true);
        }
    }

    /**
     * Get js files from assets array.
     *
     * @param array $file_string
     *
     * @return bool
     */
    public function rp_filter_js_files($file_string)
    {
        return pathinfo($file_string, PATHINFO_EXTENSION) === 'js';
    }

    /**
     * Get css files from assets array.
     *
     * @param array $file_string
     *
     * @return bool
     */
    public function rp_filter_css_files($file_string)
    {
        return pathinfo($file_string, PATHINFO_EXTENSION) === 'css';
    }

}