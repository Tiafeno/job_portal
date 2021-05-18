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
        wp_register_script('app-add-annonce', get_stylesheet_directory_uri() . '/assets/js/add-annonce.js',
            ['vuejs', 'wpapi'], null, true);
    }

    public function get_script_depends()
    {
        return ['app-add-annonce'];
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
        echo $Liquid_engine->parseFile('job-add-annonce')->render([]);
    }
}


