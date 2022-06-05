<?php
/**
 * Init all plugin hooks
 *
 * @since 1.0.0
 */

namespace WP_Wallet_Adapter;

use Damax_Transfers\Shortcode;
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
            return;
        }

        add_action('init_wp_wallet_adapter', array(Shortcode::class, 'instance'));
        add_action('init_wp_wallet_adapter', array(React::class, 'instance'));

        do_action('init_wp_wallet_adapter');
    }

}