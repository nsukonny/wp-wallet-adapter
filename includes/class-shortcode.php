<?php
/**
 * Display the form
 *
 * @since 1.0.0
 */

namespace WP_Wallet_Adapter;

use NSukonny\NFramework\Singleton;

defined('ABSPATH') || exit;

class Shortcode
{

    use Singleton;

    public $settings;

    /**
     * Init post types
     *
     * @since 1.0.0
     */
    public function init()
    {
        add_shortcode('wp_wallet_adapter', array($this, 'display_wrapper'));
    }

    /**
     * Render the form
     *
     * @since 1.0.0
     */
    public function display_wrapper()
    {
        ob_start();
        $args = array();

        $template = 'wrapper';

        if ($overridden_template = locate_template('wp-wallet-adapter/' . $template . '.php')) {
            load_template($overridden_template, true, $args);

            return ob_get_clean();
        }

        load_template(PATH . '/templates/' . $template . '.php', true, $args);

        return ob_get_clean();
    }

}