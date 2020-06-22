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
	const VERSION = '2.0.0';

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
	 * Plugin options.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $options = array();

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

		// Load public-facing assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Define custom functionality.
		add_filter( 'the_content', array( $this, 'source_affix_affix_sa_source' ) );
		add_shortcode( 'source_affix', array( $this, 'render_source_affix_content' ) );
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin slug.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}

	/**
	 * Return default options.
	 *
	 * @since 1.0.0
	 *
	 * @return Plugin slug.
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
		$this->default_options =  array(
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
	 * Register and enqueue public-facing assets.
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

	public function get_post_source_links( $post_id, $args = array() ) {
		$output = array();

		$defaults = array(
			'new_window' => false,
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

	public function get_source_links_markup( $post_id, $type = 'COMMA' ) {
		$links = $this->get_post_source_links( $post_id, array() );

		if ( ! is_array( $links ) || empty( $links ) ) {
			return;
		}

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

	public function get_source_content_markup( $post_id, $args = array() ) {
		$html = '';

		$defaults = array(
			'title' => '',
			'type'  => 'COMMA',
		);

		$args = wp_parse_args( $args, $defaults );

		$links_content = $this->get_source_links_markup( $post_id, $args['type'] );

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
	 * @param  $content The content.
	 * @return The content with affixed source.
	 */
	function source_affix_affix_sa_source( $content ) {
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

				$source_content = $this->get_source_content_markup( $current_post_id );

				if ( 'APPEND' === $sa_source_position ) {
					$content = $content . $source_content;
				} else {
					$content = $source_content . $content;
				}
	    	}
	    }

		return $content;
	}

	public function render_source_affix_content( $atts ) {
		$defaults = array(
			'id'         => null,
			'title'      => esc_html__( 'Source :', 'source-affix' ),
			'style'      => 'comma',
			'new_window' => true,
		);

		$options = shortcode_atts( $defaults, $atts );

		if ( ! $options['id'] ) {
			if ( is_singular() ) {
				$options['id'] = get_the_ID();
			}
		}

		if ( 0 === absint( $options['id'] ) ) {
			return;
		}

		$sa_source = get_post_meta( $options['id'], 'sa_source', true );

		$links_array = array();

		if ( ! empty( $sa_source ) ) {
			$links_array = source_affix_convert_meta_to_array( $sa_source );
		}

		if ( empty( $links_array ) ) {
			return;
		}

		ob_start();

		echo '<div class="sa-source-wrapper">';
		echo '<div class="sa-source-inner">';

		if ( ! empty( $options['title'] ) ) {
			echo '<span class="source-title">' . esc_html( $options['title'] ) . '</span>';
		}

		$single_link = array();

		if ( ! empty( $links_array ) && is_array( $links_array ) ) {
			foreach ( $links_array as $key => $eachline ) {
				if ( ! empty( $eachline['url'] ) ) {
					$lnk  = '<a href="' . esc_url( $eachline['url'] ) . '" ';
					$lnk .= ( true === $options['new_window'] ) ? ' target="_blank" ' : '';
					$lnk .= ' >' . esc_html( $eachline['title'] ) . '</a>';
				} else {
					$lnk = esc_attr( $eachline['title'] );
				}

				$single_link[] = $lnk;
			}
		}

		$source_message = '';

		switch ( $options['style'] ) {
			case 'comma':
				$source_message .= '<div class="news-source">' . implode( ', ', $single_link ) . '</div>';
				break;

			case 'list':
				if ( ! empty( $single_link ) ) {
					$source_message .= '<ul class="list-source-links">';
					$source_message .= '<li>' . implode( '</li><li>', $single_link ) . '</li>';
					$source_message .= '</ul>';
				}

				break;

			case 'orderedlist':
				if ( ! empty( $single_link ) ) {
					$source_message .= '<ol class="list-source-links">';
					$source_message .= '<li>' . implode( '</li><li>', $single_link ) . '</li>';
					$source_message .= '</ol>';
				}

				break;

			default:
				break;
		}

		echo '<div class="sa-source-content">';

		echo $source_message;

		echo '</div>';
		echo '</div>';
		echo '</div>';

		return ob_get_clean();
	}
}
