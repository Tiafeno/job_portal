<?php

namespace JobGridCategories\Widgets;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Widget_Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
class JobGridCategories_Widget extends Widget_Base
{
    public static $slug = 'job-grid-categories';
    public function get_name() {
        return self::$slug;
    }

    public function get_title() {
        return 'Job grid by categories';
    }

    public function get_icon() {
        return 'fas fa-newspaper';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {
        // Tab controls section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Options', self::$slug),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        // Fields control
        $this->add_control(
            'title',
            [
                'label' => __('Title', self::$slug),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => 100, // Nombre d'annonce afficher par default
                'placeholder' => __('Recherche par secteur', self::$slug),
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        global $Liquid_engine;
        $settings = $this->get_settings_for_display(); // return array
        // Secteur d'activité
        $categories = get_terms(['taxonomy' => 'category', 'hide_empty' => false, 'number' => 100]);
        echo $Liquid_engine->parseFile('job-grid-categories')->render([ 'categories' => $categories, 'title' => $settings['title']]);
    }


}