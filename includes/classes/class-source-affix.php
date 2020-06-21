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

		// nsdump( $this->get_option('sa_source_posttypes') );
		// nsdump( $this->get_option('sa_source_title') );

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
			wp_enqueue_style( 'source-affix-plugin-styles', SOURCE_AFFIX_URL . '/css/public.css', array(), self::VERSION );
		}
	}

	/**
	 * Affix source to the content.
	 *
	 * @param  $content The content.
	 * @return The content with affixed source.
	 */
	function source_affix_affix_sa_source( $content ) {
		$options = $this->options;

		if ( $options ) {
			extract( $options );
		}

		$available_post_types_array = array_keys( $sa_source_posttypes );

		$current_post_type          = get_post_type( get_the_ID() );

		if ( ! in_array( $current_post_type, $available_post_types_array ) ) {
			return $content;
		}

		$sa_source = get_post_meta( get_the_ID(), 'sa_source', true );

		if ( '' != $sa_source ) {
			$links_array = source_affix_convert_meta_to_array( $sa_source );

			$single_link = array();

			if ( ! empty( $links_array ) && is_array( $links_array ) ) {
				foreach ( $links_array  as $key => $eachline ) {
					if ( ! empty( $eachline['url'] ) ) {
						$lnk  = '<a href="' . $eachline['url'] . '" ';
						$lnk .= ( $sa_source_open_style == 'BLANK' ) ? ' target="_blank" ' : '';
						$lnk .= ' >' . esc_attr( $eachline['title'] ) . '</a>';
					} else {
						$lnk = esc_attr( $eachline['title'] );
					}

					$single_link[] = $lnk;
				}
			}

			$source_message  = '<div class="sa-source-wrapper">';
			$source_message .= '<div class="sa-source-inner">';

			if ( $sa_source_title ) {
				$source_message .= '<span class="source-title">' . $sa_source_title . '</span>';
			}

			$source_message .= '<div class="sa-source-content">';

			switch ( $sa_source_style ) {
				case 'COMMA':
					$source_message .= '<div class="news-source">' . implode( ', ', $single_link ) . '</div>';
					break;

				case 'LIST':
					if ( ! empty( $single_link ) ) {
						$source_message .= '<ul class="list-source-links">';
						$source_message .= '<li>' . implode( '</li><li>', $single_link ) . '</li>';
						$source_message .= '</ul>';
					}

					break;

				case 'ORDEREDLIST':
					if ( ! empty( $single_link ) ) {
						$source_message .= '<ol class="list-source-links">';
						$source_message .= '<li>' . implode( '</li><li>', $single_link ) . '</li>';
						$source_message .= '</ol>';
					}

					break;

				default:
					break;
			}

			$source_message .= '</div>';
			$source_message .= '</div>';
			$source_message .= '</div>';

			if ( is_singular() && 'NO' !== $options['sa_source_position'] ) {
				if ( 'APPEND' == $sa_source_position ) {
					$content = $content . $source_message;
				} else {
					$content = $source_message . $content;
				}
			}
		}

		return $content;
	}

	/**
	 * Get plugin options details.
	 *
	 * @since 1.0.0
	 *
	 * @return array Options array.
	 */
	public function source_affix_get_options_array() {
		return $this->options;
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
