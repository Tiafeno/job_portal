<?php

use JP\Framwork\Elements\jpCandidate;

if (!defined('ABSPATH')) {
    exit;
}
$no_reply_email = "no-reply@jobjiaby.com>";
add_action('init', function () {
    // Permet de se connecter avec AJAX
    add_action('wp_ajax_ajax_login', 'login');
    add_action('wp_ajax_nopriv_ajax_login', 'login');
    function login()
    {
        if (is_user_logged_in()) {
            wp_logout();
            wp_send_json_error(new WP_Error(406, "La ressource demandée n'est pas disponible"));
        }
        // First check the nonce, if it fails the function will break
        check_ajax_referer('ajax-login-nonce', 'security');
        // Nonce is checked, get the POST data and sign user on
        $info = array();
        $info['user_login'] = $_POST['username'];
        $info['user_password'] = $_POST['password'];
        $info['remember'] = true;
        $user_signon = wp_signon($info, false);
        if (!is_wp_error($user_signon)) {
            wp_set_current_user($user_signon->ID);
            wp_set_auth_cookie($user_signon->ID);
            // Envoyer le REST API controller pour l'utilisateur
            $req = new WP_REST_Request();
            $req->set_param('context', 'edit'); // set context edit 

            $user = new WP_USER((int)$user_signon->ID);
            $response = new stdClass();
            $response->id = $user->ID;
            $response->roles = $user->roles;
            $response->meta = new stdClass();
            if (in_array('employer', $response->roles)) {
                $company_id = get_user_meta($user->ID, 'company_id', true);
                $response->meta->company_id = intval($company_id);
            }
            wp_send_json_success($response);
        } else {
            // Envoyer l'objet WP_Error
            wp_send_json_error($user_signon);
        }
    }

    /**
     * Fonction ajax - nopriv only.
     * Envoie un email pour recuperer le mot de passe
     */
    add_action('wp_ajax_nopriv_forgot_password', 'forgot_password');
    function forgot_password()
    {
        if (is_user_logged_in()) {
            wp_send_json_error(["msg" => "Vous ne pouvez pas effectuer cette action"]);
        }
        $email = jpHelpers::getValue('email');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            wp_send_json_error(["msg" => "Paramétre non valide"]);
        }
        $user = get_user_by('email', $email);
        if (!$user) {
            wp_send_json_error(['msg' => "Votre recherche ne donne aucun résultat. Veuillez réessayer avec d’autres adresse email."]);
        }
        $reset_key = get_password_reset_key($user);
        if (is_wp_error($reset_key)) {
            wp_send_json_error(['msg' => $reset_key->get_error_message()]);
        }
        // Envoyer un email à l'utilisateur
        do_action('forgot_my_password', $email, $reset_key);
    }

    /**
     * Envoyer un mail de recuperation de mot de passe
     *
     * @param string $email
     * @param string $key - An generate reset key
     */
    add_action('forgot_my_password', 'forgot_my_password', 10, 2);
    function forgot_my_password($email, $key)
    {
        global $Liquid_engine, $no_reply_email;
        $to = $email;
        $User = get_user_by('email', $to);
        $subject = "Réinitialiser votre mot de passe - JOBJIABY";
        $headers = [];
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = "From: Jobjiaby <{$no_reply_email}>";
        $content = '';
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
        $content .= $Liquid_engine->parseFile('forgot-password')->render([
            'forgot_link' => home_url('/forgot-password') . "?key={$key}&account={$User->user_login}&action=resetpass",
            'home_url' => home_url("/"),
            'logo' => $logo[0]
        ]);
        $sender = wp_mail($to, $subject, $content, $headers);
        if ($sender) {
            // Mail envoyer avec success
            wp_send_json_success([
                "msg" => "Merci de vérifier que vous avez reçu un e-mail avec un lien de récupération.",
                'key' => $key
            ]);
        } else {
            // Erreur d'envoie
            wp_send_json_error([
                "msg" => "Le message n’a pas pu être envoyé. " .
                    "Cause possible : Votre hébergeur a peut-être désactivé la fonction mail().",
                "key" => $key,
                "login" => $User->user_login
            ]);
        }
    }

    // Modifier le mot de passe d'un utilisateur
    add_action('wp_ajax_change_my_pwd', function () {
        check_ajax_referer('ajax-client-form', 'pwd_nonce');
        if (!is_user_logged_in()) {
            wp_send_json_error("Vous ne pouvez pas changer de mot de passe. Veuillez vous connecter");
        }
        $password = $_POST['pwd'];
        $user_id = get_current_user_id();
        wp_set_password($password, $user_id);
        wp_send_json_success("Mot de passe modifier avec succès");
    });
    add_action('wp_ajax_ad_handler_apply', function () {
        global $wpdb;
        $candidate_id = intval($_GET['cid']);
        $table_apply = APPLY_TABLE;
        $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_apply WHERE candidate_id = %d", $candidate_id));
        $jobs = [];
        $request = new WP_REST_Request();
        $request->set_param('context', 'edit');
        foreach ($results as $result) {
            $job_controller = new WP_REST_Posts_Controller('jp-jobs');
            $jobs[] = $job_controller->prepare_item_for_response(get_post($result->job_id), $request)->data;
        }
        wp_send_json($jobs);
    });
});

/**
 * Permet d'enregistrer un utilisateur (Employer ou Candidat)
 */
add_action('init', 'pre_process_registration', 1);
function pre_process_registration() {
    global $errors;
    //if (!is_singular()) return;
    if (\jpHelpers::getValue('_wpnonce', false)) {
        // Enregistrer les informations utilisateur
        if (wp_verify_nonce($_POST['_wpnonce'], 'jobjiaby-register')) {
            $email = \jpHelpers::getValue('email', null);
            if (is_null($email) || empty($_POST['role'])) { return false; }
            $role = esc_attr($_POST['role']); //candidate or employer
            $password = \jpHelpers::getValue('password');
            if (!$password) return;
            $args = [
                'user_pass' => $password,
                'nickname' => $email,
                'first_name' => \jpHelpers::getValue('first_name', ''),
                'last_name' => '',
                'user_login' => $email,
                'user_email' => $email,
                'role' => $role
            ];
            // Check if user exist
            if (email_exists($email) || username_exists($email)) {
                // User exist in bdd
                $errors[] = ['type' => 'email', 'msg' => "Adresse email existe déja"];
                return;
            } else {
                $response = wp_insert_user($args);
                if (is_wp_error($response)) {
                    $errors[] = ['type' => "global", "msg" => $response->get_error_message()];
                    return false;
                }
            }
            $user_id = (int)$response;
            $phone_number = jpHelpers::getValue('phone');
            if ($role === 'candidate') {
                // Pour les candidat
                $candidate = new jpCandidate($user_id);
                $candidate->profile_update([
                    'phones' => esc_sql($phone_number),
                    'is_active' => 0,
                    'has_cv' => 0,
                ]);
            } else {
                // pour les employer
                update_user_meta($user_id, 'company_id', 0);
            }

            // Ajouter un meta pour la verification de mail
            update_user_meta($user_id, 'email_verify', 0);
            do_action('send_email_new_user', $user_id); // Envoyer le mail
            // Redirection
            wp_redirect(home_url('/'));
            exit();
        }
    }
}

add_action('init', 'process_validate_user_email', 1);
function process_validate_user_email() {

}

/**
 * Cette action permet de se connecter au site
 */
add_action('init', function () {
    $nonce = jpHelpers::getValue('jp-login-nonce', false);
    if (!$nonce) return;
    $nonce_verify = wp_verify_nonce($nonce, 'jp-login-action');
    if ($nonce_verify) {
        $remember = isset($_POST['remember']) ? true : false;
        $info = array();
        $info['user_login'] = $_POST['log'];
        $info['user_password'] = $_POST['pwd'];
        $info['remember'] = $remember;
        $user_signon = wp_signon($info, false);
        if (!is_wp_error($user_signon)) {
            // redirection dans l'espace client
            wp_set_current_user( $user_signon->ID );
            wp_set_auth_cookie( $user_signon->ID, $remember, false );
            //do_action( 'wp_login', $user_signon->user_login );
            wp_redirect(home_url('/espace-client'));
        }
    }
});


