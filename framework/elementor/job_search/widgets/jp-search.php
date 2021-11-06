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
            'description',
            [
                'label' => __('Description', self::$slug),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => 'Ce que vous recherchez en un simple geste',
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
        global $engine;
        $settings = $this->get_settings_for_display(); // return array
        // Secteur d'activité ou la categorie de l'annonce
        $categories = get_terms(['taxonomy' => 'category', 'hide_empty' => true, 'number' => 100]);
        $type_job = get_terms(['taxonomy' => 'job_type', 'hide_empty' => true, 'number' => 100]);
        echo $engine->parseFile('job-search')->render(
            [
                'categories' => $categories, // categories
                'types' => $type_job, // Type de contract
                'description' => $settings['description'],
                'route' => [
                    'register' => home_url('/register')
                ]
            ]);
    }


}