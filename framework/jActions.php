<?php

use JP\Framework\Elements\jCandidate;
use JP\Framework\Traits\DemandeTrait;
use JP\Framework\Traits\DemandeTypeTrait;

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
        $email = jTools::getValue('email');
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
        global $engine, $no_reply_email;
        $to = $email;
        $User = get_user_by('email', $to);
        $subject = "Réinitialiser votre mot de passe - JOBJIABY";
        $headers = [];
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = "From: Jobjiaby <{$no_reply_email}>";
        $content = '';
        $custom_logo_id = get_theme_mod('custom_logo');
        $logo = wp_get_attachment_image_src($custom_logo_id, 'full');
        $content .= $engine->parseFile('forgot-password')->render([
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

    // Tous les offres que le candidats à postuler
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
    global $jj_errors;

    $confirmation_register = get_page_by_path('confirmation-register', OBJECT);
    $page_id = $confirmation_register->ID;

    //if (!is_singular()) return;
    if (jTools::getValue('_wpnonce', false)) {
        // Enregistrer les informations utilisateur
        if (wp_verify_nonce($_POST['_wpnonce'], 'jobjiaby-register')) {
            $email = jTools::getValue('email', null);
            $role = jTools::getValue('role', null);
            if (is_null($email) || is_null($role)) { return false; }
            $password = jTools::getValue('password');
            if (!$password) return;
            $args = [
                'user_pass' => $password,
                'nickname' => $email,
                'first_name' => jTools::getValue('first_name', ''),
                'last_name' => '',
                'user_login' => $email,
                'user_email' => $email,
                'role' => $role
            ];
            // Check if user exist
            if (email_exists($email) || username_exists($email)) {
                // User exist in bdd
                $jj_errors[] = ['type' => 'email', 'msg' => "Adresse email existe déja"];
                return;
            } else {
                $response = wp_insert_user($args);
                if (is_wp_error($response)) {
                    $jj_errors[] = ['type' => "global", "msg" => $response->get_error_message()];
                    return false;
                }
            }
            $user_id = (int)$response;
            $phone_number = jTools::getValue('phone');
            // For candidate only...
            if ($role === 'candidate') {
                // Pour les candidat
                $candidate = new jCandidate($user_id);
                $candidate->profile_update([
                    'phones' => esc_sql($phone_number),
                    'validated' => 0, // not active user
                    'has_cv' => 0, // and don't have CV
                    'blocked' => 0,
                    'cv_status' => 1,
                ]);
            } else {
                // pour les employer
                update_user_meta($user_id, 'company_id', 0);
            }

            // Ajouter un meta pour la verification de mail
            update_user_meta($user_id, 'email_verify', 0);
            do_action('send_email_new_user', $user_id); // Envoyer le mail
            // Redirection
            wp_redirect(get_the_permalink($page_id) . '?user_id='.$user_id);
            exit();
        }
    }
}

/**
 * Processus d'activation d'adresse email du client
 */
add_action('init', 'process_validate_user_email', 1);
function process_validate_user_email() {
    global $jj_messages;
    $nonce = jTools::getValue('verify_email_nonce', false);
    if (!$nonce) return false;
    if (wp_verify_nonce($nonce, 'jobjiaby_verify_email')) {
        $email = jTools::getValue('e'); // email base64 encrypt
        $email = base64_decode($email);
        if ($user_id = email_exists($email)) {
            $is_verify = (int)get_user_meta($user_id, 'email_verify', true);
            if ($is_verify) {
                // Compte déja actif
                return;
            }
            update_user_meta($user_id, 'email_verify', 1);
            $jj_messages[] = ['type' => 'success', 'msg' => "Votre compte est activer"];
        }
    } else {
        $jj_messages[] = ['type' => 'danger', 'msg' => "Lien invalide"];
    }
}

/**
 * Cette processus permet d'afficher une message si l'adresse email du client n'est pas encore valide.
 * Il permet aussi de renvoyer le mail de verification d'adresse.
 */
add_action('wp_loaded', 'process_resend_verify_user_email', 1);
function process_resend_verify_user_email() {
    global $jj_messages;

    if (!is_user_logged_in()) return;

    $user_id = get_current_user_id();
    // Resend verify user email
    $nonce = jTools::getValue('e-verify-nonce');
    $verification = wp_verify_nonce($nonce, 'jobjiaby_resend_verify');
    if ($nonce && $verification) {
        // Send email
        do_action('send_email_new_user', $user_id);
        wp_redirect( get_site_url() );
        exit();
    }

    // Afficher la banniere si le compte n'est pas encore verifié
    $is_verify = get_user_meta($user_id, 'email_verify', true);
    if (!$is_verify || intval($is_verify) === 0) {
        $link_nonce = wp_create_nonce("jobjiaby_resend_verify");
        $user = new WP_User($user_id);
        $jj_messages[] = [
            'type' => 'warning',
            'msg' =>  "Bonjour {$user->display_name}, veuillez consulter {$user->user_email} pour terminer le processus d'inscription.",
            'btn' => "Envoyer",
            'btn_link' => home_url('/?e-verify-nonce=' . $link_nonce)
        ];
    }
}

add_action('demande_handler', 'process_demande_handler');
function process_demande_handler() {
    $controller = jTools::getValue('controller', null);
    if ($controller && $controller === 'DEMANDE') {
        $method = jTools::getValue('method', null);
        $type_demande = jTools::getValue('type_demande', null);
        $user_id = get_current_user_id();

        switch ($method) {
            case 'CREATE':

                switch ($type_demande) {
                    case 'DMD_CANDIDAT':

                        global $wpdb, $jj_messages;
                        $candidate_id = jTools::getValue('candidate_id', null);
                        $table = DemandeTrait::getTableName();
                        $type_dmd_id = DemandeTypeTrait::getTypeId($type_demande);

                        // Verifier si l'utiliateur à déja fait la demande
                        $reference = md5("{$method}:{$type_demande}:{$user_id}:{$type_dmd_id}:{$candidate_id}");

                        $queryReference = $wpdb->get_row("SELECT * FROM $table WHERE reference = '$reference'");
                        $wpdb->flush();
                        if ($queryReference) {
                            $jj_messages[] = ['type' => 'warning', 'msg' => "Demande déja en cours"];
                            break;
                        }

                        $data = [
                            "candidate_id" => (int)$candidate_id,
                        ];
                        $data_request = (object) $data;
                        $createDemande = $wpdb->insert($table, [
                            'user_id' => $user_id,
                            'type_demande_id' => $type_dmd_id,
                            'reference' => $reference,
                            'data_request' => serialize($data_request)
                        ]);
                        $wpdb->flush();
                        if ($createDemande) {
                            $jj_messages[] = ['type' => 'success', 'msg' => "Votre demande a été envoyer avec succès"];
                        } else {
                            $jj_messages[] = ['type' => 'danger', 'msg' => "Une erreur c'est produit pendant l'envoye de votre demande"];
                        }
                        break;
                }
                break;
        }
    }
}

add_action('save_post_jp-jobs', function($post_id, WP_Post $post) {
    if (define('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    // is published
    if ($post->post_status === 'publish'):
        $is_published = get_post_meta($post_id, '_is_published', true); // int 1 or 0 or nothing
        if (!$is_published || 0 == $is_published) {
            do_action('send_mail_when_publish_emploi', $post_id);
            update_post_meta($post_id, '_is_published', 1);
        }
    endif;

}, 20, 2);



