(function ($) {

    'use strict';

    $(document).ready(function () {

        let registerModal = $('#solbids-register');

        registerModal.show();
        if (0 < registerModal.length) {
            $('body').on('click', '#solbids-register .submit', function () {
                $('#solbids-register .form').submit();

                return false;
            });

            $('body').on('click', '#solbids-register .cancel', function () {
                let disconnect = $('.wallet-adapter-dropdown-list li:nth-child(3)');
                disconnect.trigger('click');

                return false;
            });

            let registerForm = $('form[name="register_phantom_user"]');
            registerForm.submit(function () {
                let userNameInp = registerForm.find('input[name="user_name"]'),
                    userEmailInp = registerForm.find('input[name="user_email"]');

                let data = {
                    action: 'wp_wallet_adapter_validate',
                    user_name: userNameInp.val(),
                    email: userEmailInp.val(),
                }

                $.post(wp_wallet_adapter.ajax_url, data, function (response) {
                    let oldErrors = $('.solbids-error');
                    if (0 !== oldErrors.length) {
                        oldErrors.remove();
                    }

                    if (response.success) {
                        document.location.reload();
                        return true;
                    }

                    if (response.data.user_name) {
                        userNameInp.before('<div class="solbids-error">' +response.data.user_name+ '</div>');
                    }

                    if (response.data.email) {
                        userEmailInp.before('<div class="solbids-error">' +response.data.email+ '</div>');
                    }

                    return false;
                });

                return false;
            });
        }

        if (0 < $('.ask-connect-wallet').length) {
            $('body').on('click', '.ask-connect-wallet', function () {
                $('.App .wallet-adapter-button').trigger('cliproduct_type_auctionck');
            });
        }

        //Move wallet adapter button to mobile menu
        let mobMenu = $('.site-title-bar');
        if (0 < mobMenu.length) {
            if (mobMenu.first().is(':visible')) {
                $($('#wp-wallet-adapter-wrapper').detach()).appendTo(".title-bar-left");
            }
        }

        $('body').on('click', '.solbids-close', function () {
            $('.solbids-modal').hide();
        });

    });

})(jQuery);