<?php


namespace JP\Framwork\Model;

if (!defined('ABSPATH')) {
    exit;
}

class jModel
{
    public static function get_pending_candidature(): array {
        global $wpdb;
        $table = $wpdb->prefix.'job_apply';
        $sql = "SELECT * FROM {$table} WHERE status = %d";
        $results = $wpdb->get_results($wpdb->prepare($sql, 0));
        foreach ($results as $apply) {

        }
        return $results;
    }
}