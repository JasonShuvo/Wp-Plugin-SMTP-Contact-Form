<?php
/**
 * Plugin Name:       SB Contract & Animation
 * Plugin URI:        https://github.com/YOUR-USERNAME/sb-contract-animation
 * Description:       Animated hero section + SMTP (Gmail/Web Email) + configurable Contract/Contact form with admin-manageable submissions, email templates, email-based activation, and GitHub-based updates.
 * Version:           1.0.0
 * Author:             SB
 * Author URI:        https://github.com/YOUR-USERNAME
 * License:            GPL v2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:        sb-contract-animation
 * Domain Path:        /languages
 * Requires at least:  5.5
 * Requires PHP:       7.2
 */

// Block direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ------------------------------------------------------------------
 * Constants
 * ------------------------------------------------------------------
 */
define( 'SBCA_VERSION', '1.0.0' );
define( 'SBCA_PLUGIN_FILE', __FILE__ );
define( 'SBCA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SBCA_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SBCA_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// The developer's own email address. Whenever a site owner activates
// the plugin with a real email, a heads-up notification is sent here.
// Change this to your own address before you publish the plugin on GitHub.
define( 'SBCA_DEVELOPER_NOTIFY_EMAIL', 'belivers.jsa@gmail.com' );

// Default GitHub repository the "Check for Updates" screen points to.
// Format: username/repository  (edit after you push this plugin to GitHub).
define( 'SBCA_GITHUB_REPO', 'JasonShuvo/Wp-Plugin-SMTP-Contact-Form' );

/**
 * ------------------------------------------------------------------
 * Includes
 * ------------------------------------------------------------------
 */
require_once SBCA_PLUGIN_DIR . 'includes/class-sb-db.php';
require_once SBCA_PLUGIN_DIR . 'includes/class-sb-activation.php';
require_once SBCA_PLUGIN_DIR . 'includes/class-sb-smtp.php';
require_once SBCA_PLUGIN_DIR . 'includes/class-sb-emails.php';
require_once SBCA_PLUGIN_DIR . 'includes/class-sb-hero.php';
require_once SBCA_PLUGIN_DIR . 'includes/class-sb-form.php';
require_once SBCA_PLUGIN_DIR . 'includes/class-sb-admin.php';
require_once SBCA_PLUGIN_DIR . 'includes/class-sb-updater.php';

/**
 * ------------------------------------------------------------------
 * Activation / Deactivation hooks
 * ------------------------------------------------------------------
 */
register_activation_hook( __FILE__, array( 'SBCA_DB', 'create_tables' ) );
register_activation_hook( __FILE__, array( 'SBCA_Activation', 'on_plugin_activate' ) );

/**
 * ------------------------------------------------------------------
 * Boot the plugin
 * ------------------------------------------------------------------
 */
function sbca_run_plugin() {
	new SBCA_DB();
	new SBCA_Activation();
	new SBCA_SMTP();
	new SBCA_Emails();
	new SBCA_Hero();
	new SBCA_Form();
	new SBCA_Admin();
	new SBCA_Updater( SBCA_PLUGIN_FILE, SBCA_GITHUB_REPO, SBCA_VERSION );
}
add_action( 'plugins_loaded', 'sbca_run_plugin' );

/**
 * Front-end assets (only loaded when a relevant shortcode is present).
 */
function sbca_register_assets() {
	wp_register_style( 'sbca-style', SBCA_PLUGIN_URL . 'assets/css/sb-style.css', array(), SBCA_VERSION );
	wp_register_script( 'sbca-animation', SBCA_PLUGIN_URL . 'assets/js/sb-animation.js', array(), SBCA_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'sbca_register_assets' );

/**
 * Plugin action links (Settings link on the Plugins page).
 */
function sbca_action_links( $links ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=sbca-settings' ) . '">' . esc_html__( 'Settings', 'sb-contract-animation' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
add_filter( 'plugin_action_links_' . SBCA_PLUGIN_BASENAME, 'sbca_action_links' );
