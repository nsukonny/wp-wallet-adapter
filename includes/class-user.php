<?php
/**
 * Authorization user by request from React App
 *
 * @since 1.0.0
 */

namespace WP_Wallet_Adapter;

use DazedDucks\Wallet;
use Helperpress\Settings;
use NSukonny\NFramework\Singleton;

defined('ABSPATH') || exit;

class User
{

    use Singleton;

    /**
     * Init app
     *
     * @since 1.0.0
     */
    public function init()
    {
        $this->redirect_to_fill_email();
    }

    /**
     * Ask user for fill username and Email
     *
     */
    private function redirect_to_fill_email()
    {
        global $wp;

        if (is_user_logged_in()) {
            $userdata = get_userdata(get_current_user_id());
            if (empty($userdata->user_email) && '/index.php/account/edit-account/' != $_SERVER['REQUEST_URI']) {
                wp_redirect(home_url('index.php/account/edit-account/'));
                exit;
            }
        }
    }

}