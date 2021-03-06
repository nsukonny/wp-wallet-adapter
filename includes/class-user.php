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
        if ($this->is_not_registered() && ! is_admin()) {
            $this->registration_popup();
        }

        add_action('show_user_profile', array($this, 'show_solana_wallet'));
        add_action('edit_user_profile', array($this, 'show_solana_wallet'));
        add_action('personal_options_update', array($this, 'save_solana_wallet'));
        add_action('edit_user_profile_update', array($this, 'save_solana_wallet'));

        add_action('wp_ajax_wp_wallet_adapter_validate', array($this, 'validate_registration'));
        add_action('wp_ajax_nopriv_wp_wallet_adapter_validate', array($this, 'validate_registration'));
    }

    /**
     * Add row with user Solana wallet in user page
     *
     * @param $user
     */
    public function show_solana_wallet($user)
    {
        ?>
        <table class="form-table">
            <tr>
                <th>
                    <label for="solana_gems_wallet"><?php
                        _e('Solana wallet'); ?></label>
                </th>
                <td>
                    <input type="text" name="solana_gems_wallet" id="solana_gems_wallet" value="<?php
                    echo esc_attr(get_user_meta($user->ID, 'solana_gems_wallet', true)); ?>" class="regular-text"/>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Update changes user wallet
     *
     * @param $user_id
     */
    public function save_solana_wallet($user_id)
    {
        if (current_user_can('edit_user', $user_id)) {
            update_user_meta($user_id, 'solana_gems_wallet', $_POST['solana_gems_wallet']);
        }
    }

    /**
     * Validate DisplayName and Email
     *
     * @since 1.0.0
     */
    public function validate_registration()
    {
        $user_name = sanitize_text_field($_REQUEST['user_name']);
        $email     = sanitize_email($_REQUEST['email']);

        if (empty($user_name)) {
            wp_send_json_error(array('user_name' => _('Username must be filled.')));
        }

        if (8 > mb_strlen($user_name) || 24 < mb_strlen($user_name)) {
            wp_send_json_error(array('user_name' => _('The username must be from 8 and less than 24 symbols.')));
        }

        if ($this->is_user_exist('display_name', $user_name)) {
            wp_send_json_error(array('user_name' => _('This Username is already in use. Please, pick another.')));
        }

        if (empty($email)) {
            wp_send_json_error(array('email' => _('Email must be filled')));
        }

        if ($this->is_user_exist('user_email', $email)) {
            wp_send_json_error(array('email' => _('This Email is already in use. Please, pick another.')));
        }

        if ( ! $this->save_userdata($user_name, $email)) {
            wp_send_json_error(array('user_name' => _('Something wrong. Please, try again.')));
        }

        wp_send_json_success();
    }

    /**
     * Check if user must fill email
     */
    private function is_not_registered(): bool
    {
        if (is_user_logged_in()) {
            $registered = get_user_meta(get_current_user_id(), 'solbids_registered', true);
            if ( ! isset($registered) || empty($registered)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Display registration popup
     */
    private function registration_popup()
    {
        $user_id = get_current_user_id();
        $user    = get_userdata($user_id);
        ?>
        <div id="solbids-register" style="display: none;">
            <div class="reg-wrapper">
                <header class="user__header"><?php
                    if (grbid_get_option('show_the_logo') == 'on' && grbid_get_option('logo_link') != '') { ?>
                        <?php
                        $image = grbid_get_option('logo_link');
                        ?>
                        <a href="<?php
                        echo esc_url(home_url('/')); ?>" rel="home" title="<?php
                        echo bloginfo('name') ?>"
                           class="active"><img src="<?php
                            echo esc_url($image); ?>" alt="<?php
                            echo bloginfo('name') ?>"/></a>
                        <?php
                    } ?>
                    <h1 class="user__title"><?php
                        _e(
                            'Welcome! Please make your username and tell us your email address.',
                            'wallet-adapter'
                        ); ?></h1>
                </header>

                <form class="form" method="post" name="register_phantom_user">

                    <div class="form__group">
                        <input type="text" placeholder="Username" name="user_name" value="<?php
                        echo esc_attr($user->display_name); ?>" class="form__input" maxlength="24"/>
                    </div>

                    <div class="form__group">
                        <input type="email" placeholder="Email" name="user_email" class="form__input"/>
                    </div>

                    <div class="wd-btn-wrap text-left">
                        <a href="#"
                           class="submit wd-btn btn-solid btn-color-1 hover-color-2 btn-medium btn-radius icon-after">
                            <?php
                            _e('Register', 'wallet-adapter'); ?>
                        </a>
                        <a href="#" class="cancel">
                            <?php
                            _e('Cancel', 'wallet-adapter'); ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Update userdata
     *
     * @return false
     */
    private function save_userdata($user_name, $user_email)
    {
        $user_id      = get_current_user_id();
        $max_username = 24;

        if ($max_username < strlen($user_name)) {
            $user_name = mb_strcut($user_name, 0, $max_username, "UTF-8");
        }

        $args = array(
            'ID'           => $user_id,
            'user_email'   => $user_email,
            'display_name' => $user_name,
        );

        $user = wp_update_user($args);
        if ( ! is_wp_error($user)) {
            update_user_meta($user_id, 'solbids_registered', time());

            return true;
        }

        return false;
    }

    /**
     * Check if user with this username exist
     *
     * @return bool
     */
    private function is_user_exist($field, $val)
    {
        global $wpdb;

        $users = $wpdb->get_results("SELECT user_email FROM $wpdb->users WHERE " . $field . " = '" . $val . "'");

        return 0 !== count($users);
    }

}