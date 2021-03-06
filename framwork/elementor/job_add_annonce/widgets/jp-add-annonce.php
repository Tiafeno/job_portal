<?php

namespace JobAddAnnonce\Widgets;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Elementor\Widget_Base;
use jobLogin\Widgets\jobLogin_Widget;

class JobAddAnnonce_Widget extends Widget_Base
{
    public static $slug = 'job-add-annonce';
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
        // https://developers.elementor.com/creating-a-new-widget/adding-javascript-to-elementor-widgets/
        wp_register_script('comp-add-annonce', get_stylesheet_directory_uri() . '/assets/js/component-add-annonce.js',
            ['comp-login', 'lodash', 'medium-editor',  'vue-router'], null, true);
        wp_enqueue_style( 'medium-editor' );
    }
    public function get_script_depends()
    {
        wp_localize_script('comp-add-annonce', 'job_handler_api', [
            'current_user_id' => intval(get_current_user_id()),
            'isLogged' => is_user_logged_in(),
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'account_url' => home_url( _ACCOUNT_URL_ )
        ]);
        return ['comp-add-annonce'];
    }
    public function get_style_depends()
    {
        return [];
    }
    public function get_name()
    {
        return self::$slug;
    }
    public function get_title()
    {
        return 'Job Add Annonce Form';
    }
    public function get_icon()
    {
        return 'fas fa-ad';
    }
    public function get_categories()
    {
        return ['general'];
    }
    
    /**
     * Render button widget output on the frontend.
     * Written in PHP and used to generate the final HTML.
     *
     * @access protected
     */
    protected function render()
    {
        global $Liquid_engine;

//        if (!is_user_logged_in()) {
//            $login_widget = new jobLogin_Widget();
//            //apply_filters( 'elementor/widget/render_content', string $widget_content, ElementorWidget_Base $this )
//            $nonce = wp_create_nonce('jp-login-action');
//            $msg = "Connectez vous avant de pouvoir publier une annonce";
//            $render = $Liquid_engine->parseFile('job-login')->render(['nonce' => $nonce, 'msg' => $msg ]);
//            echo apply_filters( 'elementor/widget/render_content', $render, $login_widget );
//            return true;
//        }

        $current_user = wp_get_current_user();
        if (in_array('employer', $current_user->roles)) {
            $company_id = (int)get_user_meta( $current_user->ID, 'company_id', true );
            $result = get_user_by( 'ID', $company_id ); // return WP_User|False
            if (!$result) {
                update_user_meta( $current_user->ID, 'company_id', 0 );
            }
        }

        // get the the role object
        $employer_role = get_role( 'employer' );
        // grant the unfiltered_html capability
        $employer_role->add_cap( 'create_users', true );
        $employer_role->add_cap( 'list_users', true );

        echo $Liquid_engine->parseFile('job-add-annonce')->render([]);
    }
}


