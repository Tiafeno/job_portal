<?php
namespace JobRegistration\Widgets;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use JP\Framwork\Elements\jpCandidate;

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
        global $Liquid_engine;

        do_action('action_jobportal_register');
        $nonce = wp_nonce_field('portaljob-register', '_wpnonce', true, false);
        echo $Liquid_engine->parseFile('job-registration')->render(['nonce' => $nonce]);
    }
}

/**
 * Permet d'enregistrer un utilisateur (Employer ou Candidat)
 */
add_action('action_jobportal_register', function() {
    if ( ! isset($_POST['_wpnonce']) ) return;
    if (wp_verify_nonce($_POST['_wpnonce'], 'portaljob-register')) {
        $email = is_email($_POST['email']) ? $_POST['email'] : null;
        if (is_null($email) || empty($_POST['role'])) {
            return false;
        }
        $role = esc_attr($_POST['role']); //candidate or employer
        $args = [
            'user_pass' => $_POST['password'],
            'nickname' => $email,
            'first_name' => trim($_POST['first_name']),
            'last_name' => '',
            'user_login' => $email,
            'user_email' => $email,
            'role' => $role
        ];

        // Check if user exist
        if (email_exists($email) || username_exists($email)) {
            // User exist in bdd
            $response = email_exists($email);
        } else {
            $response = wp_insert_user($args);
            if (is_wp_error($response)) {
                return false;
            }
        }

        if (!is_numeric($response)) {
            echo "Value isn't numeric";
            return false;
        }

        $user_id = $response;
        $phone_number = $_POST['phone'];
        if ($role == 'candidate') {
            $candidate = new jpCandidate($user_id);
            $candidate->profile_update([
                'phones' => [ esc_sql($phone_number) ],
                'hasCV' => false
            ]);
        }

        do_action('send_email_new_user', $user_id); // Envoyer le mail
    }
});