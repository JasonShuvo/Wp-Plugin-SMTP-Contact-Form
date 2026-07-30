<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers all wp-admin screens: Activation, Hero settings, SMTP settings,
 * Email templates, Submissions list, Updater/Help page.
 */
class SBCA_Admin {

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'admin_notices', array( $this, 'maybe_show_activation_nag' ) );
	}

	public function enqueue_admin_assets( $hook ) {
		if ( strpos( $hook, 'sbca' ) === false ) {
			return;
		}
		wp_enqueue_style( 'sbca-admin-style', SBCA_PLUGIN_URL . 'assets/css/sb-admin.css', array(), SBCA_VERSION );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_media();
		wp_enqueue_script( 'sbca-admin-script', SBCA_PLUGIN_URL . 'assets/js/sb-admin.js', array( 'jquery', 'wp-color-picker' ), SBCA_VERSION, true );
	}

	public function register_menu() {
		add_menu_page(
			'SB Contract & Animation',
			'SB Contract & Anim',
			'manage_options',
			'sbca-settings',
			array( $this, 'render_hero_page' ),
			'dashicons-megaphone',
			26
		);

		add_submenu_page( 'sbca-settings', 'Hero Section', 'Hero Section', 'manage_options', 'sbca-settings', array( $this, 'render_hero_page' ) );
		add_submenu_page( 'sbca-settings', 'SMTP Settings', 'SMTP Settings', 'manage_options', 'sbca-smtp', array( $this, 'render_smtp_page' ) );
		add_submenu_page( 'sbca-settings', 'Email Templates', 'Email Templates', 'manage_options', 'sbca-email-templates', array( $this, 'render_email_templates_page' ) );
		add_submenu_page( 'sbca-settings', 'Form Submissions', 'Form Submissions', 'manage_options', 'sbca-submissions', array( $this, 'render_submissions_page' ) );
		add_submenu_page( 'sbca-settings', 'Activation', 'Activation', 'manage_options', 'sbca-activation', array( $this, 'render_activation_page' ) );
		add_submenu_page( 'sbca-settings', 'Shortcodes & Updates', 'Shortcodes & Updates', 'manage_options', 'sbca-help', array( $this, 'render_help_page' ) );
	}

	public function maybe_show_activation_nag() {
		$screen = get_current_screen();
		if ( ! $screen || strpos( $screen->id, 'sbca' ) === false ) {
			return;
		}
		if ( 'sbca-activation' !== ( $_GET['page'] ?? '' ) && ! SBCA_Activation::is_active() ) {
			echo '<div class="notice notice-warning"><p>' .
				sprintf(
					/* translators: %s: link to activation page */
					esc_html__( 'SB Contract & Animation is not activated yet. %s to enable full functionality.', 'sb-contract-animation' ),
					'<a href="' . esc_url( admin_url( 'admin.php?page=sbca-activation' ) ) . '">' . esc_html__( 'Activate it here', 'sb-contract-animation' ) . '</a>'
				) . '</p></div>';
		}
	}

	/* =====================================================
	 * HERO SETTINGS
	 * ===================================================== */
	public function render_hero_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['sbca_hero_save'] ) && check_admin_referer( 'sbca_save_hero' ) ) {
			$settings = array(
				'title'             => sanitize_text_field( $_POST['title'] ?? '' ),
				'subtitle'          => sanitize_text_field( $_POST['subtitle'] ?? '' ),
				'button_text'       => sanitize_text_field( $_POST['button_text'] ?? '' ),
				'button_url'        => esc_url_raw( $_POST['button_url'] ?? '' ),
				'bg_image'          => esc_url_raw( $_POST['bg_image'] ?? '' ),
				'bg_color'          => sanitize_hex_color( $_POST['bg_color'] ?? '' ),
				'title_color'       => sanitize_hex_color( $_POST['title_color'] ?? '' ),
				'subtitle_color'    => sanitize_hex_color( $_POST['subtitle_color'] ?? '' ),
				'button_bg_color'   => sanitize_hex_color( $_POST['button_bg_color'] ?? '' ),
				'button_text_color' => sanitize_hex_color( $_POST['button_text_color'] ?? '' ),
				'font_family'       => sanitize_text_field( $_POST['font_family'] ?? '' ),
				'animation_style'   => sanitize_text_field( $_POST['animation_style'] ?? '' ),
				'height'            => absint( $_POST['height'] ?? 520 ),
			);
			update_option( SBCA_Hero::OPTION_KEY, $settings );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Hero section settings saved.', 'sb-contract-animation' ) . '</p></div>';
		}

		$s = SBCA_Hero::get_settings();
		?>
		<div class="wrap sbca-wrap">
			<h1><?php esc_html_e( 'Animated Hero Section', 'sb-contract-animation' ); ?></h1>
			<p><?php esc_html_e( 'Configure the hero section, then place it anywhere with the shortcode:', 'sb-contract-animation' ); ?> <code>[sb_hero]</code></p>
			<form method="post">
				<?php wp_nonce_field( 'sbca_save_hero' ); ?>
				<table class="form-table">
					<tr><th><label for="title"><?php esc_html_e( 'Title', 'sb-contract-animation' ); ?></label></th>
						<td><input class="regular-text" type="text" name="title" id="title" value="<?php echo esc_attr( $s['title'] ); ?>"></td></tr>
					<tr><th><label for="subtitle"><?php esc_html_e( 'Subtitle', 'sb-contract-animation' ); ?></label></th>
						<td><textarea class="large-text" rows="3" name="subtitle" id="subtitle"><?php echo esc_textarea( $s['subtitle'] ); ?></textarea></td></tr>
					<tr><th><label for="button_text"><?php esc_html_e( 'Button Text', 'sb-contract-animation' ); ?></label></th>
						<td><input class="regular-text" type="text" name="button_text" id="button_text" value="<?php echo esc_attr( $s['button_text'] ); ?>"></td></tr>
					<tr><th><label for="button_url"><?php esc_html_e( 'Button Link', 'sb-contract-animation' ); ?></label></th>
						<td><input class="regular-text" type="text" name="button_url" id="button_url" value="<?php echo esc_attr( $s['button_url'] ); ?>"></td></tr>
					<tr><th><label for="bg_image"><?php esc_html_e( 'Background Image', 'sb-contract-animation' ); ?></label></th>
						<td>
							<input type="text" class="regular-text" id="bg_image" name="bg_image" value="<?php echo esc_attr( $s['bg_image'] ); ?>">
							<button type="button" class="button sbca-media-btn" data-target="bg_image"><?php esc_html_e( 'Choose Image', 'sb-contract-animation' ); ?></button>
						</td></tr>
					<tr><th><?php esc_html_e( 'Background Color', 'sb-contract-animation' ); ?></th>
						<td><input type="text" class="sbca-color-picker" name="bg_color" value="<?php echo esc_attr( $s['bg_color'] ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'Title Color', 'sb-contract-animation' ); ?></th>
						<td><input type="text" class="sbca-color-picker" name="title_color" value="<?php echo esc_attr( $s['title_color'] ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'Subtitle Color', 'sb-contract-animation' ); ?></th>
						<td><input type="text" class="sbca-color-picker" name="subtitle_color" value="<?php echo esc_attr( $s['subtitle_color'] ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'Button Background Color', 'sb-contract-animation' ); ?></th>
						<td><input type="text" class="sbca-color-picker" name="button_bg_color" value="<?php echo esc_attr( $s['button_bg_color'] ); ?>"></td></tr>
					<tr><th><?php esc_html_e( 'Button Text Color', 'sb-contract-animation' ); ?></th>
						<td><input type="text" class="sbca-color-picker" name="button_text_color" value="<?php echo esc_attr( $s['button_text_color'] ); ?>"></td></tr>
					<tr><th><label for="font_family"><?php esc_html_e( 'Font Family', 'sb-contract-animation' ); ?></label></th>
						<td>
							<select name="font_family" id="font_family">
								<?php
								$fonts = array(
									"'Segoe UI', Arial, sans-serif" => 'Segoe UI',
									"Georgia, serif"                 => 'Georgia',
									"'Courier New', monospace"       => 'Courier New',
									"'Poppins', sans-serif"           => 'Poppins',
									"'Playfair Display', serif"       => 'Playfair Display',
								);
								foreach ( $fonts as $value => $label ) {
									printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $s['font_family'], $value, false ), esc_html( $label ) );
								}
								?>
							</select>
						</td></tr>
					<tr><th><label for="animation_style"><?php esc_html_e( 'Animation Style', 'sb-contract-animation' ); ?></label></th>
						<td>
							<select name="animation_style" id="animation_style">
								<?php
								$styles = array(
									'fade-up'    => 'Fade Up',
									'zoom-in'    => 'Zoom In',
									'slide-left' => 'Slide From Left',
									'typewriter' => 'Typewriter Text',
								);
								foreach ( $styles as $value => $label ) {
									printf( '<option value="%s"%s>%s</option>', esc_attr( $value ), selected( $s['animation_style'], $value, false ), esc_html( $label ) );
								}
								?>
							</select>
						</td></tr>
					<tr><th><label for="height"><?php esc_html_e( 'Section Height (px)', 'sb-contract-animation' ); ?></label></th>
						<td><input type="number" name="height" id="height" value="<?php echo esc_attr( $s['height'] ); ?>"></td></tr>
				</table>
				<?php submit_button( __( 'Save Hero Settings', 'sb-contract-animation' ), 'primary', 'sbca_hero_save' ); ?>
			</form>
		</div>
		<?php
	}

	/* =====================================================
	 * SMTP SETTINGS
	 * ===================================================== */
	public function render_smtp_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['sbca_smtp_save'] ) && check_admin_referer( 'sbca_save_smtp' ) ) {
			$provider = sanitize_text_field( $_POST['provider'] ?? 'custom' );
			$settings = array(
				'enabled'    => isset( $_POST['enabled'] ) ? 1 : 0,
				'provider'   => $provider,
				'host'       => 'gmail' === $provider ? 'smtp.gmail.com' : sanitize_text_field( $_POST['host'] ?? '' ),
				'port'       => 'gmail' === $provider ? 587 : absint( $_POST['port'] ?? 587 ),
				'encryption' => 'gmail' === $provider ? 'tls' : sanitize_text_field( $_POST['encryption'] ?? 'tls' ),
				'username'   => sanitize_text_field( $_POST['username'] ?? '' ),
				'password'   => sanitize_text_field( $_POST['password'] ?? '' ),
				'from_email' => sanitize_email( $_POST['from_email'] ?? '' ),
				'from_name'  => sanitize_text_field( $_POST['from_name'] ?? '' ),
			);
			update_option( SBCA_SMTP::OPTION_KEY, $settings );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'SMTP settings saved.', 'sb-contract-animation' ) . '</p></div>';
		}

		$s = SBCA_SMTP::get_settings();

		if ( isset( $_GET['sbca_notice'] ) ) {
			$this->print_query_notice( $_GET['sbca_notice'] );
		}
		$last_error = get_option( 'sbca_last_mail_error' );
		?>
		<div class="wrap sbca-wrap">
			<h1><?php esc_html_e( 'SMTP Configuration', 'sb-contract-animation' ); ?></h1>
			<p><?php esc_html_e( 'Send emails reliably through your own web hosting mailbox or through Gmail (with an App Password).', 'sb-contract-animation' ); ?></p>
			<?php if ( $last_error ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html__( 'Last mail error:', 'sb-contract-animation' ) . ' ' . esc_html( $last_error ); ?></p></div>
			<?php endif; ?>
			<form method="post">
				<?php wp_nonce_field( 'sbca_save_smtp' ); ?>
				<table class="form-table">
					<tr><th><?php esc_html_e( 'Enable Custom SMTP', 'sb-contract-animation' ); ?></th>
						<td><label><input type="checkbox" name="enabled" value="1" <?php checked( $s['enabled'], 1 ); ?>> <?php esc_html_e( 'Route all outgoing mail through SMTP', 'sb-contract-animation' ); ?></label></td></tr>
					<tr><th><label for="provider"><?php esc_html_e( 'Provider', 'sb-contract-animation' ); ?></label></th>
						<td>
							<select name="provider" id="sbca-provider">
								<option value="custom" <?php selected( $s['provider'], 'custom' ); ?>><?php esc_html_e( 'Web Hosting Email (custom SMTP)', 'sb-contract-animation' ); ?></option>
								<option value="gmail" <?php selected( $s['provider'], 'gmail' ); ?>><?php esc_html_e( 'Gmail (App Password)', 'sb-contract-animation' ); ?></option>
							</select>
						</td></tr>
					<tr class="sbca-custom-row"><th><label for="host"><?php esc_html_e( 'SMTP Host', 'sb-contract-animation' ); ?></label></th>
						<td><input class="regular-text" type="text" name="host" id="host" value="<?php echo esc_attr( $s['host'] ); ?>" placeholder="mail.yourdomain.com"></td></tr>
					<tr class="sbca-custom-row"><th><label for="port"><?php esc_html_e( 'SMTP Port', 'sb-contract-animation' ); ?></label></th>
						<td><input type="number" name="port" id="port" value="<?php echo esc_attr( $s['port'] ); ?>"></td></tr>
					<tr class="sbca-custom-row"><th><label for="encryption"><?php esc_html_e( 'Encryption', 'sb-contract-animation' ); ?></label></th>
						<td>
							<select name="encryption" id="encryption">
								<option value="tls" <?php selected( $s['encryption'], 'tls' ); ?>>TLS</option>
								<option value="ssl" <?php selected( $s['encryption'], 'ssl' ); ?>>SSL</option>
								<option value="none" <?php selected( $s['encryption'], 'none' ); ?>><?php esc_html_e( 'None', 'sb-contract-animation' ); ?></option>
							</select>
						</td></tr>
					<tr><th><label for="username"><?php esc_html_e( 'SMTP Username / Email', 'sb-contract-animation' ); ?></label></th>
						<td><input class="regular-text" type="text" name="username" id="username" value="<?php echo esc_attr( $s['username'] ); ?>"></td></tr>
					<tr><th><label for="password"><?php esc_html_e( 'SMTP Password / App Password', 'sb-contract-animation' ); ?></label></th>
						<td><input class="regular-text" type="password" name="password" id="password" value="<?php echo esc_attr( $s['password'] ); ?>" autocomplete="new-password">
						<p class="description"><?php esc_html_e( 'For Gmail, generate an App Password from your Google Account security settings (2-Step Verification must be enabled).', 'sb-contract-animation' ); ?></p></td></tr>
					<tr><th><label for="from_email"><?php esc_html_e( 'From Email', 'sb-contract-animation' ); ?></label></th>
						<td><input class="regular-text" type="email" name="from_email" id="from_email" value="<?php echo esc_attr( $s['from_email'] ); ?>"></td></tr>
					<tr><th><label for="from_name"><?php esc_html_e( 'From Name', 'sb-contract-animation' ); ?></label></th>
						<td><input class="regular-text" type="text" name="from_name" id="from_name" value="<?php echo esc_attr( $s['from_name'] ); ?>"></td></tr>
				</table>
				<?php submit_button( __( 'Save SMTP Settings', 'sb-contract-animation' ), 'primary', 'sbca_smtp_save' ); ?>
			</form>

			<hr>
			<h2><?php esc_html_e( 'Send a Test Email', 'sb-contract-animation' ); ?></h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="sbca_send_test_email">
				<?php wp_nonce_field( 'sbca_send_test_email' ); ?>
				<input type="email" name="sbca_test_email" placeholder="you@example.com" required class="regular-text">
				<?php submit_button( __( 'Send Test Email', 'sb-contract-animation' ), 'secondary', 'submit', false ); ?>
			</form>
		</div>
		<script>
		jQuery(function($){
			function toggleProvider(){
				var isGmail = $('#sbca-provider').val() === 'gmail';
				$('.sbca-custom-row').toggle(!isGmail);
			}
			$('#sbca-provider').on('change', toggleProvider);
			toggleProvider();
		});
		</script>
		<?php
	}

	/* =====================================================
	 * EMAIL TEMPLATES
	 * ===================================================== */
	public function render_email_templates_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_POST['sbca_templates_save'] ) && check_admin_referer( 'sbca_save_templates' ) ) {
			$templates = array(
				'admin_to'      => sanitize_email( $_POST['admin_to'] ?? '' ),
				'admin_subject' => sanitize_text_field( $_POST['admin_subject'] ?? '' ),
				'admin_body'    => sanitize_textarea_field( $_POST['admin_body'] ?? '' ),
				'user_subject'  => sanitize_text_field( $_POST['user_subject'] ?? '' ),
				'user_body'     => sanitize_textarea_field( $_POST['user_body'] ?? '' ),
			);
			update_option( SBCA_Emails::OPTION_KEY, $templates );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Email templates saved.', 'sb-contract-animation' ) . '</p></div>';
		}

		$t = SBCA_Emails::get_templates();
		?>
		<div class="wrap sbca-wrap">
			<h1><?php esc_html_e( 'Email Templates', 'sb-contract-animation' ); ?></h1>
			<p><?php esc_html_e( 'Available placeholders:', 'sb-contract-animation' ); ?> <code>{name} {email} {phone} {subject} {message} {site_name} {site_url} {date}</code></p>
			<form method="post">
				<?php wp_nonce_field( 'sbca_save_templates' ); ?>
				<h2><?php esc_html_e( 'Admin Notification Email', 'sb-contract-animation' ); ?></h2>
				<table class="form-table">
					<tr><th><label for="admin_to"><?php esc_html_e( 'Send To', 'sb-contract-animation' ); ?></label></th>
						<td><input class="regular-text" type="email" name="admin_to" id="admin_to" value="<?php echo esc_attr( $t['admin_to'] ); ?>"></td></tr>
					<tr><th><label for="admin_subject"><?php esc_html_e( 'Subject', 'sb-contract-animation' ); ?></label></th>
						<td><input class="large-text" type="text" name="admin_subject" id="admin_subject" value="<?php echo esc_attr( $t['admin_subject'] ); ?>"></td></tr>
					<tr><th><label for="admin_body"><?php esc_html_e( 'Body', 'sb-contract-animation' ); ?></label></th>
						<td><textarea class="large-text" rows="8" name="admin_body" id="admin_body"><?php echo esc_textarea( $t['admin_body'] ); ?></textarea></td></tr>
				</table>

				<h2><?php esc_html_e( 'User Confirmation Email', 'sb-contract-animation' ); ?></h2>
				<table class="form-table">
					<tr><th><label for="user_subject"><?php esc_html_e( 'Subject', 'sb-contract-animation' ); ?></label></th>
						<td><input class="large-text" type="text" name="user_subject" id="user_subject" value="<?php echo esc_attr( $t['user_subject'] ); ?>"></td></tr>
					<tr><th><label for="user_body"><?php esc_html_e( 'Body', 'sb-contract-animation' ); ?></label></th>
						<td><textarea class="large-text" rows="8" name="user_body" id="user_body"><?php echo esc_textarea( $t['user_body'] ); ?></textarea></td></tr>
				</table>
				<?php submit_button( __( 'Save Templates', 'sb-contract-animation' ), 'primary', 'sbca_templates_save' ); ?>
			</form>
		</div>
		<?php
	}

	/* =====================================================
	 * SUBMISSIONS
	 * ===================================================== */
	public function render_submissions_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['sbca_delete'] ) && check_admin_referer( 'sbca_delete_submission' ) ) {
			SBCA_DB::delete_submission( absint( $_GET['sbca_delete'] ) );
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Submission deleted.', 'sb-contract-animation' ) . '</p></div>';
		}

		$paged  = isset( $_GET['paged'] ) ? max( 1, absint( $_GET['paged'] ) ) : 1;
		$limit  = 20;
		$offset = ( $paged - 1 ) * $limit;

		$submissions = SBCA_DB::get_submissions( array( 'limit' => $limit, 'offset' => $offset ) );
		$total       = SBCA_DB::count_submissions();
		$total_pages = (int) ceil( $total / $limit );
		?>
		<div class="wrap sbca-wrap">
			<h1><?php esc_html_e( 'Contract Form Submissions', 'sb-contract-animation' ); ?></h1>
			<p><?php echo esc_html( sprintf( __( 'Total submissions: %d', 'sb-contract-animation' ), $total ) ); ?></p>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th>ID</th><th><?php esc_html_e( 'Name', 'sb-contract-animation' ); ?></th>
						<th><?php esc_html_e( 'Email', 'sb-contract-animation' ); ?></th>
						<th><?php esc_html_e( 'Phone', 'sb-contract-animation' ); ?></th>
						<th><?php esc_html_e( 'Subject', 'sb-contract-animation' ); ?></th>
						<th><?php esc_html_e( 'Message', 'sb-contract-animation' ); ?></th>
						<th><?php esc_html_e( 'Date', 'sb-contract-animation' ); ?></th>
						<th><?php esc_html_e( 'Action', 'sb-contract-animation' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php if ( $submissions ) : ?>
					<?php foreach ( $submissions as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row->id ); ?></td>
							<td><?php echo esc_html( $row->name ); ?></td>
							<td><a href="mailto:<?php echo esc_attr( $row->email ); ?>"><?php echo esc_html( $row->email ); ?></a></td>
							<td><?php echo esc_html( $row->phone ); ?></td>
							<td><?php echo esc_html( $row->subject ); ?></td>
							<td><?php echo esc_html( wp_trim_words( $row->message, 15 ) ); ?></td>
							<td><?php echo esc_html( $row->created_at ); ?></td>
							<td>
								<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'page' => 'sbca-submissions', 'sbca_delete' => $row->id ), admin_url( 'admin.php' ) ), 'sbca_delete_submission' ) ); ?>"
									onclick="return confirm('<?php echo esc_js( __( 'Delete this submission?', 'sb-contract-animation' ) ); ?>');"><?php esc_html_e( 'Delete', 'sb-contract-animation' ); ?></a>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php else : ?>
					<tr><td colspan="8"><?php esc_html_e( 'No submissions yet.', 'sb-contract-animation' ); ?></td></tr>
				<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $total_pages > 1 ) : ?>
				<div class="tablenav"><div class="tablenav-pages">
					<?php
					echo paginate_links( array( // phpcs:ignore
						'base'      => add_query_arg( 'paged', '%#%' ),
						'format'    => '',
						'current'   => $paged,
						'total'     => $total_pages,
					) );
					?>
				</div></div>
			<?php endif; ?>

			<p><?php esc_html_e( 'Place the form anywhere with the shortcode:', 'sb-contract-animation' ); ?> <code>[sb_contract_form]</code></p>
		</div>
		<?php
	}

	/* =====================================================
	 * ACTIVATION
	 * ===================================================== */
	public function render_activation_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( isset( $_GET['sbca_notice'] ) ) {
			$this->print_query_notice( $_GET['sbca_notice'] );
		}

		$is_active = SBCA_Activation::is_active();
		$saved_email = get_option( SBCA_Activation::OPTION_EMAIL );
		?>
		<div class="wrap sbca-wrap">
			<h1><?php esc_html_e( 'Plugin Activation', 'sb-contract-animation' ); ?></h1>

			<?php if ( $is_active ) : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'This plugin is activated. Thank you!', 'sb-contract-animation' ); ?></p></div>
			<?php else : ?>
				<p><?php esc_html_e( 'Enter a real email address you have access to. We will send you a 6-digit code to confirm and activate the plugin.', 'sb-contract-animation' ); ?></p>

				<h2><?php esc_html_e( 'Step 1: Send Code', 'sb-contract-animation' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="sbca_send_activation_code">
					<?php wp_nonce_field( 'sbca_send_activation_code' ); ?>
					<input type="email" name="sbca_email" class="regular-text" placeholder="you@example.com" value="<?php echo esc_attr( $saved_email ); ?>" required>
					<?php submit_button( __( 'Send Activation Code', 'sb-contract-animation' ), 'primary', 'submit', false ); ?>
				</form>

				<h2><?php esc_html_e( 'Step 2: Confirm Code', 'sb-contract-animation' ); ?></h2>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="sbca_confirm_activation_code">
					<?php wp_nonce_field( 'sbca_confirm_activation_code' ); ?>
					<input type="text" name="sbca_code" class="regular-text" placeholder="123456" required>
					<?php submit_button( __( 'Confirm & Activate', 'sb-contract-animation' ), 'primary', 'submit', false ); ?>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/* =====================================================
	 * HELP / SHORTCODES / UPDATES
	 * ===================================================== */
	public function render_help_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap sbca-wrap">
			<h1><?php esc_html_e( 'Shortcodes & GitHub Updates', 'sb-contract-animation' ); ?></h1>

			<h2><?php esc_html_e( 'Shortcodes', 'sb-contract-animation' ); ?></h2>
			<table class="widefat" style="max-width:700px;">
				<tr><td><code>[sb_hero]</code></td><td><?php esc_html_e( 'Displays the animated hero section.', 'sb-contract-animation' ); ?></td></tr>
				<tr><td><code>[sb_contract_form]</code></td><td><?php esc_html_e( 'Displays the contract/contact form.', 'sb-contract-animation' ); ?></td></tr>
				<tr><td><code>[sb_contract_form title="Custom Title"]</code></td><td><?php esc_html_e( 'Form with a custom heading.', 'sb-contract-animation' ); ?></td></tr>
			</table>

			<h2 style="margin-top:30px;"><?php esc_html_e( 'GitHub Updates', 'sb-contract-animation' ); ?></h2>
			<p><?php esc_html_e( 'This plugin checks the following GitHub repository for new releases and will show an update notice on the Plugins page automatically, just like a plugin from WordPress.org.', 'sb-contract-animation' ); ?></p>
			<p><strong><?php esc_html_e( 'Repository:', 'sb-contract-animation' ); ?></strong> <code><?php echo esc_html( SBCA_GITHUB_REPO ); ?></code></p>
			<p><?php esc_html_e( 'To publish an update: bump the "Version" number in the plugin header, then create a new GitHub Release (with a tag such as 1.0.1) on your repository. WordPress will then offer the update automatically.', 'sb-contract-animation' ); ?></p>
			<p><a href="<?php echo esc_url( admin_url( 'plugins.php' ) ); ?>" class="button"><?php esc_html_e( 'Go to Plugins Page', 'sb-contract-animation' ); ?></a></p>
		</div>
		<?php
	}

	private function print_query_notice( $notice ) {
		$notice = sanitize_text_field( wp_unslash( $notice ) );
		$map = array(
			'invalid_email' => array( 'error', __( 'Please enter a valid email address.', 'sb-contract-animation' ) ),
			'code_sent'     => array( 'success', __( 'Activation code sent. Check your inbox.', 'sb-contract-animation' ) ),
			'wrong_code'    => array( 'error', __( 'Incorrect code. Please try again.', 'sb-contract-animation' ) ),
			'activated'     => array( 'success', __( 'Plugin activated successfully!', 'sb-contract-animation' ) ),
			'test_sent'     => array( 'success', __( 'Test email sent successfully.', 'sb-contract-animation' ) ),
			'test_failed'   => array( 'error', __( 'Test email failed to send. Check your SMTP settings.', 'sb-contract-animation' ) ),
		);
		if ( isset( $map[ $notice ] ) ) {
			printf( '<div class="notice notice-%s is-dismissible"><p>%s</p></div>', esc_attr( $map[ $notice ][0] ), esc_html( $map[ $notice ][1] ) );
		}
	}
}
