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

class Tools {
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


