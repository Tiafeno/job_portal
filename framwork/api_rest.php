<?php
if (!defined('ABSPATH')) {
    exit;
}

// @source: https://github.com/WP-API/rest-filte
add_action( 'rest_api_init', function() {
    foreach ( get_post_types( array( 'show_in_rest' => true ), 'objects' ) as $post_type ) {
        add_filter( 'rest_' . $post_type->name . '_query', 'rest_api_filter_add_filter_param', 10, 2 );
    }
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
} );

// Add has_cv api rest parameter
add_action('rest_api_init', function () {
    register_meta('user', 'has_cv', [
        'type' => 'boolean',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function () {
            return true;
        }
    ]);
    add_filter('rest_user_query', function ($args, $request) {
        if (isset($request['has_cv']) && !empty($request['has_cv'])) {
            $args['meta_query'][] = [
                'relation' => 'AND',
                [
                    'key' => 'has_cv',
                    'value' => (bool)$request['has_cv'],
                    'compare' => '='
                ]
            ];

        }
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
                //return current_user_can( 'edit_posts' );
                return true;
            }
        ]);
    }
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
                $current_user_id = get_current_user_id();
                $job_id = intval($request['id']);
                $table = $wpdb->prefix . 'job_apply';

                // Verify if user has apply this job
                $key_check_sql = $wpdb->prepare("SELECT * FROM $table WHERE job_id = %d AND user_id = %d",
                    intval($job_id), intval($current_user_id));
                $key_check_row = $wpdb->get_results($key_check_sql);
                if ($key_check_row) {
                    wp_send_json_success("Vous avez deja postuler pour cette annonce");
                } else {
                    $request = $wpdb->insert($table, [
                        'job_id' => $job_id,
                        'user_id' => intval($current_user_id),
                    ]);
                    $wpdb->flush();
                    if ($request) {
                        wp_send_json_success("Envoyer avec succes");
                    } else {
                        wp_send_json_error("Une erreur s'est produit pendant l'operation");
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
                $results = [];
                // Verify if user has apply this job
                $job_sql = $wpdb->prepare("SELECT * FROM $table WHERE job_id = %d ", intval($job_id));
                $job_rows = $wpdb->get_results($job_sql, OBJECT);
                if ($job_rows) {
                    // Request params
                    $request = new WP_REST_Request();
                    $request->set_param('context', 'edit');
                    // Get candidate apply for this job
                    foreach ($job_rows as $job_row) {
                        $usr_controller = new WP_REST_Users_Controller();
                        $usr = new WP_User((int)$job_row->user_id);
                        $results[] = $usr_controller->prepare_item_for_response($usr, $request)->data;
                    }
                    wp_send_json_success($results);
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

});