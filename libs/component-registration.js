"use strict";

(function ($) {
  $().ready(function () {
    var registerForm = $("#register-form");

    if (registerForm.length > 0) {
      registerForm.validate({
        rules: {
          role: {
            required: true
          },
          first_name: {
            required: true,
            minlength: 4
          },
          password: {
            required: true,
            minlength: 8
          },
          confirm_password: {
            required: true,
            equalTo: "#password"
          },
          email: {
            required: true,
            email: true
          }
        },
        messages: {
          role: 'Ce champ est obligatoire',
          first_name: {
            required: 'Ce champ est obligatoire',
            minlength: 'Votre nom est trop courte'
          },
          password: {
            required: 'Ce champ est obligatoire',
            minlength: 'Veuillez saisir au moins 8 caractères.'
          },
          confirm_password: {
            required: 'Ce champ est obligatoire',
            equalTo: "Entrez à nouveau la même valeur s'il vous plait."
          },
          email: {
            required: 'Ce champ est obligatoire',
            email: 'S\'il vous plaît, mettez une adresse email valide.'
          }
        }
      });

      if (registerSetting.is_logged) {
        // Envoyer l'utilisateur dans son espace client s'il est connecter
        window.location.href = registerSetting.espace_client;
      }
    }
  });
})(jQuery);