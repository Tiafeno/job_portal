<?php
namespace JobRegistration\Widgets;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Elementor\Controls_Manager;
use Elementor\Widget_Base;

class JobRegistration_Widget extends Widget_Base {
    public static $slug = "job_registration";
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
    }

    public function get_script_depends()
    {
        wp_register_script('registration', get_stylesheet_directory_uri() . '/assets/js/component-registration.js',
            ['lodash'], null, true);
        wp_localize_script('registration', 'registerSetting', [
            'is_logged' => is_user_logged_in(),
            'espace_client' => home_url('/espace-client')
        ]);
        return ['registration'];
    }

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
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->add_control(
            'widget_value',
            [
                'label' => __('Nom du formulaire', self::$slug),
                'type' => Controls_Manager::TEXT,
                'default' => __('', self::$slug),
                'placeholder' => __('Value Attribute', self::$slug),
            ]
        );
        $this->end_controls_section();
    }

    protected function render() {
        global $Liquid_engine, $jj_errors;
        $home_url = home_url('/');
        if (is_user_logged_in()) {
            // is logged user
            echo "<p class='text-center'><a href='".$home_url."' target='_parent' class='btn btn-info'><i class='ti-back-left'></i> Page d'accueil</a> </p>";
            return;
        }
        // Nonce du formulaire d'inscription
        $nonce = wp_nonce_field('jobjiaby-register', '_wpnonce', true, false);
        $current_page_url = get_the_permalink();
        echo $Liquid_engine->parseFile('job-registration')
            ->render([
                'nonce' => $nonce,
                'action' =>  $current_page_url . '?reg=true',
                'errors' => $jj_errors,
                'form' => [
                    'role' => \jTools::getValue('role', ''),
                    'first_name' => \jTools::getValue('first_name', ''),
                    'email' => \jTools::getValue('email', ''),
                    'phone' => \jTools::getValue('phone', '')
                ]
            ]);
    }
}
