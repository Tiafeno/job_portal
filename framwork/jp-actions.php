<?php
if (!defined('ABSPATH')) {
    exit;
}
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
    add_action('wp_ajax_nopriv_ajax_login', function () {
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
            wp_send_json_success('Login successful, redirecting...');
        } else {
            wp_send_json_error('Error login information');
        }
    });

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
});


