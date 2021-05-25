<?php
use Liquid\Template;
// Disable warning php error
error_reporting(E_ERROR | E_PARSE);

defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('__SITENAME__') ? null: define('__SITENAME__', 'job_portal');
// URL value
defined('_ACCOUNT_URL_') ? null: define('_ACCOUNT_URL_', DS .'mon-compte');
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
