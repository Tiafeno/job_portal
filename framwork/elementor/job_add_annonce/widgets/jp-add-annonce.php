<?php

namespace JobAddAnnonce\Widgets;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Elementor\Widget_Base;

class JobAddAnnonce_Widget extends Widget_Base
{
    public static $slug = 'job-add-annonce';
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
        // https://developers.elementor.com/creating-a-new-widget/adding-javascript-to-elementor-widgets/
        wp_register_script('comp-add-annonce', get_stylesheet_directory_uri() . '/assets/js/component-add-annonce.js',
            ['comp-login', 'lodash', 'medium-editor'], null, true);
        wp_enqueue_style( 'medium-editor' );
    }

    public function get_script_depends()
    {
        wp_localize_script('comp-add-annonce', 'job_handler_api', [
            'current_user_id' => intval(get_current_user_id()),
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
        $current_user = wp_get_current_user();
        if (in_array('employer', $current_user->roles)) {
            $company_id = (int)get_user_meta( $current_user->ID, 'company_id', true );
            $result = get_user_by( 'ID', $company_id ); // return WP_User|False
            if (!$result) {
                update_user_meta( $current_user->ID, 'company_id', 0 );
            }
        } else {
            echo '<div class="alert alert-danger" role="alert">Vous n\'avez pas l\'autorisation 
necessaire pour consulter cette page</div>';
            return;
        }

        // get the the role object
        $employer_role = get_role( 'employer' );
        // grant the unfiltered_html capability
        $employer_role->add_cap( 'create_users', true );
        $employer_role->add_cap( 'list_users', true );

        echo $Liquid_engine->parseFile('job-add-annonce')->render([]);
    }
}


