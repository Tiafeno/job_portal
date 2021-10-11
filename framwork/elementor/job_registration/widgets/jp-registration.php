<?php
namespace JobRegistration\Widgets;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use JP\Framwork\Elements\jpCandidate;

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
        global $Liquid_engine;
        $home_url = home_url('/');
        if (is_user_logged_in()) {
            // is logged user
            echo "<p class='text-center'><a href='".$home_url."' target='_parent' class='btn btn-info'><i class='ti-back-left'></i> Page d'accueil</a> </p>";
            return;
        }

        $nonce = wp_nonce_field('portaljob-register', '_wpnonce', true, false);
        $current_page_url = get_the_permalink();
        echo $Liquid_engine->parseFile('job-registration')->render(['nonce' => $nonce, 'action' =>  $current_page_url . '?reg=true']);
    }
}

/**
 * Permet d'enregistrer un utilisateur (Employer ou Candidat)
 */
add_action('action_jobportal_register', function() {
    if ( ! \jpHelpers::getValue('_wpnonce', false) ) return;
    if (wp_verify_nonce($_POST['_wpnonce'], 'portaljob-register')) {
        $email = \jpHelpers::getValue('email', null);
        if (is_null($email) || empty($_POST['role'])) { return false; }
        $role = esc_attr($_POST['role']); //candidate or employer
        $password = \jpHelpers::getValue('password');
        if (!$password) return;
        $args = [
            'user_pass' => $password,
            'nickname' => $email,
            'first_name' => \jpHelpers::getValue('first_name', ''),
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
            if (is_wp_error($response)) {  return false; }
        }
        if (!is_numeric($response)) {
            echo "Value isn't numeric";
            return false;
        }
        $user_id = $response;
        $phone_number = $_POST['phone'];
        if ($role === 'candidate') {
            // Pour les candidat
            $candidate = new jpCandidate($user_id);
            $candidate->profile_update([
                'phones' => esc_sql($phone_number),
                'is_active' => 0,
                'has_cv' => 0,
            ]);
        } else {
            // pour les employer
            update_user_meta($user_id, 'company_id', 0);
        }

        do_action('send_email_new_user', $user_id); // Envoyer le mail
        // Redirection
        wp_redirect(home_url('/'));
        exit();
    }
});

add_action('wp_loaded', 'pre_process_registration', 1);
function pre_process_registration() {
    //if (!is_singular()) return;
    $reg = \jpHelpers::getValue('reg', false);
    var_dump($reg);
    wp_die();
    if (\jpHelpers::getValue('reg', false)) {
        // Enregistrer les informations utilisateur
        do_action('action_jobportal_register');
    }
}