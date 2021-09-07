<?php
if (!defined('ABSPATH')) {
    exit;
}

trait Configs {
    public function get_app_configs() {
        $directory = trailingslashit( get_template_directory_uri() );
        // Make the request
        $request = wp_remote_get( $directory . 'configs/schema.json' );
        // If the remote request fails, wp_remote_get() will return a WP_Error, so let’s check if the $request variable is an error:
        if( is_wp_error( $request ) ) {
            return false; // Bail early
        }
        // Retrieve the data
        return json_decode( wp_remote_retrieve_body( $request ) );
    }
}

class jpHelpers {
    use Configs;
    public function __construct() {}
    public static function getInstance() {
        return new self();
    }
    public static function getValue( $name, $def = false ) {
        if ( ! isset( $name ) || empty( $name ) || ! is_string( $name ) ) {
            return $def;
        }
        $returnValue = isset( $_POST[ $name ] ) ? trim( $_POST[ $name ] ) : ( isset( $_GET[ $name ] ) ? trim( $_GET[ $name ] ) : $def );
        $returnValue = urldecode( preg_replace( '/((\%5C0+)|(\%00+))/i', '', urlencode( $returnValue ) ) );
        return ! is_string( $returnValue ) ? $returnValue : stripslashes( $returnValue );
    }
    public function get_app_configs() {
        $directory = trailingslashit( get_template_directory_uri() );
        $url = $directory . 'configs/schema.json';
        // Make the request
        $request = wp_remote_get( $url );
        // If the remote request fails, wp_remote_get() will return a WP_Error, so let’s check if the $request variable is an error:
        if( is_wp_error( $request ) ) {
            return false; // Bail early
        }
        // Retrieve the data
        $body = wp_remote_retrieve_body( $request );
        $data = json_decode( $body );
        return $data;
    }
}

add_action('admin_enqueue_scripts', function($hook) {
    wp_enqueue_script('alertify', get_stylesheet_directory_uri() . '/assets/plugins/alertify/alertify.min.js', ['jquery'], null, true);
    wp_enqueue_style('alertify', get_stylesheet_directory_uri() . '/assets/plugins/alertify/css/alertify.css');
    wp_enqueue_style('job-portal', get_stylesheet_directory_uri() . '/assets/css/job-portal.css', [], null);
    wp_register_script('wpapi', get_stylesheet_directory_uri() . '/assets/js/wpapi/wpapi.js', [], null, true); // dev
    if ('user-edit.php' === $hook) {
        wp_register_script('axios', get_stylesheet_directory_uri() . '/assets/js/axios.min.js', [], null, true); // dev
        wp_register_script('medium-editor', get_stylesheet_directory_uri() . '/assets/js/vuejs/medium-editor.min.js', [], null, true); // dev
        wp_register_script('vuejs', get_stylesheet_directory_uri() . '/assets/js/vuejs/vue.js', [], '2.5.16', true); // dev
        wp_register_script('semantic', get_stylesheet_directory_uri() . '/assets/plugins/semantic-ui/semantic.min.js', ['jquery'], null, true);
        wp_enqueue_style('semantic-ui', get_stylesheet_directory_uri() . '/assets/plugins/semantic-ui/semantic.css');
    }
}, 10);

//function save_profile_fields( $user_id ) {
//    if ( !current_user_can( 'edit_user', $user_id ) ) :
//        return false;
//    endif;
//    $public_cv = jpHelpers::getValue('public_cv') ? jpHelpers::getValue('public_cv') : 0;
//    update_user_meta( $user_id, 'phone', jpHelpers::getValue('phone', '') );
//    update_user_meta( $user_id, 'address', jpHelpers::getValue('address', '') );
//    update_user_meta( $user_id, 'city', jpHelpers::getValue('city', '') );
//    update_user_meta( $user_id, 'public_cv', intval($public_cv));
//}
//add_action( 'personal_options_update', 'save_profile_fields' );
//add_action( 'edit_user_profile_update', 'save_profile_fields' );

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