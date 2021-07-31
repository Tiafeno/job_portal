<?php
if (!defined('ABSPATH')) {
    exit;
}

// @source: https://github.com/WP-API/rest-filte
add_action('rest_api_init', function () {
    foreach (get_post_types(array('show_in_rest' => true), 'objects') as $post_type) {
        add_filter('rest_' . $post_type->name . '_query', 'rest_api_filter_add_filter_param', 10, 2);
    }
    /**
     * Add the filter parameter
     *
     * @param array $args The query arguments.
     * @param WP_REST_Request $request Full details about the request.
     * @return array $args.
     **/
    function rest_api_filter_add_filter_param($args, $request)
    {
        // Bail out if no filter parameter is set.
        if (empty($request['filter']) || !is_array($request['filter'])) {
            return $args;
        }
        $filter = $request['filter'];
        if (isset($filter['posts_per_page']) && ((int)$filter['posts_per_page'] >= 1 && (int)$filter['posts_per_page'] <= 100)) {
            $args['posts_per_page'] = $filter['posts_per_page'];
        }
        global $wp;
        $vars = apply_filters('rest_query_vars', $wp->public_query_vars);
        // Allow valid meta query vars.
        $vars = array_unique(array_merge($vars, array('meta_query', 'meta_key', 'meta_value', 'meta_compare')));
        foreach ($vars as $var) {
            if (isset($filter[$var])) {
                $args[$var] = $filter[$var];
            }
        }
        return $args;
    }
});

// Add has_cv, public_cv api rest parameter
add_action('rest_api_init', function () {
    // Cette valeur est pour identifier s'il a déja rempli son CV
    register_meta('user', 'has_cv', [
        'type' => 'boolean',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function () {
            return true;
        }
    ]);
    // Cette valeur est pour savoir si le profil est public ou pas
    register_meta('user', 'public_cv', [
        'type' => 'boolean',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function () {
            return true;
        }
    ]);
    // Add two (2) variable in user meta_query (has_cv, public_cv)
    add_filter('rest_user_query', function ($args, $request) {
        $meta_query = ['relation' => 'AND'];
        if (isset($request['has_cv']) && !empty($request['has_cv'])) {
            $args = ['key' => 'has_cv', 'value' => intval($request['has_cv']), 'compare' => '='];
            array_push($meta_query, $args);
            unset($args);
        }
        if (isset($request['public_cv']) && !empty($request['public_cv'])) {
            $args = ['key' => 'public_cv', 'value' => intval($request['public_cv']), 'compare' => '='];
            array_push($meta_query, $args);
            unset($args);
        }
        // Recherche par metier et emploi rechercher
        if (isset($request['cat']) && !empty($request['cat'])) {
            $args = ['key' => 'categories','value' => $request['cat'],'compare' => 'LIKE'];
            array_push($meta_query, $args);
            unset($args);
        }
        if (isset($request['region']) && !empty($request['region'])) {
            $args = ['key' => 'region', 'value' => intval($request['region']), 'compare' => '='];
            array_push($meta_query, $args);
            unset($args);
        }
        // Find word in experiences, educations, bio and reference
        if (isset($request['s']) && !empty($request['s'])) {
            $s = $request['s'];
            $args = ['relation' => 'OR'];
            array_push($args, ['key' => 'reference', 'value' => $s, 'compare' => 'LIKE']);
            array_push($args, ['key' => 'profil', 'value' => $s, 'compare' => 'LIKE']);
            array_push($args, ['key' => 'experiences', 'value' => $s, 'compare' => 'LIKE']);
            array_push($args, ['key' => 'educations', 'value' => $s, 'compare' => 'LIKE']);

            array_push($meta_query, $args);
            unset($args);
        }

        $args['meta_query'] = $meta_query;
        //wp_die(print_r($args));
        return $args;
    }, 10, 2);
});

// Annonce API
add_action('rest_api_init', function () {
    $job_meta = [
        [
            'name' => 'experience',
            'type' => 'integer',
        ],
        [
            'name' => 'employer_id',
            'type' => 'integer',
        ],
        [
            'name' => 'address',
            'type' => 'string',
        ]
    ];
    foreach ($job_meta as $meta) {
        register_post_meta('jp-jobs', $meta['name'], [
            'type' => $meta['type'],
            'single' => true,
            'show_in_rest' => true,
            'auth_callback' => function () {
                return true;
            }
        ]);
    }
    // Add custom field in job post type
    register_rest_field( ['jp-jobs'], 'company', array(
        'get_callback' => function( $job_arr ) {
            $employer_id = get_post_meta($job_arr['id'], 'employer_id', true);
            $employer_id = intval($employer_id);

            $request = new WP_REST_Request();
            $request->set_param('context', 'view');

            $company_id = get_user_meta($employer_id, 'company_id', true);
            $company_id = intval($company_id);
            if (0 === $company_id) {
                return new WP_Error('rest_user_not_find',
                    "L'utilisateur est introuvable ou n'existe pas", ['status' => 500]);
            }

            $company_controller = new WP_REST_Users_Controller();
            $Company = new WP_User($company_id);

            return $company_controller->prepare_item_for_response($Company, $request)->data;

        },
        // Pour modifier, cette function reçois la valeur entier (company_id)
        'update_callback' => function( $value, $job_obj ) {
            $company_id = intval($value);
            if (0 === $company_id)
                return new WP_Error('rest_integer_failer',
                "L'indentifiant n'est pas un nombre valide", ['status' => 500]);

            $employer_id = get_post_meta($job_obj->ID, 'employer_id', true);
            $employer_id = intval($employer_id);

            $ret = update_post_meta($employer_id->ID, 'company_id', $company_id);
            if ( false === $ret ) {
                return new WP_Error(
                    'rest_updated_failed',
                    __( 'Failed to update employer id.' ),
                    array( 'status' => 500 )
                );
            }
            return true;
        }
    ) );
});

add_action('rest_api_init', function () {
    /**
     * Récuperer la liste des candidates
     */
    register_rest_route('job/v2', '/apply/(?P<id>\d+)', [
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => function (WP_REST_Request $request) {
                global $wpdb;

                // verifier si le client est connecter
                if (!is_user_logged_in()) {
                    wp_send_json_error("Veuillez vous connecter");
                }
                $current_user_id = get_current_user_id();
                $user = new WP_User($current_user_id);
                // Only candidate access for this endpoint
                if (!in_array('candidate', (array)$user->roles)) {
                    //The user haven't the "candidate" role
                    wp_send_json_error("Seul un candidate peut postuler pour cette annonce");
                }

                $job_id = intval($request['id']);
                $table = $wpdb->prefix . 'job_apply';
                // Verify if user has apply this job
                $key_check_sql = $wpdb->prepare("SELECT * FROM $table WHERE job_id = %d AND user_id = %d",
                    intval($job_id), intval($current_user_id));
                $key_check_row = $wpdb->get_results($key_check_sql);
                if ($key_check_row) {
                    wp_send_json_success("Vous avez déja postuler pour cette annonce");
                } else {
                    $request = $wpdb->insert($table, [
                        'job_id' => $job_id,
                        'user_id' => intval($current_user_id),
                    ]);
                    $wpdb->flush();
                    if ($request) {
                        wp_send_json_success("Envoyer avec succes");
                    } else {
                        wp_send_json_error("Une erreur s'est produit pendant l'opération");
                    }
                }
            },
            'permission_callback' => function ($data) {
                return current_user_can('edit_posts');
            },
            'args' => [
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ]
        ),
    ]);
    register_rest_route('job/v2', '/details/(?P<id>\d+)', [
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                global $wpdb;
                //$current_user_id = get_current_user_id();
                $job_id = intval($request['id']);
                $table = $wpdb->prefix . 'job_apply';
                // Verify if user has apply this job
                $job_sql = $wpdb->prepare("SELECT * FROM $table WHERE job_id = %d ", $job_id);
                $job_rows = $wpdb->get_results($job_sql, OBJECT);
                $results = new stdClass();
                $job = get_post((int)$job_id);
                $results->job = new stdClass();
                $results->job->title = $job->post_title;
                $results->job->id = $job->ID;
                $results->candidates = [];
                if ($job_rows) {
                    // Request params
                    $request = new WP_REST_Request();
                    $request->set_param('context', 'edit');
                    // Get candidate apply for this job
                    foreach ($job_rows as $job_row) {
                        $usr_controller = new WP_REST_Users_Controller();
                        $usr = new WP_User((int)$job_row->user_id);
                        $candidate = $usr_controller->prepare_item_for_response($usr, $request)->data;
                        $results->candidates[] = $candidate;
                    }
                }
                wp_send_json_success($results);
            },
            'permission_callback' => function ($data) {
                return current_user_can('edit_posts');
            },
            'args' => [
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ]
        ),
    ]);
    // Get user for not restriction by wordpress
    register_rest_route('job-portal', '/users/(?P<id>\d+)', [
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                $user_id = intval($request['id']);
                // Create request
                $request = new WP_REST_Request();
                $request->set_param('context', 'edit');
                // Create REST API user controller
                $user_controller = new WP_REST_Users_Controller();
                $candidate = $user_controller->prepare_item_for_response(new WP_User($user_id), $request)->data;
                // Send response data
                wp_send_json_success($candidate);

            },
            'permission_callback' => function ($data) {
                return true;
            },
            'args' => [
                'id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ]
        ),
    ]);

});