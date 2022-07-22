<?php
/**
 * Buy bids and other
 *
 * @since 1.0.0
 */

namespace WP_Wallet_Adapter;

use DazedDucks\Wallet;
use Helperpress\Settings;
use NSukonny\NFramework\Singleton;
use WP_User;

defined('ABSPATH') || exit;

class Bids
{

    use Singleton;

    /**
     * Init app
     *
     * @since 1.0.0
     */
    public function init()
    {
        add_filter('bid_text', array($this, 'bid_text'), 10, 2);
        add_action('wp_footer', array($this, 'success_message'));

        add_action('wp_ajax_wp_wallet_adapter_buy_bids', array($this, 'buy_bids'));
        add_action('wp_ajax_nopriv_wp_wallet_adapter_buy_bids', array($this, 'buy_bids'));
    }

    /**
     * Add bids for user after success transaction
     *
     * @since 1.0.0
     */
    public function buy_bids()
    {
        $wallet = $this->sanityze_wallet($_REQUEST['public_key']);
        $key    = md5('Cmim4vT1gCSC698T' . $wallet);

        if ($wallet && $key == $_REQUEST['key']) {
            if (is_user_logged_in()) {
                $bids = (int)sanitize_text_field($_REQUEST['bids']);
                if (0 < $bids) {
                    $user_id = get_current_user_id();
                    $mycred  = mycred('mycred_default');
                    $balance = (int)$mycred->get_users_balance($user_id);
                    $balance += $bids;
                    $balance = round($balance);

                    $mycred->set_users_balance($user_id, $balance);
                    $success = _('Congratulations, you can start bidding now!');
                    update_user_meta($user_id, 'buybids_success', $success);

                    wp_send_json_success(array('reload' => true));
                }
            }
        }

        wp_send_json_error();
    }

    /**
     * Auth user by request from React App
     *
     * @since 1.0.0
     */
    public function logout()
    {
        if (is_user_logged_in()) {
            $this->logout_user();
            wp_send_json_success(array('reload' => true));
        }

        wp_send_json_error();
    }

    /**
     * Display Connect wallet text if user not logged in
     *
     * @param $text
     * @param $product
     */
    public function bid_text($text, $product)
    {
        if ( ! is_user_logged_in()) {
            return _('Connect Wallet');
        }

        return _('Bid Now');
    }

    /**
     * Show success message after buy bids
     */
    public function success_message()
    {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $success = get_user_meta($user_id, 'buybids_success', true);

            if ( ! empty($success)) {
                ?>
                <div class="solbids-modal">
                    <div class="reg-wrapper">
                        <a href="#" class="solbids-close"><?php
                            _e('Close'); ?></a>

                        <div class="success"><?php
                            echo esc_attr($success); ?>
                        </div>

                        <div class="wd-btn-wrap text-left">
                            <a href="/auctions/"
                               class="submit wd-btn btn-solid btn-color-1 hover-color-2 btn-medium btn-radius icon-after">
                                <?php
                                _e('Auctions', 'wallet-adapter'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
                update_user_meta($user_id, 'buybids_success', '');
            }
        }
    }

    /**
     * Check if wallet has Dazed Ducks NFT
     *
     * @param $wallet
     *
     * @return bool
     */
    private function sanityze_wallet($wallet)
    {
        $wallet = trim(sanitize_text_field($wallet));

        if (32 <= mb_strlen($wallet) && 44 >= mb_strlen($wallet)) {
            return $wallet;
        }

        return null;
    }

    /**
     * Login to user account by his Solana string
     *
     * @param mixed $wallet
     */
    private function login_by_wallet($wallet) //TODO Make more strong security
    {
        $users = get_users(
            array(
                'meta_key'   => 'solana_gems_wallet',
                'meta_value' => $wallet,
            )
        );

        if (0 < count($users)) {
            foreach ($users as $user) {
                $this->login_user($user);

                return $user;
            }
        }

        return null;
    }

    /**
     * Register new user by Solana wallet string
     *
     * @param mixed $wallet
     */
    private function register_by_wallet($wallet) //TODO Make more strong security
    {
        $login    = $this->generate_login($wallet);
        $password = wp_generate_password(12, true);
        $user_id  = wp_create_user($login, $password);
        if ( ! is_wp_error($user_id)) {
            update_user_meta($user_id, 'solana_gems_wallet', $wallet);

            $user = get_user_by('id', $user_id);
            $user->remove_role('subscriber');
            $user->add_role('holder');
            $this->login_user($user);

            return $user;
        }

        return null;
    }

    /**
     * Generate login from wallet
     *
     * @param $wallet
     *
     * @return string
     */
    private function generate_login($wallet): string
    {
        return substr($wallet, 0, 4) . '__' . substr($wallet, -4);
    }

    /**
     * Log in to system as user by id
     *
     * @param WP_User $user
     */
    private function login_user(WP_User $user)
    {
        clean_user_cache($user->ID);
        wp_clear_auth_cookie();
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, false, false);
        update_user_caches($user);
    }

    /**
     * Logout current user
     */
    private function logout_user()
    {
        wp_destroy_current_session();
        wp_clear_auth_cookie();
        wp_set_current_user(0);
    }

    /**
     * Link wallet to current user
     *
     * @param $wallet
     */
    private function link_wallet_to_user($wallet)
    {
        update_user_meta(get_current_user_id(), 'solana_gems_wallet', $wallet);
    }

}