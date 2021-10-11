<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once 'api_rest.php';
require_once 'admin-manager.php';
require_once 'jp-helpers.php';
require_once 'jp-actions.php';
require_once 'jp-mailing.php';

// load widget
require_once 'widgets/widget-social.php';

// Load elementor module
add_action('init', function() {
    if ( class_exists('Elementor\Widget_Base')) {
        require_once 'elementor/elementor-jobportal.php';
    }
});
// Class object
require 'elements/jpCandidate.php';
require 'elements/jpCompany.php';
require 'elements/jpEmployer.php';
require 'elements/jpJobs.php';
// Functions
require_once 'jp-functions.php';
