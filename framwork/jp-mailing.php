<?php

use JP\Framwork\Elements\jpCandidate;
use JP\Framwork\Elements\jpJobs;

if (!defined('ABSPATH')) {
    exit;
}

$admin_email = 'contact@falicrea.net';
$dev_email = 'tiafenofnel@gmail.com';

add_action('send_email_new_user', function($user_id = 0) {
    if (!$user_id) return;
    global $Liquid_engine;
    $subject = "Activation de compte - JOBJIABY";
    $headers = [];
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = "From: Jobjiaby <no-reply@jobjiaby.com>";

    $custom_logo_id = get_theme_mod('custom_logo');
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');

    $user = new WP_User($user_id);
    $e = base64_encode($user->user_email);

    $nonce = wp_create_nonce("jobjiaby_verify_email");

    $content = $Liquid_engine->parseFile('mails/verify_email')->render([
        'link' => home_url('/') . "?e={$e}&verify_email_nonce={$nonce}",
        'home_url' => home_url("/"),
        'logo' => $logo[0]
    ]);
    wp_mail($user->user_email, $subject, $content, $headers);
}, 10, 1);


add_action('send_mail_activated_account', function($user_id, $template = '') {
    if (!$user_id) return;
    global $Liquid_engine;
    $subject = "Validation de compte - JOBJIABY";
    $headers = [];
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = "From: Jobjiaby <no-reply@jobjiaby.com>";

    $custom_logo_id = get_theme_mod('custom_logo');
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');

    $user = new WP_User($user_id);
    $filename = "mails/{$template}_validation_account";
    $content = $Liquid_engine->parseFile($filename)->render([
        'link' => home_url('/espace-client'),
        'home_url' => home_url("/"),
        'logo' => $logo[0]
    ]);
    wp_mail($user->user_email, $subject, $content, $headers);
}, 10, 2);

add_action('send_mail_when_user_apply', function($job_id = 0, $candidate_id = 0) {
    if (0 === $job_id || 0 === $candidate_id) return;
    global $Liquid_engine;

    $job = new jpJobs(new WP_Post($job_id));

    // employer
    $employer = $job->get_employer_object();
    if (is_wp_error($employer)) return;

    // company
    $company = $employer->get_company_object();
    if (is_wp_error($company)) return;

    // candidate
    $candidate = new jpCandidate($candidate_id);

    $subject = "Candidature - JOBJIABY";
    $headers = [];
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = "From: Jobjiaby <no-reply@jobjiaby.com>";

    $custom_logo_id = get_theme_mod('custom_logo');
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');

    $annonce = $job->get_post();

    $content = $Liquid_engine->parseFile('mails/notice_employer_when_candidate_apply')->render([
        'espace_client_url' => home_url('/espace-client'),
        'company' => $company,
        'candidate' => $candidate,
        'job' => $annonce,
        'home_url' => home_url("/"),
        'logo' => $logo[0]
    ]);
    $result = wp_mail($employer->user_email, $subject, $content, $headers);
    if ($result) {
        global $dev_email, $admin_email;
        // Send to admin
        $headers[] = "Cc: Dev <$dev_email>";
        wp_mail($admin_email, $subject, $content, $headers);
    }
}, 10, 2);


/**
 * On publish job
 */
add_action('send_mail_when_publish_emploi', function($job_id) {
    global $Liquid_engine;

    $job = new jpJobs(new WP_Post($job_id));

    // employer
    $employer = $job->get_employer_object();
    if (is_wp_error($employer)) return;

    // company
    $company = $employer->get_company_object();
    if (is_wp_error($company)) return;


    $subject = "Validation de votre offre d’emploi sur JOBJIABY";
    $headers = [];
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = "From: Jobjiaby <no-reply@jobjiaby.com>";

    $custom_logo_id = get_theme_mod('custom_logo');
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');

    $annonce = $job->get_post();
    $content = $Liquid_engine->parseFile('mails/notice_publish_annonce')->render([
        'company' => $company,
        'job' => $annonce,
        'home_url' => home_url("/"),
        'logo' => $logo[0]
    ]);
    wp_mail($employer->user_email, $subject, $content, $headers);
}, 10, 1);