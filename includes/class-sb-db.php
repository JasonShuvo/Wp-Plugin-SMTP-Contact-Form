<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the custom database table that stores contract form submissions.
 */
class SBCA_DB {

	public static function table_name() {
		global $wpdb;
		return $wpdb->prefix . 'sbca_submissions';
	}

	/**
	 * Runs on plugin activation.
	 */
	public static function create_tables() {
		global $wpdb;

		$table_name      = self::table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name VARCHAR(191) NOT NULL,
			email VARCHAR(191) NOT NULL,
			phone VARCHAR(64) DEFAULT '',
			subject VARCHAR(191) DEFAULT '',
			message LONGTEXT NULL,
			status VARCHAR(20) DEFAULT 'new',
			ip_address VARCHAR(100) DEFAULT '',
			created_at DATETIME NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'sbca_db_version', SBCA_VERSION );
	}

	/**
	 * Insert a new submission, returns the inserted row ID or false.
	 */
	public static function insert_submission( $data ) {
		global $wpdb;

		$inserted = $wpdb->insert(
			self::table_name(),
			array(
				'name'       => sanitize_text_field( $data['name'] ),
				'email'      => sanitize_email( $data['email'] ),
				'phone'      => isset( $data['phone'] ) ? sanitize_text_field( $data['phone'] ) : '',
				'subject'    => isset( $data['subject'] ) ? sanitize_text_field( $data['subject'] ) : '',
				'message'    => isset( $data['message'] ) ? sanitize_textarea_field( $data['message'] ) : '',
				'status'     => 'new',
				'ip_address' => isset( $data['ip_address'] ) ? sanitize_text_field( $data['ip_address'] ) : '',
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $inserted ? $wpdb->insert_id : false;
	}

	public static function get_submissions( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'limit'   => 20,
			'offset'  => 0,
			'orderby' => 'id',
			'order'   => 'DESC',
		);
		$args = wp_parse_args( $args, $defaults );

		$table   = self::table_name();
		$orderby = in_array( $args['orderby'], array( 'id', 'name', 'email', 'created_at' ), true ) ? $args['orderby'] : 'id';
		$order   = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		$sql = $wpdb->prepare(
			"SELECT * FROM $table ORDER BY $orderby $order LIMIT %d OFFSET %d",
			(int) $args['limit'],
			(int) $args['offset']
		);

		return $wpdb->get_results( $sql );
	}

	public static function count_submissions() {
		global $wpdb;
		$table = self::table_name();
		return (int) $wpdb->get_var( "SELECT COUNT(id) FROM $table" );
	}

	public static function get_submission( $id ) {
		global $wpdb;
		$table = self::table_name();
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );
	}

	public static function delete_submission( $id ) {
		global $wpdb;
		return $wpdb->delete( self::table_name(), array( 'id' => (int) $id ), array( '%d' ) );
	}

	public static function update_status( $id, $status ) {
		global $wpdb;
		return $wpdb->update(
			self::table_name(),
			array( 'status' => sanitize_text_field( $status ) ),
			array( 'id' => (int) $id ),
			array( '%s' ),
			array( '%d' )
		);
	}
}

