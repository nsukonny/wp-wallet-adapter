<?php
/**
 * Init all plugin hooks
 *
 * @since 1.0.0
 */

namespace WP_Wallet_Adapter;

use NSukonny\NFramework\Singleton;

defined( 'ABSPATH' ) || exit;

class Init {

	use Singleton;

	/**
	 * Init core of the plugin
	 *
	 * @since 1.0.0
	 */
	public function init() {

		add_action( 'init_wp_wallet_adapter', array( Form::class, 'instance' ) );

		do_action( 'init_wp_wallet_adapter' );

	}

}