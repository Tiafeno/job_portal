<?php

namespace JobGridCategories\Widgets;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Widget_Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly
class JobGridCategories_Widget extends Widget_Base
{
    public static $slug = 'job-grid-categories';
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
        // https://developers.elementor.com/creating-a-new-widget/adding-javascript-to-elementor-widgets/
        wp_register_script('comp-grid-categories', get_stylesheet_directory_uri() . '/assets/js/comp-job-grid-categories.js',
            ['lodash', 'wp-api'], null, true);
    }
    public function get_script_depends() {
        wp_localize_script('comp-grid-categories', 'gridAPIHandler', [
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' ),
            'archive_categories_url' => get_taxono
        ]);
        return ['comp-grid-categories'];
    }
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
            'gc_title',
            [
                'label' => __('Title', self::$slug),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => "Recherche par secteur", // Nombre d'annonce afficher par default
                'placeholder' => __('', self::$slug),
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

        echo $Liquid_engine->parseFile('job-grid-categories')->render(
            [
                'categories' => $categories,
                'title' => $settings['gc_title'],
                'archive_url' => home_url('/emploi')
            ]);
    }


}