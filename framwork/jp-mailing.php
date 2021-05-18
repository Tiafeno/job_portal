<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('send_email_new_user', function($user_id = 0) {

}, 10, 1);