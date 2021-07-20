<?php
/**
 * Source Affix
 *
 * @package Source_Affix
 */

/**
 * Source Affix Plugin class.
 *
 * @since 1.0.0
 */
class Source_Affix {

	/**
	 * Plugin version.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const VERSION = '2.0.1';

	/**
	 * Plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	protected $plugin_slug = 'source-affix';

	/**
	 * Plugin default options.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $default_options = array();

	/**
	 * Instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Options.
		$this->set_default_options();

		// Load plugin text domain.
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Load public assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Define custom functionality.
		add_filter( 'the_content', array( $this, 'append_sa_source' ) );
		add_shortcode( 'source_affix', array( $this, 'render_source_affix_content' ) );

		// Migrate options.
		add_action( 'init', array( $this, 'migrate_options' ) );
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string Plugin slug.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return default options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Defaults.
	 */
	public function get_defaults() {
		return $this->default_options;
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Get plugin option.
	 *
	 * @since 2.0.0
	 *
	 * @param string $key Option key.
	 * @return mixed Option value.
	 */
	public function get_option( $key ) {
		if ( empty( $key ) ) {
			return;
		}

		$plugin_options = (array) get_option( 'sa_plugin_options' );
		$plugin_options = array_merge( $this->default_options, $plugin_options );

		$value = null;

		if ( isset( $plugin_options[ $key ] ) ) {
			$value = $plugin_options[ $key ];
		}

		return $value;
	}

	/**
	 * Set default options.
	 *
	 * @since 2.0.0
	 */
	public function set_default_options() {
		$this->default_options = array(
			'sa_source_posttypes'   => array( 'post' ),
			'sa_source_title'       => esc_html__( 'Source :', 'source-affix' ),
			'sa_source_style'       => 'COMMA',
			'sa_source_open_style'  => 'BLANK',
			'sa_source_position'    => 'APPEND',
			'sa_load_plugin_styles' => 'YES',
			'sa_make_required'      => 'NO',
		);
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'source-affix' );
	}

	/**
	 * Register and enqueue public assets.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets() {
		$sa_load_plugin_styles = $this->get_option( 'sa_load_plugin_styles' );

		if ( 'YES' === $sa_load_plugin_styles ) {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

			wp_enqueue_style( 'source-affix-plugin-styles', SOURCE_AFFIX_URL . '/assets/css/public' . $min . '.css', array(), self::VERSION );
		}
	}

	/**
	 * Get source links details.
	 *
	 * @since 2.0.0
	 *
	 * @param int $post_id Post ID.
	 * @return array Links details.
	 */
	public function get_post_source_details( $post_id ) {
		$output = array();

		$sa_source = get_post_meta( $post_id, 'sa_source', true );

		if ( $sa_source ) {
			$links_array = source_affix_convert_meta_to_array( $sa_source );

			if ( ! empty( $links_array ) ) {
				$output = $links_array;
			}
		}

		return $output;
	}

	/**
	 * Get post source links.
	 *
	 * @since 2.0.0
	 *
	 * @param int   $post_id Post ID.
	 * @param array $args Arguments.
	 * @return array Links.
	 */
	public function get_post_source_links( $post_id, $args = array() ) {
		$output = array();

		$defaults = array(
			'new_window' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		$links = $this->get_post_source_details( $post_id );

		if ( ! empty( $links ) ) {
			foreach ( $links as $link ) {
				$item = '';

				if ( ! empty( $link['title'] ) ) {
					if ( isset( $link['url'] ) && ! empty( $link['url'] ) ) {
						$target_string = '';

						if ( true === $args['new_window'] ) {
							$target_string .= ' target="_blank"';
						}

						$item = '<a href="' . esc_url( $link['url'] ) . '" ' . ( $target_string ? $target_string : '' ) . '>' . esc_html( $link['title'] ) . '</a>';
					} else {
						$item = esc_html( $link['title'] );
					}
				}

				$output[] = $item;
			}
		}

		return $output;
	}

	/**
	 * Get source links markup.
	 *
	 * @since 2.0.0
	 *
	 * @param int   $post_id Post ID.
	 * @param array $args Arguments.
	 * @return string Content.
	 */
	public function get_source_links_markup( $post_id, $args = array() ) {
		$defaults = array(
			'style'      => 'COMMA',
			'new_window' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		$links = $this->get_post_source_links( $post_id, $args );

		if ( ! is_array( $links ) || empty( $links ) ) {
			return;
		}

		$type = $args['style'];

		$html = '';

		switch ( $type ) {
			case 'COMMA':
				$html .= '<div class="news-source">' . implode( ', ', $links ) . '</div>';
				break;

			case 'LIST':
				$html .= '<ul class="list-source-links">';
				$html .= '<li>' . implode( '</li><li>', $links ) . '</li>';
				$html .= '</ul>';

				break;

			case 'ORDEREDLIST':
				$html .= '<ol class="list-source-links">';
				$html .= '<li>' . implode( '</li><li>', $links ) . '</li>';
				$html .= '</ol>';

				break;

			default:
				break;
		}

		return $html;
	}

	/**
	 * Get source content markup.
	 *
	 * @since 2.0.0
	 *
	 * @param int   $post_id Post ID.
	 * @param array $args Arguments.
	 * @return string Content.
	 */
	public function get_source_content_markup( $post_id, $args = array() ) {
		$html = '';

		$defaults = array(
			'title'      => '',
			'style'      => 'COMMA',
			'new_window' => true,
		);

		$args = wp_parse_args( $args, $defaults );

		$links_content = $this->get_source_links_markup( $post_id, $args );

		if ( empty( $links_content ) ) {
			return;
		}

		ob_start();
		?>
		<div class="sa-source-wrapper">
			<div class="sa-source-inner">
				<?php if ( ! empty( $args['title'] ) ) : ?>
					<span class="source-title"><?php echo esc_html( $args['title'] ); ?></span>
				<?php endif; ?>
				<div class="sa-source-content">
					<?php echo wp_kses_post( $links_content ); ?>
				</div><!-- .sa-source-content -->
			</div><!-- .sa-source-inner -->
		</div><!-- .sa-source-wrapper -->
		<?php
		$html = ob_get_clean();

		return $html;
	}

	/**
	 * Affix source to the content.
	 *
	 * @since 1.0.0
	 *
	 * @param string $content The content.
	 * @return string The content with affixed source.
	 */
	public function append_sa_source( $content ) {
		// Check if we're inside the main loop in a single post.
		if ( is_singular() && in_the_loop() && is_main_query() ) {
			$sa_source_position = $this->get_option( 'sa_source_position' );

			if ( 'NO' !== $sa_source_position ) {
				$current_post_id = get_the_ID();

				$sa_source_posttypes = $this->get_option( 'sa_source_posttypes' );

				$current_post_type = get_post_type( $current_post_id );

				if ( ! in_array( $current_post_type, $sa_source_posttypes, true ) ) {
					return $content;
				}

				$params = array(
					'title'      => $this->get_option( 'sa_source_title' ),
					'style'      => $this->get_option( 'sa_source_style' ),
					'new_window' => ( 'BLANK' === $this->get_option( 'sa_source_open_style' ) ) ? true : false,
				);

				$source_content = $this->get_source_content_markup( $current_post_id, $params );

				if ( 'APPEND' === $sa_source_position ) {
					$content = $content . $source_content;
				} else {
					$content = $source_content . $content;
				}
			}
		}

		return $content;
	}

	/**
	 * Shortcode callback.
	 *
	 * @since 1.0.0
	 *
	 * @param array $atts Parameters.
	 * @return string Shortcode output.
	 */
	public function render_source_affix_content( $atts ) {
		$defaults = array(
			'id'         => null,
			'title'      => esc_html__( 'Source :', 'source-affix' ),
			'style'      => 'comma',
			'new_window' => true,
		);

		$atts = shortcode_atts( $defaults, $atts );

		if ( ! $atts['id'] ) {
			if ( is_singular() ) {
				$atts['id'] = get_the_ID();
			}
		}

		if ( 0 === absint( $atts['id'] ) ) {
			return;
		}

		$params = array(
			'title'      => $atts['title'],
			'style'      => strtoupper( $atts['style'] ),
			'new_window' => rest_sanitize_boolean( $atts['new_window'] ),
		);

		return $this->get_source_content_markup( $atts['id'], $params );
	}

	/**
	 * Migrate options.
	 *
	 * @since 2.0.0
	 */
	public function migrate_options() {
		if ( 'yes' === get_option( 'nssa_option_migration_complete' ) ) {
			return;
		}

		$opt = get_option( 'sa_plugin_options' );

		if ( $opt ) {
			if ( isset( $opt['sa_source_posttypes'] ) && ! empty( $opt['sa_source_posttypes'] ) ) {

				$values = array_keys( $opt['sa_source_posttypes'] );

				$values = array_filter( $values );

				if ( ! empty( $values ) ) {
					$opt['sa_source_posttypes'] = $values;
				}

				update_option( 'sa_plugin_options', $opt );
			}
		}

		update_option( 'nssa_option_migration_complete', 'yes' );
	}
}
