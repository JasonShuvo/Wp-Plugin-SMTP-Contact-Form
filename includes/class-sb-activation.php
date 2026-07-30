<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the "activate the plugin with a real email address" flow.
 *
 * - Site owner enters their email on the Activation screen.
 * - Plugin emails them a 6-digit code.
 * - Once they confirm the code, the plugin is marked active
 *   (sbca_activated = 1) and a heads-up email is sent to the
 *   plugin developer so they know someone new is trying the plugin.
 */
class SBCA_Activation {

	const OPTION_STATUS = 'sbca_activated';
	const OPTION_EMAIL  = 'sbca_activation_email';
	const OPTION_CODE   = 'sbca_activation_code';

	public function __construct() {
		add_action( 'admin_post_sbca_send_activation_code', array( $this, 'handle_send_code' ) );
		add_action( 'admin_post_sbca_confirm_activation_code', array( $this, 'handle_confirm_code' ) );
	}

	public static function on_plugin_activate() {
		if ( get_option( self::OPTION_STATUS ) === false ) {
			add_option( self::OPTION_STATUS, 0 );
		}
	}

	public static function is_active() {
		return (bool) get_option( self::OPTION_STATUS, 0 );
	}

	/**
	 * Step 1: send a verification code to the email the admin entered.
	 */
	public function handle_send_code() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'sb-contract-animation' ) );
		}
		check_admin_referer( 'sbca_send_activation_code' );

		$email = isset( $_POST['sbca_email'] ) ? sanitize_email( wp_unslash( $_POST['sbca_email'] ) ) : '';

		if ( empty( $email ) || ! is_email( $email ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'sbca-activation', 'sbca_notice' => 'invalid_email' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		$code = (string) wp_rand( 100000, 999999 );

		update_option( self::OPTION_EMAIL, $email );
		update_option( self::OPTION_CODE, $code );

		$subject = sprintf( '[%s] Your SB Contract & Animation activation code', get_bloginfo( 'name' ) );
		$body    = "Hi,\n\nYour activation code for the SB Contract & Animation plugin is:\n\n$code\n\nEnter this code in your WordPress admin to finish activating the plugin.\n\nIf you did not request this, you can ignore this email.";

		wp_mail( $email, $subject, $body );

		wp_safe_redirect( add_query_arg( array( 'page' => 'sbca-activation', 'sbca_notice' => 'code_sent' ), admin_url( 'admin.php' ) ) );
		exit;
	}

	/**
	 * Step 2: confirm the code, mark the plugin active, notify the developer.
	 */
	public function handle_confirm_code() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'sb-contract-animation' ) );
		}
		check_admin_referer( 'sbca_confirm_activation_code' );

		$submitted_code = isset( $_POST['sbca_code'] ) ? sanitize_text_field( wp_unslash( $_POST['sbca_code'] ) ) : '';
		$saved_code     = get_option( self::OPTION_CODE );
		$email          = get_option( self::OPTION_EMAIL );

		if ( empty( $submitted_code ) || $submitted_code !== $saved_code ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'sbca-activation', 'sbca_notice' => 'wrong_code' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		update_option( self::OPTION_STATUS, 1 );

		// Notify the plugin developer that a new site is testing the plugin.
		$dev_subject = 'SB Contract & Animation: new activation';
		$dev_body    = "A site just activated the SB Contract & Animation plugin.\n\n"
			. 'Site: ' . home_url() . "\n"
			. 'Admin email entered: ' . $email . "\n"
			. 'Site name: ' . get_bloginfo( 'name' ) . "\n"
			. 'Time: ' . current_time( 'mysql' );

		wp_mail( SBCA_DEVELOPER_NOTIFY_EMAIL, $dev_subject, $dev_body );

		wp_safe_redirect( add_query_arg( array( 'page' => 'sbca-activation', 'sbca_notice' => 'activated' ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
