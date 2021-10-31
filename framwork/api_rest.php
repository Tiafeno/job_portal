<?php

use JP\Framwork\Elements\jpCandidate as jpCandidateAlias;

if (!defined('ABSPATH')) {
    exit;
}
include_once 'api-rest-action.php'; // Tous les actions dans l'API REST

function send_rest_user(WP_REST_Request $request) {
    $user_id = intval($request->get_param('user_id'));

    // Create request
    $req = new WP_REST_Request();
    $req->set_param('context', 'edit');

    // Create REST API user controller
    $user_controller = new WP_REST_Users_Controller();
    $candidate = $user_controller->prepare_item_for_response(new WP_User($user_id), $req)->data;
    // Send response data
    wp_send_json_success($candidate);
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
add_action('rest_api_init', function () {
// Add two (2) variable in user meta_query (has_cv, public_cv)
    add_filter('rest_user_query', function ($args, $request) {
        $meta_query = ['relation' => 'AND'];
        // Rechercher si l'entreprise ou l'employer est actif
        if (isset($request['is_active']) && !empty($request['is_active'])) {
            $args = ['key' => 'is_active', 'value' => intval($request['is_active']), 'compare' => '='];
            array_push($meta_query, $args);
            unset($args);
        }
        // Rechercher si le CV existe
        if (isset($request['has_cv']) && !empty($request['has_cv'])) {
            $args = ['key' => 'has_cv', 'value' => intval($request['has_cv']), 'compare' => '='];
            array_push($meta_query, $args);
            unset($args);
        }
        // also is_active
        if (isset($request['public_cv']) && !empty($request['public_cv'])) {
            $args = ['key' => 'is_active', 'value' => intval($request['public_cv']), 'compare' => '='];
            array_push($meta_query, $args);
            unset($args);
        }
        // Recherche par metier et emploi rechercher
        if (isset($request['cat']) && !empty($request['cat'])) {
            $args = ['key' => 'categories','value' => $request['cat'],'compare' => 'LIKE'];
            array_push($meta_query, $args);
            unset($args);
        }
        // Recherche de region
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
        return $args;
    }, 10, 2);

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

    // Postuler pour un offre et recuperer la liste des candidates qui ont postuler...
    register_rest_route('job/v2', '/(?P<job_id>\d+)/apply', [
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                global $wpdb;
                //$current_user_id = get_current_user_id();
                $job_id = intval($request['job_id']);
                $job = get_post($job_id);
                $table = $wpdb->prefix . 'job_apply';
                // Verify if user has apply this job
                $job_sql = $wpdb->prepare("SELECT * FROM $table WHERE job_id = %d ", $job_id);
                $job_rows = $wpdb->get_results($job_sql, OBJECT);
                // Request params
                $request = new WP_REST_Request();
                $request->set_param('context', 'view');
                // Create results object
                $results = new stdClass();
                $results->job = new stdClass();
                $results->job->id = $job->ID;
                $results->job->title = $job->post_title;
                $results->candidates = [];
                if ($job_rows) {
                    // Get candidate apply for this job
                    foreach ($job_rows as $job_row) {
                        $usr_controller = new WP_REST_Users_Controller();
                        $usr = new WP_User((int)$job_row->candidate_id);
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
    register_rest_route('job/v2', '/(?P<job_id>\d+)/purchase/(?P<id_candidate>\d+)', [
        [
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => function (WP_REST_Request $request) {
                // TODO: Ajouter une fonctionnalité pour qu'un employeur achete un CV dans l'espace client
            },
            'permission_callback' => function ($data) {
                return current_user_can('edit_posts');
            },
            'args' => [
                'id_apply' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'id_candidate' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ]
        ]
    ]);
    // Recuperer le candidate
    register_rest_route('job/v2', '/candidate/(?P<id_candidate>\d+)', [
        [
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                $candidate_id = intval($request->get_param('id_candidate'));
                // Create request
                $req = new WP_REST_Request();
                $req->set_param('context', 'edit');
                // Create REST API user controller
                $user_controller = new WP_REST_Users_Controller();
                $candidate = $user_controller->prepare_item_for_response(new WP_User($candidate_id), $req)->data;
                // Send response data
                $encode = json_encode($candidate);
                wp_send_json(base64_encode($encode));
            },
            'permission_callback' => function ($data) {
                return true;
            },
            'args' => [
                'id_candidate' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ]
        ]
    ]);
    // Recuperer les entreprises
    register_rest_route('job/v2', '/companies', [
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                $page = isset($_GET['paged']) ? intval($_GET['paged']) : 0;
                $companies = [];
                $company_query = new WP_User_Query( array(
                    'number' => 10,
                    'role' => 'company',
                    'role__not_in' => 'Administrator'
                ) );
                $rest_request = new WP_REST_Request();
                $rest_request->set_param('context', 'view');
                foreach ($company_query->get_results() as $company) {
                    $comp_controller = new WP_REST_Users_Controller();
                    $companies[] = $comp_controller->prepare_item_for_response(new WP_User($company->ID), $rest_request)->data;
                }
                wp_send_json([
                    'total' => $company_query->get_total(),
                    'data' => $companies
                ]);
            },
            'permission_callback' => function ($data) {
                return true;
            }
        ),
    ]);
    // Recuperer une entreprise
    register_rest_route('job/v2', '/companies/(?P<company_id>\d+)', [
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                $object_id = $request->get_param('company_id');
                $object_id = intval($object_id);
                if (\JP\Framwork\Elements\jpCompany::is_company($object_id)) {
                    $request = new WP_REST_Request();
                    $request->set_param('user_id', $object_id);
                    send_rest_user($request);
                    return false;
                } else {
                    wp_send_json_error("L'indentifiant n'est pas une entreprise ou une société existante");
                }
            },
            'permission_callback' => function ($data) {
                return true;
            },
            'args' => [
                'company_id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ]
        ),
    ]);
    register_rest_route('job/v2', '/companies/(?P<company_id>\d+)/employer', [
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                $object_id = $request->get_param('company_id');
                $object_id = intval($object_id);
                if (\JP\Framwork\Elements\jpCompany::is_company($object_id)) {
                    global $wpdb;
                    /**
                     * Recuperer l'employer de cette entreprise, pour être utiliser dans la page entreprise
                     * (Afficher les offres publier par cette entreprise ou l'employer)
                     **/
                    $req = "SELECT umeta.user_id as employer_id FROM $wpdb->usermeta as umeta WHERE umeta.meta_key = %s AND umeta.meta_value LIKE %s ";
                    $query_result = $wpdb->get_var($wpdb->prepare($req, 'company_id', $object_id));
                    if (is_null($query_result)) wp_send_json_error("Compte employer introuvable");
                    $employer_id = intval($query_result);
                    $user_controller = new WP_REST_Users_Controller();
                    $_request = new WP_REST_Request();
                    $_request->set_param('context', 'view');
                    wp_send_json_success($user_controller->prepare_item_for_response(new WP_User($employer_id), $_request)->data);
                } else {
                    wp_send_json_error("L'indentifiant n'est pas une entreprise ou une société existante");
                }
            },
            'permission_callback' => function ($data) {
                return true;
            },
            'args' => [
                'company_id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
            ]
        ),
    ]);
    register_rest_route('job/v2', '/pricing', [
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                $configs = Tools::getInstance()->get_app_configs();
                $account_pricing = $configs->pricing->account;
                wp_send_json($account_pricing);
            },
            'permission_callback' => function ($data) {
                return true;
            },
        ),
    ]);
    register_rest_route('job/v2', '/cv-status', [
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => function (WP_REST_Request $request) {
                $configs = Tools::getInstance()->get_app_configs();
                $status = $configs->candidat_status;
                wp_send_json($status);
            },
            'permission_callback' => function ($data) {
                return true;
            },
        ),
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => function () {
                $user_id = empty($_POST['uid']) ? 0 : intval($_POST['uid']);
                $value = intval($_POST['val']);
                if (0 === $user_id) wp_send_json_error("Erreur de requete");
                $response = update_metadata('user', $user_id, 'cv_status', $value);
                if ($response) {
                    wp_send_json_success("Status mis a jour avec succes");
                }
                wp_send_json_error("Une erreur s'est produit");
            },
            'permission_callback' => function ($data) {
                return is_user_logged_in();
            },
        ),
    ]);
    register_rest_route('pay/v2', '/pricing/(?P<product_id>\d+)/(?P<employer_id>\d+)/(?P<ref>\w+)', [
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => function (WP_REST_Request $request) {
                $resp = null;
                /**
                 * ref:
                 *  - follow: Le paiement suras effectuer dans le front office
                 *  - through: Paiement directement effectuer (Administrateur seulement)
                 */
                $ref = esc_attr($request->get_param('ref'));

                $employer_id = intval($request->get_param('employer_id'));
                $product_id = intval($request->get_param('product_id'));
                $checkout_url = get_permalink(wc_get_page_id('cart'));
                switch ($ref):
                    case 'through':
                        $args = array(
                            'status'        => 'pending', // En attente
                            'customer_id'   => $employer_id,
                            'customer_note' => "",
                            'parent'        => null,
                            'created_via'   => null,
                            'cart_hash'     => null,
                        );
                        $product = wc_get_product( $product_id );
                        $order = wc_create_order($args);
                        // Add address
                        $billing_address = array(
                            'country' => 'US',
                            'first_name' => 'Jeroen',
                            'last_name' => 'Sormani',
                            'company' => 'WooCompany',
                            'address_1' => 'WooAddress',
                            'address_2' => '',
                            'postcode' => '123456',
                            'city' => 'WooCity',
                            'state' => 'NY',
                            'email' => 'admin@example.org',
                            'phone' => '555-32123'
                        );
                        $order->set_address($billing_address, 'billing');
                        // Add product
                        $order->add_product($product, 1, []);
                        // Set payment gateway
                        $payment_gateways = WC()->payment_gateways->payment_gateways();
                        $order->set_payment_method($payment_gateways['bacs']);

                        $order->set_total(0, 'shipping');
                        $order->set_total(0, 'cart_discount');
                        $order->set_total(0, 'cart_discount_tax');
                        $order->set_total(0, 'tax');
                        $order->set_total(0, 'shipping_tax');

                        // Tu doit specifier la valeur total du commande
                        //$order->set_total(40, 'total');

                        $req = new WP_REST_Request();
                        $req->set_param('context', 'view');

                        $order_controller = new WC_REST_Orders_V2_Controller();
                        $data = $order_controller->prepare_object_for_response(wc_get_order($order->get_id()), $req)->data;
                        $resp = [
                            'type' => 'through',
                            'response' => $data
                        ];
                        break;
                    default:
                        WC()->frontend_includes();
                        WC()->session = new WC_Session_Handler();
                        WC()->session->init();
                        WC()->customer = new WC_Customer( $employer_id, true );
                        WC()->cart = new WC_Cart();
                        WC()->cart->empty_cart(); // Clear cart
                        WC()->cart->add_to_cart($product_id, 1); // Add new product in cart
                        // https://docs.woocommerce.com/wc-apidocs/function-wc_get_page_id.html
                        $resp = [
                            'type' => 'default',
                            'redirect_url' => $checkout_url
                        ];
                    endswitch;

                wp_send_json($resp);
            },
            'permission_callback' => function ($data) {
                return is_user_logged_in() && current_user_can('edit_users');
            },
            'args' => [
                'product_id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'user_id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'ref' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return !empty($param);
                    }
                ),
            ]
        ),
    ]);
    register_rest_route('pay/v2', '/(?P<type>\w+)/(?P<object_id>\d+)/(?P<customer_id>\d+)', [
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => function (WP_REST_Request $request) {
                $response = null;
                $price = 0;
                /**
                 * type:
                 *  - ad-job: Acheter une position (pub) pour une annonce
                 *  - ad-candidate: Acheter une position (pub) pour un CV
                 *  - cv: Acheter un candidate qui a postuler dans une annonce
                 */
                $type = esc_attr($request->get_param('type'));
                $customer_id = intval($request->get_param('customer_id'));
                $object_id = intval($request->get_param('object_id'));
                $product_title = null;
                $configs = Tools::getInstance()->get_app_configs();
                $date = new DateTime();
                switch ($type) {
                    case 'account':
                        $pricing_account = $configs->pricing->account;
                        $abonnement = apiHelper::getAccountPricingByID($object_id, $pricing_account);
                        if (!$abonnement) wp_send_json_error("Abonnement introuvable");
                        $price = $abonnement->regular_price;
                        $product_title = "PRODUCT#{$date->getTimestamp()} - {$abonnement->title} - {$customer_id}";
                        break;
                    case 'ad-job':
                        break;
                    case 'ad-candidate':
                        break;
                    case 'cv':
                        $candidate = new jpCandidateAlias($object_id);
                        $product_title = "CV_". $candidate->display_name . "_" . $customer_id;
                        break;
                    default:
                        wp_send_json_error("Type de demande non definie");
                        break;
                }

                $result = wp_insert_post([
                    'post_status' => 'publish',
                    'post_type' => 'product',
                    'post_title' => $product_title,
                    'post_author' => $customer_id
                ], true);

                if (is_wp_error($result)) { return false; }
                $product_id = $result;
                $product = new \WC_Product($product_id);
                $product->set_price($price);
                $product->set_sold_individually(true);
                $product->set_regular_price($price);
                $product->set_sku("ACHAT-{$type}-{$product_id}");
                // Ajouter des meta data
                $product->add_meta_data('customer_id', $customer_id);
                $product->add_meta_data('purchase_type', $type);
                // Enregistrer
                $product->save();
                // Ajouter dans le panier
                WC()->frontend_includes();
                WC()->session = new WC_Session_Handler();
                WC()->session->init();
                WC()->customer = new WC_Customer( $customer_id, true );
                WC()->cart = new WC_Cart();
                WC()->cart->empty_cart(); // Clear cart
                WC()->cart->add_to_cart($product_id, 1); // Add new product in cart
                // https://docs.woocommerce.com/wc-apidocs/function-wc_get_page_id.html
                $checkout = get_permalink(wc_get_page_id('cart'));
                wp_send_json_success(['type' => $type, 'redirect_url' => $checkout]);
            },
            'permission_callback' => function ($data) {
                return is_user_logged_in() && current_user_can('edit_users');
            },
            'args' => [
                'object_id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'customer_id' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    }
                ),
                'type' => array(
                    'validate_callback' => function ($param, $request, $key) {
                        return !empty($param);
                    }
                ),
            ]
        ),
    ]);
    // Get user for not restriction by wordpress
    register_rest_route('job-portal', '/users/(?P<user_id>\d+)', [
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'send_rest_user',
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

/**
 * Meta and fields
 */
add_action('rest_api_init', function() {
    // Annnce or jobs
    register_rest_field( ['jp-jobs'], 'company', array(
        'get_callback' => function( $job_arr ) {
            $employer_id = (int)get_post_meta($job_arr['id'], 'employer_id', true);
            $request = new WP_REST_Request();
            $request->set_param('context', 'view');
            $company_id = get_user_meta($employer_id, 'company_id', true);
            if (0 === (int)$company_id || !$company_id)  return 0;
            $company_controller = new WP_REST_Users_Controller();
            $Company = new WP_User((int)$company_id);
            return $company_controller->prepare_item_for_response($Company, $request)->data;
        },
        // Pour modifier, cette function reçois la valeur entier (company_id)
        // 'update_callback' => function( $value, $job_obj ) {
        //     $company_id = intval($value);
        //     if (0 === $company_id)
        //         return new WP_Error('rest_integer_failer',
        //             "L'indentifiant n'est pas un nombre valide", ['status' => 500]);
        //     $employer_id = get_post_meta($job_obj->ID, 'employer_id', true);
        //     $employer_id = intval($employer_id);
        //     $ret = update_post_meta($employer_id->ID, 'company_id', $company_id);
        //     if ( false === $ret ) {
        //         return new WP_Error(
        //             'rest_updated_failed',
        //             __( 'Failed to update company id.' ),
        //             array( 'status' => 500 )
        //         );
        //     }
        //     return true;
        // }
    ) );
    register_rest_field( ['jp-jobs'], 'employer', array(
        'get_callback' => function( $job_arr ) {
            $employer_id = get_post_meta($job_arr['id'], 'employer_id', true);
            return intval($employer_id);
        },
        // Pour modifier, cette function reçois la valeur entier (company_id)
        'update_callback' => function( $value, $job_obj ) {
            $employer_id = intval($value);
            if (0 === $employer_id)
                return new WP_Error('rest_integer_failer',
                    "L'indentifiant n'est pas un nombre valide", ['status' => 500]);
            update_post_meta($job_obj->ID, 'employer_id', $employer_id);
            return true;
        }
    ) );
    // Avatar
    register_rest_field('user', 'avatar', [
        'get_callback' => function($user_arr) {
            $user_id = intval($user_arr['id']);
            $id = get_metadata('user', $user_id, 'avatar_id', true);
            $id = intval($id);
            if (0 === $id) return '';
            $attach = wp_get_attachment_metadata($id);
            return ['attach_id' => $id ,'image' => $attach, 'upload_dir' => wp_upload_dir()];
        },
        'update_callback' => function($value, $user_obj) {
            return update_user_meta($user_obj->ID, 'avatar_id', intval($value));
        }
    ]);
    /**
     * Cette champ permet de verifier si l'employer ou l'entreprise est validé ou pas.
     * Il est aussi utiliser poour activer ou désactiver un utilisateur (employer ou candidat)
     */
    register_rest_field('user', 'is_active', [
        'get_callback' => function($user_arr) {
            $user_id = intval($user_arr['id']);
            $is_active = get_metadata('user', $user_id, 'is_active', true);
            return boolval($is_active);
        },
        'update_callback' => function($value, $user_obj) {
            return update_user_meta($user_obj->ID, 'is_active', intval($value));
        }
    ]);
    register_rest_field('user', 'has_cv', [
        'get_callback' => function($user_arr) {
            $user_id = intval($user_arr['id']);
            $user_object = new WP_User($user_id);
            $roles = $user_object->roles;
            if (!in_array('candidate', $roles)) return false;
            $has_cv = get_metadata('user', $user_id, 'has_cv');
            return boolval($has_cv);
        },
        'update_callback' => function($value, $user_obj) {
            return update_user_meta($user_obj->ID, 'has_cv', $value);
        }
    ]);
    register_rest_field('user', 'cv_status', [
        'get_callback' => function($user_arr) {
            $user_id = intval($user_arr['id']);
            $user_object = new WP_User($user_id);
            $roles = $user_object->roles;
            if (!in_array('candidate', $roles)) return 0;
            $is_active = get_metadata('user', $user_id, 'cv_status', true);
            return intval($is_active);
        },
        'update_callback' => function($value, $user_obj) {
            return update_user_meta($user_obj->ID, 'cv_status', intval($value));
        }
    ]);
    // Experiences du candidat
    register_rest_field('user', 'experiences', [
        'get_callback' => function($user_arr) {
            $user_id = intval($user_arr['id']);
            $user_object = new WP_User($user_id);
            $roles = $user_object->roles;
            if (!in_array('candidate', $roles)) return false;
            $experiences = get_metadata('user', $user_id, 'experiences', true);
            $experiences = empty($experiences) ? json_encode([]) : json_decode($experiences);
            return $experiences;
        },
        'update_callback' => function($value, $user_obj) {
            // $value - Cette valeur est déja encodé en JSON
            return update_user_meta($user_obj->ID, 'experiences', $value);
        }
    ]);
    // Parcours scolaire ou formations du candidat
    register_rest_field('user', 'educations', [
        'get_callback' => function($user_arr) {
            $user_id = intval($user_arr['id']);
            $user_object = new WP_User($user_id);
            $roles = $user_object->roles;
            if (!in_array('candidate', $roles)) return false;
            $educations = get_metadata('user', $user_id, 'educations', true);
            $educations = empty($educations) ? json_encode([]) : json_decode($educations);
            return $educations;
        },
        'update_callback' => function($value, $user_obj) {
            // $value - Cette valeur est déja encodé en JSON
            return update_user_meta($user_obj->ID, 'educations', $value);
        }
    ]);
    // Cette valeur est pour identifier s'il a déja rempli son CV
    register_meta('user', 'has_cv', [
        'type' => 'boolean',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function () {
            return true;
        }
    ]);
    // Annonce
    // Additional params at api_rest line 104
    register_meta('user', 'experience', [ // sans 's'
        'type' =>  'integer',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);

    //Entreprise....
    register_meta('user', 'country', [
        'type' =>  'integer',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'category', [
        'type' =>  'integer',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'employees', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'zipcode', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'website', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'nif', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return is_user_logged_in(  );
        }
    ]);
    register_meta('user', 'stat', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return is_user_logged_in(  );
        }
    ]);
    register_meta('user', 'employer_id', [
        'type' =>  'integer',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);

    // Employer....
    register_meta('user', 'company_id', [
        'type' =>  'integer',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);

    // Candidate....
    register_meta('user', 'reference', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'region', [
        'type' =>  'integer',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'status', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'phone', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
    ]);
    register_meta('user', 'address', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'city', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'categories', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'gender', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'birthday', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'profil', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'drive_licences', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'languages', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'educations', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);
    register_meta('user', 'experiences', [
        'type' =>  'string',
        'single' => true,
        'show_in_rest' => true,
        'auth_callback' => function() {
            return true;
        }
    ]);

    // https://github.com/WP-API/rest-filter/blob/master/plugin.php
    /**
     * adds a "filter" query parameter to API post collections to filter returned results
     * based on public WP_Query parameters, adding back the "filter" parameter
     * that was removed from the API when it was merged into WordPress core.
     */
});