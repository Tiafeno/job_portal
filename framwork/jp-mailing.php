<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('send_email_new_user', function($user_id = 0) {
    if (!$user_id) return;
    global $Liquid_engine;
    $subject = "Activation du compte - JOBJIABY";
    $headers = [];
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = "From: Jobjiaby <no-reply@jobjiaby.com>";

    $custom_logo_id = get_theme_mod('custom_logo');
    $logo = wp_get_attachment_image_src($custom_logo_id, 'full');

    $user = new WP_User($user_id);
    $e = base64_encode($user->user_email);

    $nonce = wp_create_nonce("jobjiaby_verify_email");

    $content = $Liquid_engine->parseFile('verify_email')->render([
        'link' => home_url('/') . "?e={$e}&verify_email_nonce={$nonce}",
        'home_url' => home_url("/"),
        'logo' => $logo[0]
    ]);
    wp_mail($user->user_email, $subject, $content, $headers);

}, 10, 1);


add_action('send_mail_activated_account', function($user_id) {
    if (!$user_id) return;
    global $Liquid_engine;
    // todo send mail to user
}, 10, 1);