<?php
namespace JP\Framework;

use JP\Framework\Model\jModel;

class jCron {
    public function __construct() {}

    public function pending_postulated_candidate() {
        $pending = jModel::get_pending_candidature();
    }
}


$cron = new jCron();
add_action('jobjiaby_send_pending_postulated_candidate', [$cron, 'pending_postulated_candidate'], 10);