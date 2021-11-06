<?php
/**
 * Job apply table
 */
add_action('after_switch_theme', function() {
    global $wpdb;
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    $apply_table = $wpdb->prefix . 'job_apply';

    $sql = "CREATE TABLE IF NOT EXISTS $apply_table (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		job_id bigint(20) NOT NULL,
		candidate_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		employer_id  BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  `apply_id` (`ID`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1; ";
    dbDelta( $sql );

    $abonnement_table = $wpdb->prefix . 'abonnement';
    $sql = "CREATE TABLE IF NOT EXISTS $abonnement_table (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		employer_id  BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		date_end DATETIME NOT NULL,
        purchase_key TINYINT(5) NOT NULL DEFAULT 0,
        validated TINYINT(5) NOT NULL DEFAULT 1,
		PRIMARY KEY  `abonnement_id` (`ID`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1; ";
    dbDelta( $sql );

    // Demande
    // status: 0: en attente, 1: valider, 2: refuser
    $demande_table = $wpdb->prefix . 'demande';
    $sql = "CREATE TABLE IF NOT EXISTS $demande_table (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		user_id bigint(20) NOT NULL,
        type_demande_id bigint(255) NOT NULL DEFAULT 0,
        status TINYINT(5) NOT NULL DEFAULT 0,
        reference VARCHAR(225) NULL DEFAULT NULL UNIQUE,
        data_request varchar (255) NOT NULL DEFAULT 0,
        date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  `demande_id` (`ID`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1; ";
    dbDelta( $sql );
    /**
     * Type de demande
     *  - Demande de consulter les information d'un candidate (DMD_CANDIDAT)
     *  - Demande d'abonnement (DMD_ABONNEMENT)
     *
     */
    $demande_type_table = $wpdb->prefix . 'demande_type';
    $sql = "CREATE TABLE IF NOT EXISTS $demande_type_table (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
		name varchar(250) NOT NULL,
        description varchar(250) DEFAULT '',
		date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  `demande_type_id` (`ID`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
    dbDelta( $sql );


    $profil_access_table = $wpdb->prefix . 'profil_employer_access';
    $sql = "CREATE TABLE IF NOT EXISTS $profil_access_table (
		ID bigint(20) NOT NULL AUTO_INCREMENT,
        employer_id  BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        candidate_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
		date_add DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        purchased TINYINT(5) NOT NULL DEFAULT 0,
        purchase_key VARCHAR(255) NOT NULL DEFAULT '',
		PRIMARY KEY  `profil_access_id` (`ID`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;";
    dbDelta( $sql );
});

/**
 * Pricing
 */
