(function ($) {

    'use strict';

    $(document).ready(function () {

        let registerForm = $('#solbids-register');
        if (0 < registerForm.length) {
            $('body').on('click', '#solbids-register .submit', function () {
                $('#solbids-register .form').submit();

                return false;
            });

            $('body').on('click', '#solbids-register .cancel', function () {
                let disconnect = $('.wallet-adapter-dropdown-list li:nth-child(3)');
                disconnect.trigger('click');

                return false;
            });
        }

    });

})(jQuery);