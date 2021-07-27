<?php
use Liquid\Template;
// Disable warning php error
error_reporting(E_ERROR | E_PARSE);

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('__SITENAME__') ? null: define('__SITENAME__', 'job_portal');
// URL value
defined('_ACCOUNT_URL_') ? null: define('_ACCOUNT_URL_', DS .'espace-client');
defined('_ADD_ANNONCE_URL_') ? null: define('_ADD_ANNONCE_URL_', DS . 'add-annonce');

require_once __DIR__ . '/framwork/loader.php'; // Load all elements
require_once __DIR__ . '/vendor/autoload.php';

\Liquid\Liquid::set('INCLUDE_PREFIX', '');
$Liquid_engine = new Template(__DIR__ . '/templates');
$Liquid_engine->setCache(new \Liquid\Cache\Local());

// Create filter
$Liquid_engine->registerFilter('taxonomy', function($taxonomy_name) {
    $result = get_terms([
        'taxonomy' => $taxonomy_name,
        'hide_empty' => false,
        'number' => 100,
        'fields' => 'all',
    ]);
    if (is_wp_error($result) || !is_array($result)) return [];
    return $result;
});

add_action('wp_head', function() {
    global $Liquid_engine;
    echo $Liquid_engine->parseFile('theme')->render([]);
});

load_theme_textdomain(__SITENAME__, get_template_directory() . '/languages');

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
});

add_action('init', function() {
    do_action('helper_register_jp_user_role');
    do_action('helper_register_jp_post_types');
});

//Fires on the first WP load after a theme switch if the old theme still exists.
/**
 * This action fires multiple times and the parameters differs according to the context,
 * if the old theme exists or not. If the old theme is missing, the parameter
 * will be the slug of the old theme.
 */
add_action('after_switch_theme', function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'job_apply';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		job_id bigint(20) NOT NULL,
		user_id bigint(20) NOT NULL,
		date_add DATETIME NOT NULL,
		PRIMARY KEY  `apply_id` (`ID`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
});

// or install this plugin: https://wordpress.org/plugins/admin-bar-dashboard-control/
add_action('after_setup_theme', 'remove_admin_bar');
function remove_admin_bar() {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
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

add_action('init', function () {
    if (!isset($_POST['jp-login-nonce'])) return;
    $nonce = trim($_POST['jp-login-nonce']);
    $verify_result = wp_verify_nonce($nonce, 'jp-login-action');
    if ($verify_result) {
        $remember = isset($_POST['remember']) ? true : false;
        $info = array();
        $info['user_login'] = $_POST['log'];
        $info['user_password'] = $_POST['pwd'];
        $info['remember'] = $remember;
        $user_signon = wp_signon($info, false);
        if (!is_wp_error($user_signon)) {
            // redirection dans l'espace client
            echo 'login success';
            wp_set_current_user( $user_signon->ID );
            wp_set_auth_cookie( $user_signon->ID, $remember, false );
            //do_action( 'wp_login', $user_signon->user_login );
            wp_redirect(home_url('/espace-client'));
        }
    }
});

