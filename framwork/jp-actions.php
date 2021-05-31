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

    // for company
    $company_capabilities = array(
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
        'company',
        'Company',
        $company_capabilities
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

    // Ajouter category pour le 'jp-jobs'
    register_taxonomy_for_object_type('category', 'jp-jobs');

    // Logiciel maitrisés
    register_taxonomy( 'tech_mastery', [ 'post' ], [
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => 'Logiciels',
            'singular_name'     => 'Logiciel',
            'search_items'      => 'Trouver',
            'all_items'         => 'Trouver des tech.',
            'parent_item'       => 'Tech. parent',
            'parent_item_colon' => 'Tech. parent:',
            'edit_item'         => 'Modifier la technologie',
            'update_item'       => 'Mettre à jour',
            'add_new_item'      => 'Ajouter',
            'menu_name'         => 'Logiciels',
        ),
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'mastered_technology' ),
    ]);

    //  Salaires
    register_taxonomy( 'salaries', [ 'jp-jobs' ], [
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => 'Salaires',
            'singular_name'     => 'Salaire',
            'search_items'      => 'Trouver',
            'all_items'         => 'Trouver des salaires.',
            'parent_item'       => 'Tech. parent',
            'parent_item_colon' => 'Tech. parent:',
            'edit_item'         => 'Modifier le salaire',
            'update_item'       => 'Mettre à jour',
            'add_new_item'      => 'Ajouter',
            'menu_name'         => 'Salaires',
        ),
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'salaries' ),
    ]);

    //  Qualification
    register_taxonomy( 'qualification', [ 'jp-jobs' ], [
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => 'Qualifications',
            'singular_name'     => 'Qualification',
            'search_items'      => 'Trouver',
            'all_items'         => 'Trouver des qualifications',
            'edit_item'         => 'Modifier la qualification',
            'update_item'       => 'Mettre à jour',
            'add_new_item'      => 'Ajouter',
            'menu_name'         => 'Qualifications',
        ),
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'qualification' ),
    ]);

    //  Type de travail
    register_taxonomy( 'job_type', [ 'jp-jobs' ], [
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => 'Type de travail',
            'singular_name'     => 'Type de travail',
            'search_items'      => 'Trouver',
            'all_items'         => 'Trouver des types',
            'parent_item'       => 'Tech. parent',
            'parent_item_colon' => 'Tech. parent:',
            'edit_item'         => 'Modifier le type',
            'update_item'       => 'Mettre à jour',
            'add_new_item'      => 'Ajouter',
            'menu_name'         => 'Type de travail',
        ),
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'job_type' ),
    ]);

    // Pays
    register_taxonomy( 'country', [ 'post' ], [
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => 'Pays',
            'singular_name'     => 'Pays',
            'search_items'      => 'Trouver',
            'all_items'         => 'Trouver des pays',
            'edit_item'         => 'Modifier',
            'update_item'       => 'Mettre à jour',
            'add_new_item'      => 'Ajouter',
            'menu_name'         => 'Pays',
        ),
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'country' ),
    ] );

    // Pays
    register_taxonomy( 'region', [ 'jp-jobs' ], [
        'hierarchical'      => true,
        'labels'            => array(
            'name'              => 'Regions',
            'singular_name'     => 'Region',
            'search_items'      => 'Trouver',
            'all_items'         => 'Trouver des regions',
            'edit_item'         => 'Modifier',
            'update_item'       => 'Mettre à jour',
            'add_new_item'      => 'Ajouter',
            'menu_name'         => 'Regions',
        ),
        'show_ui'           => true,
        'show_admin_column' => false,
        'query_var'         => true,
        'public'            => true,
        'show_in_rest'      => true,
        'rewrite'           => array( 'slug' => 'region' ),
    ] );
});


add_action('init', function() {
    // Permet de se connecter avec AJAX
    add_action('wp_ajax_nopriv_ajax_login', 'ajax_login');
    function ajax_login() {
        // First check the nonce, if it fails the function will break
        check_ajax_referer( 'ajax-login-nonce', 'security' );

        // Nonce is checked, get the POST data and sign user on
        $info = array();
        $info['user_login'] = $_POST['username'];
        $info['user_password'] = $_POST['password'];
        $info['remember'] = true;

        $user_signon = wp_signon( $info, false );
        if ( !is_wp_error($user_signon) ){
            wp_set_current_user($user_signon->ID);
            wp_set_auth_cookie($user_signon->ID);
            wp_send_json_success('Login successful, redirecting...');
        } else {
            wp_send_json_error('Error login information');
        }
    }
});

// @source: https://github.com/WP-API/rest-filte
add_action( 'rest_api_init', function() {
    foreach ( get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
        add_filter( 'rest_' . $post_type->name . '_query', 'rest_api_filter_add_filter_param', 10, 2 );
    }
} );

/**
 * Add the filter parameter
 *
 * @param  array           $args    The query arguments.
 * @param  WP_REST_Request $request Full details about the request.
 * @return array $args.
 **/
function rest_api_filter_add_filter_param( $args, $request ) {
    // Bail out if no filter parameter is set.
    if ( empty( $request['filter'] ) || ! is_array( $request['filter'] ) ) {
        return $args;
    }
    $filter = $request['filter'];
    if ( isset( $filter['posts_per_page'] ) && ( (int) $filter['posts_per_page'] >= 1 && (int) $filter['posts_per_page'] <= 100 ) ) {
        $args['posts_per_page'] = $filter['posts_per_page'];
    }
    global $wp;
    $vars = apply_filters( 'rest_query_vars', $wp->public_query_vars );
    // Allow valid meta query vars.
    $vars = array_unique( array_merge( $vars, array( 'meta_query', 'meta_key', 'meta_value', 'meta_compare' ) ) );
    foreach ( $vars as $var ) {
        if ( isset( $filter[ $var ] ) ) {
            $args[ $var ] = $filter[ $var ];
        }
    }
    return $args;
}