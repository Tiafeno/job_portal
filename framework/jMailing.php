<?php

use JP\Framework\Elements\jCandidate;
use JP\Framework\Elements\JDemande;
use JP\Framework\Elements\jpEmployer;
use JP\Framework\Elements\jpJobs;

if (!defined('ABSPATH')) {
    exit;
}

class jMailing {
    public $admin_email = 'contact@falicrea.net';
    public $dev_email = 'tiafenofnel@gmail.com';
    public $logo = null;
    public function __contruct() {
        $custom_logo_id = get_theme_mod('custom_logo');
        $this->logo = wp_get_attachment_image_src($custom_logo_id, 'full')[0];
    }
    public static function send($to, $subject, $content, $headers) {
        return wp_mail($to, $subject, $content, $headers);
    }
}

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

    try {
        $content = $Liquid_engine->parseFile('mails/verify_email')->render([
            'link' => home_url('/') . "?e={$e}&verify_email_nonce={$nonce}",
            'home_url' => home_url("/"),
            'logo' => $logo[0]
        ]);
    } catch (Exception $e) {
        return false;
    }
    jMailing::send($user->user_email, $subject, $content, $headers);
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
    try {
        $content = $Liquid_engine->parseFile($filename)->render([
            'link' => home_url('/espace-client'),
            'home_url' => home_url("/"),
            'logo' => $logo[0]
        ]);
    } catch (Exception $e) {
        return false;
    }
    jMailing::send($user->user_email, $subject, $content, $headers);
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
    $candidate = new jCandidate($candidate_id);

    $subject = "Candidature - JOBJIABY";
    $headers = [];
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = "From: Jobjiaby <no-reply@jobjiaby.com>";

    $custom_logo_id = get_theme_mod('custom_logo');
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');

    $annonce = $job->get_post();

    try {
        $content = $Liquid_engine->parseFile('mails/notice_employer_when_candidate_apply')->render([
            'espace_client_url' => home_url('/espace-client'),
            'company' => get_object_vars($company),
            'candidate' => get_object_vars($candidate),
            'job' => get_object_vars($annonce),
            'home_url' => home_url("/"),
            'logo' => $logo[0]
        ]);
    } catch (Exception $e) {
        return false;
    }
    $result = wp_mail($employer->user_email, $subject, $content, $headers);
    if ($result) {
        // Send to admin
        $jmail = new jMailing();
        $headers[] = "Cc: Dev <$jmail->dev_email>";
        wp_mail($jmail->admin_email, $subject, $content, $headers);
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
        'company' => get_object_vars($company),
        'job' => get_object_vars($annonce),
        'home_url' => home_url("/"),
        'logo' => $logo[0]
    ]);
    wp_mail($employer->user_email, $subject, $content, $headers);
}, 10, 1);

add_action('send_mail_demande_accepted', function($id_demande) {
    // todo envoyer mail au client

}, 10, 1);

add_action('send_mail_on_demande_posted', function(int $id_demande) {
    global $engine;
    $demande = new JDemande($id_demande);
    $type_demande_name = $demande->type_demande->name;
    switch ($type_demande_name){
        case 'DMD_CANDIDAT':
            $candidate_id = $demande->getData('candidate_id');
            if (is_null($candidate_id)) {
                break;
            }
            $candidate = new jCandidate((int) $candidate_id);
            $employer = new jpEmployer($demande->user->ID);
            $company = $employer->get_company_object();
            $subject = "Demande à consulter un CV `{$candidate->reference}` sur JOBJIABY";
            $headers = [];
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = "From: Jobjiaby <no-reply@jobjiaby.com>";
            $to = (new jMailing())->admin_email;
            $body = $engine->parseFile("mails/notice_on_demande_posted")->render([
                'company' => [
                    'display_name' => $company->display_name,
                    'phone' => $company->phone
                ],
                'employer' => [
                    'user_email' => $demande->user->user_email
                ],
                'candidate' => [
                    'reference' => $candidate->reference
                ],
                'home_url' => home_url("/"),
                'logo' => (new jMailing())->logo
            ]);
            jMailing::send($to, $subject, $body, $headers);
            break;
    }
}, 10, 1);