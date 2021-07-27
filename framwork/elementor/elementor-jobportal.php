<?php

namespace JobSearch;
/**
 * Plugin Name: Elementor Button Fixed
 * Description: Fixed button for Elementor page builder
 * Plugin URI: https://github.com/MarieComet/Elementor-Button-Fixed/
 * Version: 0.0.1
 * Author: Marie Comet
 * Author URI: https://mariecomet.fr
 * Text Domain: elementor-button-fixed
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly
include 'job_search/widgets/jp-search.php';
include 'job_grid/widgets/jp-job-grid.php';
include 'job_registration/widgets/jp-registration.php';
include 'job_add_annonce/widgets/jp-add-annonce.php';
include 'job_login/widgets/jp-login.php';

use Elementor\Plugin;
use JobAddAnnonce\Widgets\JobAddAnnonce_Widget;
use JobGrid\Widgets\JobGrid_Widget;
use jobLogin\Widgets\jobLogin_Widget;
use JobRegistration\Widgets\JobRegistration_Widget;
use JobSearch\Widgets\JobSearch_Widget;

// The Widget_Base class is not available immediately after plugins are loaded, so
// we delay the class' use until Elementor widgets are registered
add_action('elementor/widgets/widgets_registered', function () {

    // Let Elementor know about our widget
    Plugin::instance()->widgets_manager->register_widget_type(new JobSearch_Widget());
    Plugin::instance()->widgets_manager->register_widget_type(new JobGrid_Widget());
    Plugin::instance()->widgets_manager->register_widget_type(new JobRegistration_Widget());
    Plugin::instance()->widgets_manager->register_widget_type(new JobAddAnnonce_Widget());
    Plugin::instance()->widgets_manager->register_widget_type(new jobLogin_Widget());
});