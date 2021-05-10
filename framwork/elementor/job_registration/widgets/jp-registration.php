<?php
namespace JobRegistration\Widgets;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Elementor\Widget_Base;

class JobRegistration_Widget extends Widget_Base {
    public static $slug = "job_registration";
    public function get_name() {
        return self::$slug;
    }

    public function get_title() {
        return 'Job Registration';
    }

    public function get_icon() {
        return 'fa fa-user';
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
            'widget_value',
            [
                'label' => __('Nom du formulaire', self::$slug),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('', self::$slug),
                'placeholder' => __('Value Attribute', self::$slug),
            ]
        );
        $this->end_controls_section();
    }

    protected function render() {
        global $Liquid_engine;

        do_action('action_jobportal_register');
        $nonce = wp_nonce_field('portaljob-register', '_wpnonce', true, false);
        echo $Liquid_engine->parseFile('job-registration')->render(['nonce' => $nonce]);
    }
}