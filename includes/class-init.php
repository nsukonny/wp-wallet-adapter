<?php
/**
 * Init all plugin hooks
 *
 * @since 1.0.0
 */

namespace WP_Wallet_Adapter;

use NSukonny\NFramework\Singleton;

defined('ABSPATH') || exit;

class Init
{

    use Singleton;

    /**
     * Init core of the plugin
     *
     * @since 1.0.0
     */
    public function init()
    {
        if (is_admin()) {
            add_action('init_wp_wallet_adapter_admin', array(Auth::class, 'instance'));

            do_action('init_wp_wallet_adapter_admin');

            return;
        }

        add_action('init_wp_wallet_adapter', array(User::class, 'instance'));
        add_action('init_wp_wallet_adapter', array(Shortcode::class, 'instance'));
        add_action('init_wp_wallet_adapter', array(React::class, 'instance'));
        add_action('init_wp_wallet_adapter', array(Auth::class, 'instance'));

        do_action('init_wp_wallet_adapter');
    }

}