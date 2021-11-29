<?php
namespace JobGrid\Widgets;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Elementor\Widget_Base;

class JobGrid_Widget extends Widget_Base
{
    public static $slug = 'job-grid';

    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
        // https://developers.elementor.com/creating-a-new-widget/adding-javascript-to-elementor-widgets/
        wp_register_script('comp-job-grid', get_stylesheet_directory_uri() . '/assets/js/components/component-job-grid.js',
            ['vuejs', 'wp-api', 'axios', 'lodash'], null, true);
    }

    public function get_script_depends() {
        wp_localize_script('comp-job-grid', 'apiSettings', [
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' )
        ]);
        return ['comp-job-grid'];
    }

    public function get_name() {
        return self::$slug;
    }

    public function get_title() {
        return 'Job Grid';
    }

    public function get_icon() {
        return 'fas fa-newspaper';
    }

    public function get_categories() {
        return ['general'];
    }

    protected function _register_controls() {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Options', self::$slug),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'title',
            [
                'label' => __('Titre', self::$slug),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Les offres d\'emploie', self::$slug),
                'placeholder' => __('Titre du widget', self::$slug),
            ]
        );
        $this->add_control(
            'description',
            [
                'label' => __('Description', self::$slug),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __("On vous aident à vous faire recruter en vous contactant lorsque les postes " .
                    "correspondent à votre profil.", self::$slug),
                'placeholder' => __('Quelque description...', self::$slug),
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
        $settings = $this->get_settings_for_display();
        echo $Liquid_engine->parseFile('job-grid')->render([
            'job_archive_url' => get_post_type_archive_link('jp-jobs'),
            'title' => $settings['title'],
            'description' => $settings['description']
        ]);
    }
}


