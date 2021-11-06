<?php


namespace JP\Framework\Model;

use JP\Framework\Elements\jpJobs;

if (!defined('ABSPATH')) {
    exit;
}

class jModel
{
    public static function get_pending_candidature(): array
    {
        global $wpdb;
        $table = $wpdb->prefix . 'job_apply';
        $sql = "SELECT * FROM {$table} WHERE status = %d";
        $results = $wpdb->get_results($wpdb->prepare($sql, 0));
        foreach ($results as $apply) {
            // todo pas de validation pour le moment
        }
        return $results;
    }

    public static function get_pending_offers(): array
    {
        $response = [];
        $args = [
            'post_type' => 'jp-jobs',
            'post_status' => "pending",
            'posts_per_page' => -1
        ];
        $query = new \WP_Query($args);
        if ($query->have_posts()) {
            while ($query->have_posts()):
                $query->the_post();
                if (is_null($query->post)) continue;
                $response[] = new jpJobs($query->post);
                endwhile;
        }
        return $response;
    }

    public static function get_pending_candidates() {
        $responses = [];
        $args = [
            'role__in' => ['candidate'],
            'meta_query' => [
                'relation' => 'AND',
                [
                    'key' => 'validated',
                    'value' => 1,
                    'compare' => '='
                ],
                [
                    'key' => 'blocked',
                    'value' => 1,
                    'compare' => '!='
                ],
                [
                    'key' => 'has_cv',
                    'value' => 0,
                    'compare' => '!='
                ],
            ]
        ];
        $query = new \WP_User_Query($args);
        $results = $query->get_results();
        return $results;
    }
}