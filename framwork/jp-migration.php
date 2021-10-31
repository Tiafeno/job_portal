<?php
/**
 * Job apply table
 */
add_action('after_switch_theme', function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'job_apply';
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		job_id bigint(20) NOT NULL,
		candidate_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		employer_id  BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        purchased TINYINT(5) NOT NULL DEFAULT 0,
        status TINYINT(5) NOT NULL DEFAULT 0,
		PRIMARY KEY  `apply_id` (`ID`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
});

/**
 * Pricing
 */
