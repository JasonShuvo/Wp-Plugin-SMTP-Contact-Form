<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the animated Hero section via the [sb_hero] shortcode.
 * All text, colors, fonts, background image and animation style are
 * configurable from the WP admin.
 */
class SBCA_Hero {

	const OPTION_KEY = 'sbca_hero_settings';

	public function __construct() {
		add_shortcode( 'sb_hero', array( $this, 'render_shortcode' ) );
	}

	public static function get_settings() {
		$defaults = array(
			'title'            => 'Build Something Great',
			'subtitle'         => 'We craft animated, high-converting websites for modern businesses.',
			'button_text'      => 'Get Started',
			'button_url'       => '#sb-contract-form',
			'bg_image'         => '',
			'bg_color'         => '#0f172a',
			'title_color'      => '#ffffff',
			'subtitle_color'   => '#e2e8f0',
			'button_bg_color'  => '#6366f1',
			'button_text_color'=> '#ffffff',
			'font_family'      => "'Segoe UI', Arial, sans-serif",
			'animation_style'  => 'fade-up', // fade-up, zoom-in, slide-left, typewriter
			'height'           => '520',
		);

		$saved = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $saved, $defaults );
	}

	public function render_shortcode( $atts ) {
		$s = self::get_settings();

		wp_enqueue_style( 'sbca-style' );
		wp_enqueue_script( 'sbca-animation' );

		$bg_style = ! empty( $s['bg_image'] )
			? "background-image:url('" . esc_url( $s['bg_image'] ) . "');background-size:cover;background-position:center;"
			: '';

		ob_start();
		?>
		<section class="sbca-hero sbca-anim-<?php echo esc_attr( $s['animation_style'] ); ?>"
			style="background-color:<?php echo esc_attr( $s['bg_color'] ); ?>;<?php echo esc_attr( $bg_style ); ?>font-family:<?php echo esc_attr( $s['font_family'] ); ?>;min-height:<?php echo esc_attr( $s['height'] ); ?>px;">
			<div class="sbca-hero-overlay"></div>
			<div class="sbca-hero-inner">
				<h1 class="sbca-hero-title" style="color:<?php echo esc_attr( $s['title_color'] ); ?>;" data-sbca-text="<?php echo esc_attr( $s['title'] ); ?>">
					<?php echo esc_html( $s['title'] ); ?>
				</h1>
				<p class="sbca-hero-subtitle" style="color:<?php echo esc_attr( $s['subtitle_color'] ); ?>;">
					<?php echo esc_html( $s['subtitle'] ); ?>
				</p>
				<?php if ( ! empty( $s['button_text'] ) ) : ?>
					<a href="<?php echo esc_url( $s['button_url'] ); ?>" class="sbca-hero-btn"
						style="background-color:<?php echo esc_attr( $s['button_bg_color'] ); ?>;color:<?php echo esc_attr( $s['button_text_color'] ); ?>;">
						<?php echo esc_html( $s['button_text'] ); ?>
					</a>
				<?php endif; ?>
			</div>
		</section>
		<?php
		return ob_get_clean();
	}
}
