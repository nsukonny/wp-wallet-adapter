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
        $this->save_userdata();

        if ($this->is_not_registered()) {
            $this->registration_popup();
        }
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
        <div id="solbids-register">
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

                <form class="form" method="post">
                    <div class="form__group">
                        <input type="text" placeholder="Username" name="user_name" value="<?php
                        echo esc_attr($user->display_name); ?>" class="form__input"/>
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
    private function save_userdata(): bool
    {
        if ( ! isset($_REQUEST['user_name']) || ! isset($_REQUEST['user_email']) || ! is_user_logged_in()) {
            return false;
        }

        $user_name  = sanitize_text_field($_REQUEST['user_name']);
        $user_email = sanitize_email($_REQUEST['user_email']);
        $user_id    = get_current_user_id();

        $args = array(
            'ID'           => $user_id,
            'user_email'   => $user_email,
            'display_name' => $user_name,
        );

        update_user_meta($user_id, 'solbids_registered', time());

        return wp_update_user($args);
    }

}