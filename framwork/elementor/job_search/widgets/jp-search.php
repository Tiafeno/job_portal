<?php

namespace JobSearch\Widgets;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Widget_Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
class JobSearch_Widget extends Widget_Base
{
    public static $slug = 'job-search';
    public function get_name()
    {
        return self::$slug;
    }
    public function get_title()
    {
        return 'Job Search';
    }

    /**
     * Retrieve button widget icon.
     *
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon()
    {
        return 'fas fa-newspaper';
    }

    public function get_categories()
    {
        return ['general'];
    }

    protected function _register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Options', self::$slug),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'ad_number',
            [
                'label' => __('Nombre d\'annonce', self::$slug),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 100, // Nombre d'annonce afficher par default
                'placeholder' => __('Value Attribute', self::$slug),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render button widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @access protected
     */
    protected function render()
    {
        global $Liquid_engine;
        $settings = $this->get_settings_for_display(); // return array
        // Region
        $regions = get_terms(['taxonomy' => 'region', 'hide_empty' => false, 'number' => 50]);
        // Secteur d'activité
        $categories = get_terms(['taxonomy' => 'category', 'hide_empty' => false, 'number' => 50]);
        echo $Liquid_engine->parseFile('job-search')->render(['regions' => $regions, 'categories' => $categories, 'ad_count' => $settings['ad_number']]);
    }


}