<?php
require_once 'api_rest.php';
require_once 'jp-helpers.php';
require_once 'jp-actions.php';
require_once 'jp-mailing.php';
add_action('init', function() {
    if ( class_exists('Elementor\Widget_Base')) {
        require_once 'elementor/elementor-jobportal.php';
    }
});
require 'elements/jpCandidate.php';
require 'elements/jpCompany.php';
require 'elements/jpEmployer.php';
require 'elements/jpJobs.php';
require_once 'jp-functions.php';
