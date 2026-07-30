<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Builds and sends the two form-related emails:
 *  1. Admin notification - "you got a new submission"
 *  2. User confirmation  - "thanks, we received your message"
 *
 * Both templates are stored as options and are fully editable from
 * the WP admin, using simple {placeholder} tokens.
 */
class SBCA_Emails {

	const OPTION_KEY = 'sbca_email_templates';

	public static function get_templates() {
		$defaults = array(
			'admin_subject' => 'New contract form submission from {name}',
			'admin_body'    => "You received a new submission on {site_name}.\n\nName: {name}\nEmail: {email}\nPhone: {phone}\nSubject: {subject}\n\nMessage:\n{message}\n\nSubmitted: {date}",
			'admin_to'      => get_bloginfo( 'admin_email' ),
			'user_subject'  => 'Thanks for contacting {site_name}, {name}!',
			'user_body'     => "Hi {name},\n\nThank you for reaching out to {site_name}. We received your message and will get back to you soon.\n\nYour message:\n{message}\n\nBest regards,\n{site_name}",
		);

		$saved = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Replace {placeholder} tokens with real submission data.
	 */
	public static function apply_tokens( $text, $data ) {
		$tokens = array(
			'{name}'      => $data['name'],
			'{email}'     => $data['email'],
			'{phone}'     => isset( $data['phone'] ) ? $data['phone'] : '',
			'{subject}'   => isset( $data['subject'] ) ? $data['subject'] : '',
			'{message}'   => isset( $data['message'] ) ? $data['message'] : '',
			'{site_name}' => get_bloginfo( 'name' ),
			'{site_url}'  => home_url(),
			'{date}'      => current_time( 'F j, Y g:i a' ),
		);

		return strtr( $text, $tokens );
	}

	/**
	 * Sends both the admin notification and the user confirmation email.
	 */
	public static function send_submission_emails( $data ) {
		$templates = self::get_templates();

		// 1. Notify the admin.
		$admin_to      = ! empty( $templates['admin_to'] ) ? $templates['admin_to'] : get_bloginfo( 'admin_email' );
		$admin_subject = self::apply_tokens( $templates['admin_subject'], $data );
		$admin_body    = self::apply_tokens( $templates['admin_body'], $data );
		wp_mail( $admin_to, $admin_subject, $admin_body );

		// 2. Confirm to the user who filled the form.
		if ( ! empty( $data['email'] ) && is_email( $data['email'] ) ) {
			$user_subject = self::apply_tokens( $templates['user_subject'], $data );
			$user_body    = self::apply_tokens( $templates['user_body'], $data );
			wp_mail( $data['email'], $user_subject, $user_body );
		}
	}
}
