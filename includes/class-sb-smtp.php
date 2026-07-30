<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Routes wp_mail() through SMTP (a normal web-hosting mailbox, or Gmail
 * using an App Password) based on settings saved in the admin panel.
 */
class SBCA_SMTP {

	const OPTION_KEY = 'sbca_smtp_settings';

	public function __construct() {
		add_action( 'phpmailer_init', array( $this, 'configure_phpmailer' ) );
		add_action( 'admin_post_sbca_send_test_email', array( $this, 'handle_test_email' ) );
		add_action( 'wp_mail_failed', array( $this, 'log_mail_error' ) );
	}

	public static function get_settings() {
		$defaults = array(
			'enabled'       => 0,
			'provider'      => 'custom', // 'custom' or 'gmail'
			'host'          => '',
			'port'          => 587,
			'encryption'    => 'tls', // tls, ssl, none
			'username'      => '',
			'password'      => '',
			'from_email'    => get_bloginfo( 'admin_email' ),
			'from_name'     => get_bloginfo( 'name' ),
		);

		$saved = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $saved, $defaults );
	}

	public function configure_phpmailer( $phpmailer ) {
		$settings = self::get_settings();

		if ( empty( $settings['enabled'] ) || empty( $settings['host'] ) ) {
			return;
		}

		$phpmailer->isSMTP();
		$phpmailer->Host       = $settings['host'];
		$phpmailer->Port       = (int) $settings['port'];
		$phpmailer->SMTPAuth   = true;
		$phpmailer->Username   = $settings['username'];
		$phpmailer->Password   = $settings['password'];

		if ( 'none' === $settings['encryption'] ) {
			$phpmailer->SMTPSecure = '';
			$phpmailer->SMTPAutoTLS = false;
		} else {
			$phpmailer->SMTPSecure = $settings['encryption']; // 'tls' or 'ssl'
		}

		if ( ! empty( $settings['from_email'] ) ) {
			$phpmailer->setFrom( $settings['from_email'], $settings['from_name'] );
		}
	}

	public function log_mail_error( $wp_error ) {
		update_option( 'sbca_last_mail_error', $wp_error->get_error_message() );
	}

	/**
	 * Sends a test email from the SMTP settings page.
	 */
	public function handle_test_email() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'sb-contract-animation' ) );
		}
		check_admin_referer( 'sbca_send_test_email' );

		$to = isset( $_POST['sbca_test_email'] ) ? sanitize_email( wp_unslash( $_POST['sbca_test_email'] ) ) : '';

		if ( empty( $to ) || ! is_email( $to ) ) {
			wp_safe_redirect( add_query_arg( array( 'page' => 'sbca-smtp', 'sbca_notice' => 'invalid_email' ), admin_url( 'admin.php' ) ) );
			exit;
		}

		delete_option( 'sbca_last_mail_error' );

		$sent = wp_mail(
			$to,
			'SB Contract & Animation - Test Email',
			"This is a test email sent from the SB Contract & Animation plugin's SMTP settings.\n\nIf you received this, your SMTP configuration is working."
		);

		$notice = $sent ? 'test_sent' : 'test_failed';
		wp_safe_redirect( add_query_arg( array( 'page' => 'sbca-smtp', 'sbca_notice' => $notice ), admin_url( 'admin.php' ) ) );
		exit;
	}
}
