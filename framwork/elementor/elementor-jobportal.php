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

use Elementor\Plugin;
use JobGrid\Widgets\JobGrid_Widget;
use JobSearch\Widgets\JobSearch_Widget;

// The Widget_Base class is not available immediately after plugins are loaded, so
// we delay the class' use until Elementor widgets are registered
add_action('elementor/widgets/widgets_registered', function () {

    $job_search = new JobSearch_Widget();
    $job_grid = new JobGrid_Widget();

    // Let Elementor know about our widget
    Plugin::instance()->widgets_manager->register_widget_type($job_search);
    Plugin::instance()->widgets_manager->register_widget_type($job_grid);
});