<?php
if (!defined('ABSPATH')) {
    exit;
}
$no_reply_email = "no-reply@jobjiaby.com>";
// Create user role
add_action('helper_register_jp_user_role', function () {
    // for employer
    $employer_capabilities = array(
        'read' => true,  // true allows this capability
        'upload_files' => true,

        'edit_others_pages' => true,
        'edit_others_posts' => true,
        'edit_pages' => true,
        'edit_posts' => true,
        'edit_users' => true,
        'list_users' => true,

        'manage_options' => false,
        'remove_users' => false,
        'delete_others_pages' => true,
        'delete_posts' => false,
        'delete_pages' => false,
        'delete_published_posts' => false,
        'delete_users' => false,
        'delete_themes' => false,
        'delete_plugins' => false,

        'create_posts' => true, // Allows user to create new posts
        'manage_categories' => true, // Allows user to manage post categories
        'publish_posts' => true, // Allows the user to publish, otherwise posts stays in draft mode
        'edit_themes' => false, // false denies this capability. User can’t edit your theme
        'install_plugins' => false, // User cant add new plugins
        'update_plugin' => false, // User can’t update any plugins
        'update_core' => false, // user cant perform core updates
        'create_users' => true,
        'install_themes' => false,
    );
    add_role('employer', 'Employer', $employer_capabilities);

    // for candidate
    $candidate_capabilities = array(
        'read' => true,  // true allows this capability
        'upload_files' => true,
        'edit_posts' => true,
        'edit_users' => true,
        'list_users' => true,
        'manage_options' => false,
        'remove_users' => false,
        'delete_others_pages' => true,
        'delete_published_posts' => true,
        'edit_others_posts' => true, // Allows user to edit others posts not just their own
        'create_posts' => true, // Allows user to create new posts
        'manage_categories' => true, // Allows user to manage post categories
        'publish_posts' => true, // Allows the user to publish, otherwise posts stays in draft mode
        'edit_themes' => false, // false denies this capability. User can’t edit your theme
        'install_plugins' => false, // User cant add new plugins
        'delete_plugins' => false,
        'update_plugin' => false, // User can’t update any plugins
        'update_core' => false, // user cant perform core updatesy
        'create_users' => false,
        'delete_themes' => false,
        'install_themes' => false,
    );
    add_role('candidate', 'Candidate', $candidate_capabilities);

    // for company
    $company_capabilities = array(
        'read' => true,  // true allows this capability
        'upload_files' => true,
        'edit_posts' => true,
        'edit_users' => true,
        'list_users' => true,
        'manage_options' => false,
        'remove_users' => false,
        'delete_others_pages' => true,
        'delete_published_posts' => true,
        'edit_others_posts' => true, // Allows user to edit others posts not just their own
        'create_posts' => true, // Allows user to create new posts
        'manage_categories' => true, // Allows user to manage post categories
        'publish_posts' => true, // Allows the user to publish, otherwise posts stays in draft mode
        'edit_themes' => false, // false denies this capability. User can’t edit your theme
        'install_plugins' => false, // User cant add new plugins
        'delete_plugins' => false,
        'update_plugin' => false, // User can’t update any plugins
        'update_core' => false, // user cant perform core updatesy
        'create_users' => false,
        'delete_themes' => false,
        'install_themes' => false,
    );
    add_role('company', 'Company', $company_capabilities);

});
// Create post type
add_action('helper_register_jp_post_types', function () {
    // Emploi post type
    register_post_type('jp-jobs', [
        'label' => "L'emploi",
        'labels' => [
            'name' => "L'emplois",
            'singular_name' => "Emploi",
            'add_new' => 'Ajouter',
            'add_new_item' => "Ajouter une nouvelle emploi",
            'edit_item' => 'Modifier',
            'view_item' => 'Voir',
            'search_items' => "Trouver des emplois",
            'all_items' => "Tous les emplois",
            'not_found' => "Aucune emploi trouver",
            'not_found_in_trash' => "Aucune emploi dans la corbeille"
        ],
        'public' => true,
        'hierarchical' => false,
        'menu_position' => null,
        'show_ui' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'emploi'],
        'rest_base' => 'emploi',
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'menu_icon' => 'dashicons-media-interactive',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
        'show_in_rest' => true
    ]);
    // Ajouter category pour le 'jp-jobs'
    register_taxonomy_for_object_type('category', 'jp-jobs');
    // Logiciel maitrisés
    register_taxonomy('tech_mastery', ['post'], [
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Logiciels',
            'singular_name' => 'Logiciel',
            'search_items' => 'Trouver',
            'all_items' => 'Trouver des tech.',
            'parent_item' => 'Tech. parent',
            'parent_item_colon' => 'Tech. parent:',
            'edit_item' => 'Modifier la technologie',
            'update_item' => 'Mettre à jour',
            'add_new_item' => 'Ajouter',
            'menu_name' => 'Logiciels',
        ),
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'mastered_technology'),
    ]);
    //  Drive licence
    register_taxonomy('drive_licence', ['jp-jobs'], [
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Permis de conduire',
            'singular_name' => 'Permis de conduire',
            'search_items' => 'Trouver',
            'all_items' => 'Trouver des permis',
            'edit_item' => 'Modifier le permis',
            'update_item' => 'Mettre à jour',
            'add_new_item' => 'Ajouter',
            'menu_name' => 'Permis de conduire',
        ),
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'drive_licence'),
    ]);
    //  Salaires
    register_taxonomy('salaries', ['jp-jobs'], [
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Salaires',
            'singular_name' => 'Salaire',
            'search_items' => 'Trouver',
            'all_items' => 'Trouver des salaires.',
            'parent_item' => 'Tech. parent',
            'parent_item_colon' => 'Tech. parent:',
            'edit_item' => 'Modifier le salaire',
            'update_item' => 'Mettre à jour',
            'add_new_item' => 'Ajouter',
            'menu_name' => 'Salaires',
        ),
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'salaries'),
    ]);
    //  Qualification
    register_taxonomy('qualification', ['jp-jobs'], [
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Qualifications',
            'singular_name' => 'Qualification',
            'search_items' => 'Trouver',
            'all_items' => 'Trouver des qualifications',
            'edit_item' => 'Modifier la qualification',
            'update_item' => 'Mettre à jour',
            'add_new_item' => 'Ajouter',
            'menu_name' => 'Qualifications',
        ),
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'qualification'),
    ]);
    //  Language
    register_taxonomy('language', ['post'], [
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Langages',
            'singular_name' => 'Langage',
            'search_items' => 'Trouver',
            'all_items' => 'Trouver des langues',
            'edit_item' => 'Modifier la langue',
            'update_item' => 'Mettre à jour',
            'add_new_item' => 'Ajouter',
            'menu_name' => 'Languages',
        ),
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'language'),
    ]);
    //  Type de travail
    register_taxonomy('job_type', ['jp-jobs'], [
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Type de contract',
            'singular_name' => 'Type de contract',
            'search_items' => 'Trouver',
            'all_items' => 'Trouver des types',
            'parent_item' => 'Tech. parent',
            'parent_item_colon' => 'Tech. parent:',
            'edit_item' => 'Modifier le type',
            'update_item' => 'Mettre à jour',
            'add_new_item' => 'Ajouter',
            'menu_name' => 'Type de contract',
        ),
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'job_type'),
    ]);
    // Pays
    register_taxonomy('country', ['post'], [
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Pays',
            'singular_name' => 'Pays',
            'search_items' => 'Trouver',
            'all_items' => 'Trouver des pays',
            'edit_item' => 'Modifier',
            'update_item' => 'Mettre à jour',
            'add_new_item' => 'Ajouter',
            'menu_name' => 'Pays',
        ),
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'country'),
    ]);
    // Region
    register_taxonomy('region', ['jp-jobs'], [
        'hierarchical' => true,
        'labels' => array(
            'name' => 'Regions',
            'singular_name' => 'Region',
            'search_items' => 'Trouver',
            'all_items' => 'Trouver des regions',
            'edit_item' => 'Modifier',
            'update_item' => 'Mettre à jour',
            'add_new_item' => 'Ajouter',
            'menu_name' => 'Regions',
        ),
        'show_ui' => true,
        'show_admin_column' => false,
        'query_var' => true,
        'public' => true,
        'show_in_rest' => true,
        'rewrite' => array('slug' => 'region'),
    ]);
});
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

    add_action('wp_ajax_nopriv_forgot_password', 'forgot_password');
    add_action('forgot_my_password', 'forgot_my_password');
    /**
     * Fonction ajax - nopriv only.
     * Envoie un email pour recuperer le mot de passe
     */
    function forgot_password() {
        if (is_user_logged_in()) {
            wp_send_json_error(["msg" => "Vous ne pouvez pas effectuer cette action"]);
        }
        $email = Http\Request::getValue('email');
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
    function forgot_my_password($email, $key) {
        global $Liquid_engine, $no_reply_email;
        $to = $email;
        $User = get_user_by('email', $to);
        $subject = "Réinitialiser votre mot de passe - JOBJIABY";
        $headers = [];
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = "From: ItJobMada <{$no_reply_email}>";
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


