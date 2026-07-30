<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A minimal GitHub-releases based updater.
 *
 * Checks https://api.github.com/repos/{owner}/{repo}/releases/latest
 * and, if the tag_name is newer than the currently installed version,
 * surfaces a normal WordPress "update available" notice with a link
 * to the release's zip asset (or the auto-generated source zip).
 */
class SBCA_Updater {

	private $plugin_file;
	private $repo;
	private $version;
	private $plugin_slug;
	private $cache_key;

	public function __construct( $plugin_file, $repo, $version ) {
		$this->plugin_file = $plugin_file;
		$this->repo        = $repo;
		$this->version     = $version;
		$this->plugin_slug = plugin_basename( $plugin_file );
		$this->cache_key   = 'sbca_github_release_' . md5( $repo );

		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_for_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_info' ), 20, 3 );
		add_filter( 'upgrader_source_selection', array( $this, 'fix_source_dir' ), 10, 4 );
	}

	private function get_release() {
		$cached = get_transient( $this->cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$response = wp_remote_get(
			'https://api.github.com/repos/' . $this->repo . '/releases/latest',
			array(
				'headers' => array( 'Accept' => 'application/vnd.github+json' ),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( empty( $body['tag_name'] ) ) {
			return false;
		}

		set_transient( $this->cache_key, $body, 12 * HOUR_IN_SECONDS );
		return $body;
	}

	public function check_for_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release = $this->get_release();
		if ( ! $release ) {
			return $transient;
		}

		$remote_version = ltrim( $release['tag_name'], 'v' );

		if ( version_compare( $remote_version, $this->version, '>' ) ) {
			$zip_url = ! empty( $release['zipball_url'] ) ? $release['zipball_url'] : '';

			$transient->response[ $this->plugin_slug ] = (object) array(
				'slug'        => dirname( $this->plugin_slug ),
				'plugin'      => $this->plugin_slug,
				'new_version' => $remote_version,
				'url'         => 'https://github.com/' . $this->repo,
				'package'     => $zip_url,
			);
		}

		return $transient;
	}

	public function plugin_info( $result, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) || $args->slug !== dirname( $this->plugin_slug ) ) {
			return $result;
		}

		$release = $this->get_release();
		if ( ! $release ) {
			return $result;
		}

		return (object) array(
			'name'          => 'SB Contract & Animation',
			'slug'          => dirname( $this->plugin_slug ),
			'version'       => ltrim( $release['tag_name'], 'v' ),
			'author'        => '<a href="https://github.com/' . esc_attr( $this->repo ) . '">SB</a>',
			'homepage'      => 'https://github.com/' . $this->repo,
			'sections'      => array(
				'description' => isset( $release['body'] ) ? wp_kses_post( $release['body'] ) : '',
			),
			'download_link' => isset( $release['zipball_url'] ) ? $release['zipball_url'] : '',
		);
	}

	/**
	 * GitHub's auto-zip extracts into a folder like "owner-repo-hash".
	 * Rename it to match the plugin's own folder name so WordPress
	 * updates the existing plugin instead of creating a duplicate.
	 */
	public function fix_source_dir( $source, $remote_source, $upgrader, $args ) {
		global $wp_filesystem;

		if ( empty( $args['plugin'] ) || $args['plugin'] !== $this->plugin_slug ) {
			return $source;
		}

		$target_dir = trailingslashit( $remote_source ) . dirname( $this->plugin_slug );

		if ( $source !== $target_dir && $wp_filesystem->move( $source, $target_dir ) ) {
			return $target_dir;
		}

		return $source;
	}
}
