(function($) {
    $().ready(function() {
        $("#register-form").validate({
            rules: {
                role: {
                    required: true
                },
                first_name: {
                    required: true,
                    minlength: 4
                },
                password: {
                    required:true,
                    minlength: 5
                },
                confirm_password: {
                    required: true,
                    minlength: 5,
                    equalTo: "#password"
                },
                email: {
                    required: true,
                    email: true
                }
            }
        });
        if (registerSetting.is_logged) {
            // Envoyer l'utilisateur dans son espace client s'il est connecter
            window.location.href = registerSetting.espace_client;
        }
    });
})(jQuery);