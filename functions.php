<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/framework/cron.php';

use JP\Framework\Elements\jpCandidate;
use JP\Framework\Elements\jpJobs;
use Liquid\Template;
// Disable warning php error
error_reporting(E_ERROR | E_PARSE);

/**
 * Page path (slug) with model
 *
 * Connexion [Login]: /connexion
 * Mot de passe oublié [Forgot Password]: /forgot-password
 * Espace client [Client Page]: /espace-client
 * Page de confirmation d'enregistrement: /confirmation-register?user_id={id}
 * Candidate archives: /candidate
 * Offre archives: /emploi
 * Entreprise: /companies
 * Enregistrement: /register
 * Candidat CV: /candidate-cv?eid={employer_id}&cid={candidate_id}tk={tk}
 *
 */

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('__SITENAME__') ? null: define('__SITENAME__', 'job_portal');
// URL value
defined('_ACCOUNT_URL_') ? null: define('_ACCOUNT_URL_', DS .'espace-client');
defined('_ADD_ANNONCE_URL_') ? null: define('_ADD_ANNONCE_URL_', DS . 'add-annonce');

// Table
global $wpdb;
define('APPLY_TABLE', "{$wpdb->prefix}job_apply");
define('APPLY_PURCHASE_TABLE', "{$wpdb->prefix}job_apply_purchase");

require_once __DIR__ . '/framework/loader.php'; // Load all elements
require_once __DIR__ . '/framework/jp-migration.php';

// Gestion d'erreur
$jj_errors = [];
$jj_messages = [];

\Liquid\Liquid::set('INCLUDE_PREFIX', '');
$Liquid_engine = new Template(__DIR__ . '/templates');
$Liquid_engine->setCache(new \Liquid\Cache\Local());

/*
 * ***************************************************
 * ***************  Liquid Filters *******************
 * *************************************************
 */

$engine = &$Liquid_engine;
$engine->registerFilter('taxonomy', function($taxonomy_name) {
    $result = get_terms([
        'taxonomy' => $taxonomy_name,
        'hide_empty' => false,
        'number' => 100,
        'fields' => 'all',
    ]);
    if (is_wp_error($result) || !is_array($result)) return [];
    return $result;
});
$engine->registerFilter('get_candidate_link', function($candidate_id) {
    return home_url("/candidate/#/candidate/{$candidate_id}");
});

//*****************************************************

load_theme_textdomain(__SITENAME__, get_template_directory() . '/languages');

// Add theme.liquid file content in balise head
add_action('wp_head', function() {
    global $Liquid_engine;
    $forgot_pwd_url = home_url('/forgot-password');
    echo $Liquid_engine->parseFile('theme')->render([
        'forgot_pwd_url' => $forgot_pwd_url,
        'register_url' => home_url('/register')
    ]);
});

// Themes support and register sidebar
add_action('after_setup_theme', function () {
    /** @link https://codex.wordpress.org/Post_Thumbnails */
    add_theme_support('post-thumbnails');
    add_theme_support('category-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('custom-logo', array(
        'height' => 38,
        'width' => 150,
        'flex-width' => true,
    ));
    add_image_size('sidebar-thumb', 120, 120, true);
    add_image_size('homepage-thumb', 220, 180);
    add_image_size('singlepost-thumb', 590, 9999);
    /**
     * This function will not resize your existing featured images.
     * To regenerate existing images in the new size,
     * use the Regenerate Thumbnails plugin.
     */
    set_post_thumbnail_size(50, 50, array(
        'center',
        'center'
    )); // 50 pixels wide by 50 pixels tall, crop from the center

    // Register menu location
    register_nav_menus(array(
        'primary' => 'Menu Principal',
        'social-network' => 'Réseaux social',
    ));
    /**
     * Register sidebar for footer and ads
     */
    register_sidebar( array(
        'id' => 'footer_social',
        'name' => 'Footer Réseaux sociaux',
        'before_widget'  => '<div class="%2$s">',
        'after_widget'  => '</div>',
        'before_title' => '<h4 >',
        'after_title' => '</h4>',
    ) );
    register_sidebar( array(
        'id' => 'footer_menu',
        'name' => 'Footer Menu',
        'before_widget'  => '<div class="col-md-3 col-sm-6 %2$s">',
        'after_widget'  => '</div>',
        'before_title' => '<h4 >',
        'after_title' => '</h4>',
    ) );
});

add_action('init', function() {
    do_action('register_services');
});

// or install this plugin: https://wordpress.org/plugins/admin-bar-dashboard-control/
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
}

add_action( 'admin_init', 'allow_admin_area_to_admins_only');
function allow_admin_area_to_admins_only() {
    if( defined('DOING_AJAX') && DOING_AJAX ) {
        //Allow ajax calls
        return;
    }
    $user = wp_get_current_user();
    if( empty( $user ) || !in_array( "administrator", (array) $user->roles ) ) {
        //Redirect to main page if no user or if the user has no "administrator" role assigned
        wp_redirect( get_site_url( ) );
        exit();
    }
}

/**
 * Cette action permet de bien configurer la recherche dans
 * la page search.php
 */
add_action('pre_get_posts', function (WP_Query $query) {
    if (($query->is_search && $query->is_archive) && !is_admin() ) {
        // Spécifier pour les annonces seulement
        if ($query->get('post_type') === 'jp-jobs') {
            $query->set('post_status', ['publish']);
        }
    }
    return $query;
});

add_action( 'show_user_profile', 'user_fields' );
add_action( 'edit_user_profile', 'user_fields' );
function user_fields( $user ) {
    global $Liquid_engine;
    wp_enqueue_script('admin-user', get_stylesheet_directory_uri() . '/assets/js/admin-user.js',
        ['jquery', 'wp-api','wpapi', 'vuejs', 'medium-editor', 'alertify', 'lodash', 'axios', 'semantic']);
    wp_localize_script('admin-user', 'WPAPIUserSettings', [
        'root' => esc_url_raw(rest_url()),
        'nonce' => wp_create_nonce('wp_rest'),
        'uId' => intval($user->ID),
        'uRole' => reset($user->roles),
    ]);
    echo $Liquid_engine->parseFile('admin/extra-profil-information')->render([]);
}

add_action('admin_enqueue_scripts', function($hook) {
    global $post;

    wp_enqueue_script('alertify', get_stylesheet_directory_uri() . '/assets/plugins/alertify/alertify.min.js', ['jquery'], null, true);
    wp_enqueue_style('alertify', get_stylesheet_directory_uri() . '/assets/plugins/alertify/css/alertify.css');
    wp_enqueue_style('job-portal', get_stylesheet_directory_uri() . '/assets/css/job-portal.css', [], null);
    wp_register_script('wpapi', get_stylesheet_directory_uri() . '/assets/js/wpapi/wpapi.js', [], null, true); // dev

    if ('user-edit.php' === $hook || 'post.php' == $hook || 'post-new.php' == $hook ) {
        wp_register_script('axios', get_stylesheet_directory_uri() . '/assets/js/axios.min.js', [], null, true); // dev
        wp_register_script('medium-editor', get_stylesheet_directory_uri() . '/assets/js/vuejs/medium-editor.min.js', [], null, true); // dev
        wp_register_script('vuejs', get_stylesheet_directory_uri() . '/assets/js/vuejs/vue.js', [], '2.5.16', true); // dev
        wp_register_script('semantic', get_stylesheet_directory_uri() . '/assets/plugins/semantic-ui/semantic.min.js', ['jquery'], null, true);
        wp_enqueue_style('semantic-ui', get_stylesheet_directory_uri() . '/assets/plugins/semantic-ui/semantic.css');
    }

    if( 'post.php' == $hook || 'post-new.php' == $hook ) {
        wp_enqueue_script('admin-emploie', get_stylesheet_directory_uri() . '/assets/js/admin-emploie.js',
            ['jquery', 'wp-api','wpapi', 'vuejs', 'medium-editor', 'alertify', 'lodash', 'axios', 'semantic']);
        wp_localize_script('admin-emploie', 'WPAPIEmploiSettings', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'postId' => intval($post->ID),
        ]);
    }
}, 10);



/**
 * Permet de rendre une interface graphique dans la page admin de l'emploie
 */
add_action('admin_init', function() {
    add_meta_box('candidature', __( 'Candidature', 'textdomain' ),'candidature_view' ,
        'jp-jobs',
        'advanced',
        'high'
    );
});

function candidature_view($field) {
    /**
     * @field param
     * Array (
     * [ID] => 262
     * [key] => field_6169df19507ef
     * [label] => Information sur l'annonce
     * [name] => acf[field_6169df19507ef]
     * [prefix] => acf
     * [type] => text
     * [parent] => 261
     * [_name] => editor)
     */
    global $Liquid_engine, $wpdb, $post;
    $table = $wpdb->prefix . 'job_apply';
    // Verify if user has apply this job
    $sql = $wpdb->prepare("SELECT * FROM {$table} WHERE job_id = %d ", $post->ID);
    $apply_rows = $wpdb->get_results($sql, OBJECT);
    $candidates = [];
    foreach ($apply_rows as $row) {
        $candidate = new jpCandidate(intval($row->candidate_id));
        $candidates[] = get_object_vars($candidate);
    }
    print_r($candidates);
    echo $Liquid_engine
            ->parseFile('emploie/editor')
            ->render(['candidates' => $candidates]);
}


/*
 * ***************************************************
 * *******************  ACF Action *******************
 * ***************************************************
 */

/**
 * Cette action permet d'afficher des contenues dynamique
 * et aussi gerer les abonnements
 */
add_action('acf/render_field/name=pricing', 'acf_pricing_field');
function acf_pricing_field() {
    global $Liquid_engine;
    $app_configs = Tools::getInstance()->getSchemas();
    $pricings = $app_configs->pricing->account;
    $args = [
        'pricings' => $pricings
    ];
    echo $Liquid_engine->parseFile('pricings/pricing-layout')->render($args);
}




