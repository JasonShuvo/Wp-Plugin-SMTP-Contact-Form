<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the [sb_contract_form] shortcode and handles submissions:
 * saves the entry to the database (visible in wp-admin), then sends
 * the admin notification + user confirmation emails.
 */
class SBCA_Form {

	public function __construct() {
		add_shortcode( 'sb_contract_form', array( $this, 'render_shortcode' ) );
		add_action( 'admin_post_sbca_submit_form', array( $this, 'handle_submit' ) );
		add_action( 'admin_post_nopriv_sbca_submit_form', array( $this, 'handle_submit' ) );
	}

	public function render_shortcode( $atts ) {
		wp_enqueue_style( 'sbca-style' );

		$atts = shortcode_atts(
			array(
				'title' => 'Get In Touch',
			),
			$atts,
			'sb_contract_form'
		);

		$notice = '';
		if ( isset( $_GET['sbca_form'] ) ) {
			if ( 'success' === $_GET['sbca_form'] ) {
				$notice = '<div class="sbca-notice sbca-notice-success">' . esc_html__( 'Thank you! Your message has been sent successfully.', 'sb-contract-animation' ) . '</div>';
			} elseif ( 'error' === $_GET['sbca_form'] ) {
				$notice = '<div class="sbca-notice sbca-notice-error">' . esc_html__( 'Please fill in all required fields correctly and try again.', 'sb-contract-animation' ) . '</div>';
			}
		}

		ob_start();
		?>
		<div id="sb-contract-form" class="sbca-form-wrap">
			<h2 class="sbca-form-title"><?php echo esc_html( $atts['title'] ); ?></h2>
			<?php echo $notice; // phpcs:ignore ?>
			<form class="sbca-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="sbca_submit_form">
				<?php wp_nonce_field( 'sbca_submit_form_action', 'sbca_form_nonce' ); ?>
				<input type="text" name="sbca_website" style="display:none" tabindex="-1" autocomplete="off">

				<div class="sbca-form-row">
					<label for="sbca-name"><?php esc_html_e( 'Full Name', 'sb-contract-animation' ); ?> *</label>
					<input type="text" id="sbca-name" name="sbca_name" required>
				</div>

				<div class="sbca-form-row">
					<label for="sbca-email"><?php esc_html_e( 'Email Address', 'sb-contract-animation' ); ?> *</label>
					<input type="email" id="sbca-email" name="sbca_email" required>
				</div>

				<div class="sbca-form-row">
					<label for="sbca-phone"><?php esc_html_e( 'Phone Number', 'sb-contract-animation' ); ?></label>
					<input type="text" id="sbca-phone" name="sbca_phone">
				</div>

				<div class="sbca-form-row">
					<label for="sbca-subject"><?php esc_html_e( 'Subject', 'sb-contract-animation' ); ?></label>
					<input type="text" id="sbca-subject" name="sbca_subject">
				</div>

				<div class="sbca-form-row">
					<label for="sbca-message"><?php esc_html_e( 'Message', 'sb-contract-animation' ); ?> *</label>
					<textarea id="sbca-message" name="sbca_message" rows="5" required></textarea>
				</div>

				<button type="submit" class="sbca-form-submit"><?php esc_html_e( 'Send Message', 'sb-contract-animation' ); ?></button>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	public function handle_submit() {
		$redirect_base = wp_get_referer() ? wp_get_referer() : home_url();

		if ( ! isset( $_POST['sbca_form_nonce'] ) || ! wp_verify_nonce( $_POST['sbca_form_nonce'], 'sbca_submit_form_action' ) ) {
			wp_safe_redirect( add_query_arg( 'sbca_form', 'error', $redirect_base ) );
			exit;
		}

		// Honeypot check.
		if ( ! empty( $_POST['sbca_website'] ) ) {
			wp_safe_redirect( add_query_arg( 'sbca_form', 'success', $redirect_base ) );
			exit;
		}

		$name    = isset( $_POST['sbca_name'] ) ? sanitize_text_field( wp_unslash( $_POST['sbca_name'] ) ) : '';
		$email   = isset( $_POST['sbca_email'] ) ? sanitize_email( wp_unslash( $_POST['sbca_email'] ) ) : '';
		$phone   = isset( $_POST['sbca_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['sbca_phone'] ) ) : '';
		$subject = isset( $_POST['sbca_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['sbca_subject'] ) ) : '';
		$message = isset( $_POST['sbca_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['sbca_message'] ) ) : '';

		if ( empty( $name ) || empty( $email ) || ! is_email( $email ) || empty( $message ) ) {
			wp_safe_redirect( add_query_arg( 'sbca_form', 'error', $redirect_base ) );
			exit;
		}

		$data = array(
			'name'       => $name,
			'email'      => $email,
			'phone'      => $phone,
			'subject'    => $subject,
			'message'    => $message,
			'ip_address' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '',
		);

		SBCA_DB::insert_submission( $data );
		SBCA_Emails::send_submission_emails( $data );

		wp_safe_redirect( add_query_arg( 'sbca_form', 'success', $redirect_base ) );
		exit;
	}
}
