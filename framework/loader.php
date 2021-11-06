<?php
if (!defined('ABSPATH')) {
    exit;
}
require_once 'api_rest.php';

require_once 'admin-manager.php';
require_once 'menu-walker.php';
require_once 'service-providers.php';

require_once 'JTools.php';
require_once 'jMailing.php';
require_once 'jActions.php';

// load widget
require_once 'widgets/widget-social.php';

// Load elementor module
add_action('init', function() {
    if ( class_exists('Elementor\Widget_Base')) {
        require_once 'elementor/elementor-jobportal.php';
    }
});

// Traits
require 'traits/DemandeTrait.php';
require 'traits/DemandeTypeTrait.php';
require 'traits/ProfilAccessTrait.php';

// Class object
require 'elements/jCandidate.php';
require 'elements/jpCompany.php';
require 'elements/jpEmployer.php';
require 'elements/jpJobs.php';

// Styles and Scripts
require_once 'enqueue.php';
