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
        'create_users' => false,
        'install_themes' => false,
    );
    add_role(
        'employer',
        'Employer',
        $employer_capabilities
    );

    // for candidate
    $candidate_capabilities = array(
        'read' => true,  // true allows this capability
        'upload_files' => true,
        'edit_posts' => true,
        'edit_users' => true,
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
    add_role(
        'candidate',
        'Candidate',
        $candidate_capabilities
    );
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
        'rest_base'       => 'emploi',
        'capability_type' => 'post',
        'map_meta_cap' => true,
        'menu_icon' => 'dashicons-media-interactive',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'custom-fields'],
        'show_in_rest' => true
    ]);
    // Logiciel maitrisés
    register_taxonomy( 'tech_mastery', [ 'jp-jobs', 'post' ], [
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => 'Mastered Technology',
            'singular_name'     => 'Mastered Tech.',
            'search_items'      => 'Trouver',
            'all_items'         => 'Trouver des tech.',
            'parent_item'       => 'Tech. parent',
            'parent_item_colon' => 'Tech. parent:',
            'edit_item'         => 'Modifier la technologie',
            'update_item'       => 'Mettre à jour',
            'add_new_item'      => 'Ajouter',
            'menu_name'         => 'Mastered Technology',
        ),
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'mastered_technology' ),
    ] );
});

/**
 * Permet d'enregistrer un utilisateur (Employer ou Candidat)
 */
add_action('action_jobportal_register', function() {
    if ( ! isset($_POST['_wpnonce']) ) return;
    if (wp_verify_nonce($_POST['_wpnonce'], 'portaljob-register')) {
        $email = is_email($_POST['email']) ? $_POST['email'] : null;
        if (is_null($email) || empty($_POST['role'])) {
            return false;
        }
        $role = esc_attr($_POST['role']); //candidate or employer
        $args = [
            'user_pass' => $_POST['password'],
            'nickname' => $email,
            'first_name' => trim($_POST['first_name']),
            'last_name' => '',
            'user_login' => $email,
            'user_email' => $email,
            'role' => $role
        ];

        // Check if user exist
        if (email_exists($email) || username_exists($email)) {
            // User exist in bdd
            $response = email_exists($email);
        } else {
            $response = wp_insert_user($args);
            if (is_wp_error($response)) {
                return false;
            }
        }

        if (!is_numeric($response)) {
            echo "Value isn't numeric";
            return false;
        }

        $user_id = $response;
        $candidate = new \JP\Framwork\Elements\jpCandidate($user_id);
        $phone_number = $_POST['phone'];
        $candidate->phones = [ $phone_number ];

        do_action('send_email_new_user', $user_id); // Envoyer le mail
    }
});

add_action('new_job', function() {

});