<?php
// If uninstall is not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * NOTE: By default this only removes plugin options, not the
 * submissions table (so you don't accidentally lose contract-form
 * data by uninstalling). Uncomment the DROP TABLE block below if
 * you want a full clean uninstall.
 */

$options = array(
	'sbca_hero_settings',
	'sbca_smtp_settings',
	'sbca_email_templates',
	'sbca_activated',
	'sbca_activation_email',
	'sbca_activation_code',
	'sbca_last_mail_error',
	'sbca_db_version',
);

foreach ( $options as $option ) {
	delete_option( $option );
}

// Uncomment to also remove the submissions table on uninstall:
// global $wpdb;
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}sbca_submissions" );
