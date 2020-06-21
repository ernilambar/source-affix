<?php
/**
 * Source Affix
 *
 * @package Source_Affix
 */

use Nilambar\Optioner\Optioner;

/**
 * Source Affix Admin class.
 */
class Source_Affix_Admin {
	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Plugin options.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	protected $options = array();

	/**
	 * Optioner instance.
	 *
	 * @since 2.0.0
	 *
	 * @var Optioner
	 */
	protected $optioner;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		// Call $plugin_slug from public plugin class.
		$plugin            = Source_Affix::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();
		$this->options     = $plugin->source_affix_get_options_array();

		$this->optioner = new Optioner();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'source_affix_add_plugin_admin_menu' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'source-affix.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'source_affix_add_action_links' ) );

		// Add the post meta box to the post editor.
		add_action( 'add_meta_boxes', array( $this, 'source_affix_add_sa_metabox' ) );
		add_action( 'save_post', array( $this, 'source_affix_save_sa_source' ), 10, 2 );

		if ( 'YES' === $this->options['sa_make_required'] ) {
			add_action( 'save_post', array( $this, 'source_affix_check_required' ), 11, 2 );
		}

		add_action( 'admin_init', array( $this, 'source_affix_plugin_register_settings' ) );

		add_action( 'admin_notices', array( $this, 'show_admin_notices' ) );

		// Setup admin page.
		add_action( 'init', array( $this, 'setup_admin_page' ) );
	}

	/**
	 * Setup admin page.
	 *
	 * @since 2.0.0
	 */
	public function setup_admin_page() {
		$this->optioner->set_page(
			array(
				'page_title'  => esc_html__( 'Source Affix', 'source-affix' ),
				'menu_title'  => esc_html__( 'Source Affix', 'source-affix' ),
				'capability'  => 'manage_options',
				'menu_slug'   => 'sasa',
				'option_slug' => 'sa_plugin_options',
			)
		);

		// Tab: nsfp_settings_tab.
		$this->optioner->add_tab(
			array(
				'id'    => 'nsfp_settings_tab',
				'title' => esc_html__( 'Settings', 'source-affix' ),
			)
		);

		// Sidebar.
		$this->optioner->set_sidebar(
			array(
				'render_callback' => array( $this, 'render_sidebar' ),
				'width'           => 30,
			)
		);

		// Run now.
		$this->optioner->run();
	}


	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Register and enqueue admin-specific style sheet.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		$screen = get_current_screen();

		$options = $this->options;

		if ( $options ) {
			extract( $options );
		}

		$available_post_types_array = array_keys( $sa_source_posttypes );

		if ( in_array( $screen->id, $available_post_types_array ) ) {
			wp_enqueue_style( 'source-affix-admin-styles', plugins_url( 'css/admin.css', __FILE__ ), array(), Source_Affix::VERSION );
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 *
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		$screen = get_current_screen();

		$options = $this->options;

		if ( $options ) {
			extract( $options );
		}

		$available_post_types_array = array_keys( $sa_source_posttypes );

		if ( in_array( $screen->id, $available_post_types_array ) ) {

			$extra_array = array(
				'lang' => array(
					'are_you_sure'   => __( 'Are you sure?', 'source-affix' ),
					'enter_title'    => __( 'Enter Title', 'source-affix' ),
					'enter_full_url' => __( 'Enter Full URL', 'source-affix' ),
				),
			);

			wp_register_script( 'source-affix-admin-script', plugins_url( 'js/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-sortable' ), Source_Affix::VERSION );
			wp_localize_script( 'source-affix-admin-script', 'SAF_OBJ', $extra_array );
			wp_enqueue_script( 'source-affix-admin-script' );
		}
	}

	/**
	 * Register the administration menu.
	 *
	 * @since    1.0.0
	 */
	public function source_affix_add_plugin_admin_menu() {
		$this->plugin_screen_hook_suffix = add_options_page(
			__( 'Source Affix', 'source-affix' ),
			__( 'Source Affix', 'source-affix' ),
			'manage_options',
			'source-affix',
			array( $this, 'display_plugin_admin_page' )
		);
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		// Check that the user is allowed to update options
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		include_once SOURCE_AFFIX_DIR . '/views/admin.php';
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function source_affix_add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . esc_url( admin_url( 'options-general.php?page=' . $this->plugin_slug ) ) . '">' . __( 'Settings', 'source-affix' ) . '</a>',
			),
			$links
		);
	}

	/**
	 * Adds the meta box below the post content editor on the post edit dashboard.
	 */
	function source_affix_add_sa_metabox() {
		$options = $this->options;

		if ( $options ) {
			extract( $options );
		}

		$available_post_types_array = array_keys( $sa_source_posttypes );

		if ( ! empty( $available_post_types_array ) ) {
			foreach ( $available_post_types_array as $ptype ) {
				add_meta_box(
					'sa_source',
					__( 'Sources', 'source-affix' ),
					array( $this, 'source_affix_sa_source_display' ),
					$ptype,
					'normal',
					'high'
				);
			}
		}
	}

	/**
	 * Renders the nonce and the textarea for the notice.
	 */
	function source_affix_sa_source_display( $post ) {
		wp_nonce_field( plugin_basename( __FILE__ ), 'sa_source_nonce' );

		$source_meta = get_post_meta( $post->ID, 'sa_source', true );

		$links_array = source_affix_convert_meta_to_array( $source_meta );

		echo '<ul id="list-source-link">';

		if ( ! empty( $links_array ) && is_array( $links_array ) ) {
			foreach ( $links_array as $key => $link ) {
				echo '<li>';
				echo '<span class="btn-move-source-link"><i class="dashicons dashicons-sort"></i></span>';
				echo '<input type="text" name="link_title[]" value="' . esc_attr( $link['title'] ) . '"  class="regular-text1 code" placeholder="' . __( 'Enter Title', 'source-affix' ) . '" />';
				echo '<input type="text" name="link_url[]" value="' . esc_url( $link['url'] ) . '"  class="regular-text code" placeholder="' . __( 'Enter Full URL', 'source-affix' ) . '" />';
				echo '<span class="btn-remove-source-link"><i class="dashicons dashicons-no-alt"></i></span>';
				echo '</li>';
			}
		} else {
			// Show empty first field.
			echo '<li>';
			echo '<span class="btn-move-source-link"><i class="dashicons dashicons-sort"></i></span>';
			echo '<input type="text" name="link_title[]" value=""  class="regular-text1 code" placeholder="' . __( 'Enter Title', 'source-affix' ) . '" />';
			echo '<input type="text" name="link_url[]" value=""  class="regular-text code" placeholder="' . __( 'Enter Full URL', 'source-affix' ) . '" />';
			echo '<span class="btn-remove-source-link"><i class="dashicons dashicons-no-alt"></i></span>';
			echo '</li>';
		}

		echo '</ul>';
		echo '<a href="#" class="button button-primary" id="btn-add-source-link">' . __( 'Add New', 'source-affix' ) . '</a>';

		return;
	}

	/**
	 * Save meta box value.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	function source_affix_save_sa_source( $post_id, $post ) {
		// Verify nonce.
		if ( ! ( isset( $_POST['sa_source_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['sa_source_nonce'] ), plugin_basename( __FILE__ ) ) ) ) {
			return;
		}

		// Bail if auto save or revision.
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
			return;
		}

		if ( isset( $_POST['sa_source_nonce'] ) && isset( $_POST['post_type'] ) ) {

			$links_array = array();

			if ( isset( $_POST['link_title'] ) && ! empty( $_POST['link_title'] ) ) {
				$cnt = 0;
				foreach ( $_POST['link_title'] as $key => $lnk ) {
					$links_array[ $cnt ]['title'] = sanitize_text_field( $lnk );
					$links_array[ $cnt ]['url']   = esc_url_raw( $_POST['link_url'][ $key ] );
					$cnt++;
				}
			}

			$sa_source_message = source_affix_convert_array_to_meta( $links_array );

			// If the value for the source message exists, delete it first.
			if ( 0 == count( get_post_meta( $post_id, 'sa_source' ) ) ) {
				delete_post_meta( $post_id, 'sa_source' );
			}

			// Update it for this post.
			update_post_meta( $post_id, 'sa_source', $sa_source_message );
		}
	}

	/**
	 * Check required.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 */
	function source_affix_check_required( $post_id, $post ) {
		// Verify nonce.
		if ( ! ( isset( $_POST['sa_source_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['sa_source_nonce'] ), plugin_basename( __FILE__ ) ) ) ) {
			return;
		}

		// Bail if auto save or revision.
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || absint( $_POST['post_ID'] ) !== $post_id ) {
			return;
		}

		// Field option.
		$post_types = array();

		if ( isset( $this->options['sa_source_posttypes'] ) ) {
			$post_types = array_keys( $this->options['sa_source_posttypes'] );
		}

		// Bail if not selected post type.
		if ( ! in_array( get_post_type( $post_id ), $post_types ) ) {
			return;
		}

		$meta = get_post_meta( $post_id, 'sa_source', true );

		// Bail if there is meta.
		if ( $meta ) {
			return;
		}

		set_transient( 'sa_required_check', 'no' );

		// Change status to draft.
		global $wpdb;

		// Update post.
		$wpdb->update( $wpdb->posts, array( 'post_status' => 'draft' ), array( 'ID' => $post->ID ) );

		// Clean post cache.
		clean_post_cache( $post->ID );

		// Manage post transition
		$old_status = $post->post_status;

		$post->post_status = 'draft';

		wp_transition_post_status( 'draft', $old_status, $post );
	}

	/**
	 * Show admin notices.
	 *
	 * @since 1.0.0
	 */
	function show_admin_notices() {
		// Check if the transient is set, and display the error message.
		if ( 'no' == get_transient( 'sa_required_check' ) ) {
			echo '<div id="message" class="error"><p><strong>';
			echo esc_html__( 'Source is required.', 'source-affix' );
			echo '</strong></p></div>';
			delete_transient( 'sa_required_check' );
		}
	}

	/**
	 * Register plugin settings
	 */
	public function source_affix_plugin_register_settings() {
		register_setting( 'sa-plugin-options-group', 'sa_plugin_options', array( $this, 'source_affix_plugin_options_validate' ) );

		add_settings_section( 'main_settings', __( 'Source Affix Settings', 'source-affix' ), array( $this, 'source_affix_plugin_section_text_callback' ), 'source-affix-main' );

		add_settings_field( 'sa_source_posttypes', __( 'Enable Source Affix for', 'source-affix' ), array( $this, 'sa_source_posttypes_callback' ), 'source-affix-main', 'main_settings' );
		add_settings_field( 'sa_source_title', __( 'Source Title', 'source-affix' ), array( $this, 'sa_source_title_callback' ), 'source-affix-main', 'main_settings' );
		add_settings_field( 'sa_source_style', __( 'Source Style', 'source-affix' ), array( $this, 'sa_source_style_callback' ), 'source-affix-main', 'main_settings' );
		add_settings_field( 'sa_source_open_style', __( 'Open Source Link', 'source-affix' ), array( $this, 'sa_source_open_style_callback' ), 'source-affix-main', 'main_settings' );
		add_settings_field( 'sa_source_position', __( 'Source Position', 'source-affix' ), array( $this, 'sa_source_position_callback' ), 'source-affix-main', 'main_settings' );
		add_settings_field( 'sa_load_plugin_styles', __( 'Load Plugin Styles', 'source-affix' ), array( $this, 'sa_load_plugin_styles_callback' ), 'source-affix-main', 'main_settings' );
		add_settings_field( 'sa_make_required', __( 'Make Source Required', 'source-affix' ), array( $this, 'sa_make_required_callback' ), 'source-affix-main', 'main_settings' );
	}

	// Validate options.
	function source_affix_plugin_options_validate( $input ) {
		if ( ! isset( $input['sa_source_posttypes'] ) ) {
			$input['sa_source_posttypes'] = array();
		}

		$input['sa_source_title']       = sanitize_text_field( $input['sa_source_title'] );
		$input['sa_source_style']       = sanitize_text_field( $input['sa_source_style'] );
		$input['sa_source_open_style']  = sanitize_text_field( $input['sa_source_open_style'] );
		$input['sa_source_position']    = sanitize_text_field( $input['sa_source_position'] );
		$input['sa_load_plugin_styles'] = sanitize_text_field( $input['sa_load_plugin_styles'] );
		$input['sa_make_required']      = sanitize_text_field( $input['sa_make_required'] );

		return $input;
	}

	function source_affix_plugin_section_text_callback() {
		return;
	}

	function sa_source_posttypes_callback() {
		?>
		<p>
			<input type="checkbox" name="sa_plugin_options[sa_source_posttypes][post]" value="1"
			<?php checked( isset( $this->options['sa_source_posttypes']['post'] ) && 1 == $this->options['sa_source_posttypes']['post'] ); ?> />&nbsp;<?php _e( 'Post', 'source-affix' ); ?>
		</p>
		<p>
			<input type="checkbox" name="sa_plugin_options[sa_source_posttypes][page]" value="1"
			<?php checked( isset( $this->options['sa_source_posttypes']['page'] ) && 1 == $this->options['sa_source_posttypes']['page'] ); ?> />&nbsp;<?php _e( 'Page', 'source-affix' ); ?>
		</p>
		<?php
		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		$post_types_custom = get_post_types( $args );

		if ( ! empty( $post_types_custom ) ) {
			foreach ( $post_types_custom as $ptype ) {
				?>
				<p>
					<input type="checkbox" name="sa_plugin_options[sa_source_posttypes][<?php echo $ptype; ?>]" value="1"
					<?php checked( isset( $this->options['sa_source_posttypes'][ $ptype ] ) && 1 == $this->options['sa_source_posttypes'][ $ptype ] ); ?> />&nbsp;<?php echo ucfirst( $ptype ); ?>
				</p>
				<?php
			}
		}
	}

	function sa_source_title_callback() {
		?>
		<input type="text" id="sa_source_title" name="sa_plugin_options[sa_source_title]" value="<?php echo $this->options['sa_source_title']; ?>" />
		<?php
	}

	function sa_source_style_callback() {
		?>
		<select id="sa_source_style" name="sa_plugin_options[sa_source_style]">
			<option value="COMMA" <?php selected( $this->options['sa_source_style'], 'COMMA' ); ?>><?php _e( 'Comma Separated', 'source-affix' ); ?></option>
			<option value="LIST" <?php selected( $this->options['sa_source_style'], 'LIST' ); ?>><?php _e( 'List', 'source-affix' ); ?></option>
			<option value="ORDEREDLIST" <?php selected( $this->options['sa_source_style'], 'ORDEREDLIST' ); ?>><?php _e( 'Ordered List', 'source-affix' ); ?></option>
		</select>
		<?php
	}

	function sa_source_open_style_callback() {
		?>
		<select id="sa_source_open_style" name="sa_plugin_options[sa_source_open_style]">
		<option value="SELF" <?php selected( $this->options['sa_source_open_style'], 'SELF' ); ?>><?php _e( 'Same Window', 'source-affix' ); ?></option>
			<option value="BLANK" <?php selected( $this->options['sa_source_open_style'], 'BLANK' ); ?>><?php _e( 'New Window', 'source-affix' ); ?></option>
		</select>
		<?php
	}

	function sa_source_position_callback() {
		?>
		<select id="sa_source_position" name="sa_plugin_options[sa_source_position]">
			<option value="APPEND" <?php selected( $this->options['sa_source_position'], 'APPEND' ); ?>>
				<?php _e( 'End of the content', 'source-affix' ); ?></option>
			<option value="PREPEND" <?php selected( $this->options['sa_source_position'], 'PREPEND' ); ?>>
				<?php _e( 'Beginning of the content', 'source-affix' ); ?></option>
			<option value="NO" <?php selected( $this->options['sa_source_position'], 'NO' ); ?>>
				   <?php _e( 'Do Not Append', 'source-affix' ); ?></option>
		</select>
		<?php
	}

	function sa_load_plugin_styles_callback() {
		?>
		<select id="sa_load_plugin_styles" name="sa_plugin_options[sa_load_plugin_styles]">
			<option value="YES" <?php selected( $this->options['sa_load_plugin_styles'], 'YES' ); ?>>
				<?php _e( 'Yes', 'source-affix' ); ?></option>
			<option value="NO" <?php selected( $this->options['sa_load_plugin_styles'], 'NO' ); ?>>
				<?php _e( 'No', 'source-affix' ); ?></option>
		</select>
		<?php
	}

	function sa_make_required_callback() {
		?>
		<select id="sa_make_required" name="sa_plugin_options[sa_make_required]">
			<option value="YES" <?php selected( $this->options['sa_make_required'], 'YES' ); ?>>
				<?php _e( 'Yes', 'source-affix' ); ?></option>
			<option value="NO" <?php selected( $this->options['sa_make_required'], 'NO' ); ?>>
				<?php _e( 'No', 'source-affix' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Get post types options.
	 *
	 * @since 2.0.0
	 *
	 * @return array Options.
	 */
	public function get_post_types_options() {
		$output = array(
			'post' => esc_html__( 'Post', 'ns-featured-posts' ),
			'page' => esc_html__( 'Page', 'ns-featured-posts' ),
		);

		$args = array(
			'public'   => true,
			'_builtin' => false,
		);

		$custom_types = get_post_types( $args, 'objects' );

		if ( ! empty( $custom_types ) ) {
			foreach ( $custom_types as $item ) {
				$output[ $item->name ] = $item->labels->{'singular_name'};
			}
		}

		return $output;
	}

	/**
	 * Render sidebar.
	 *
	 * @since 2.0.0
	 */
	public function render_sidebar() {
		?>
		<div class="sidebox">
			<h3 class="box-heading">Help &amp; Support</h3>
			<div class="box-content">
				<ul>
					<li><strong>Questions, bugs or great ideas?</strong></li>
					<li><a href="https://wordpress.org/support/plugin/source-affix/" target="_blank">Visit plugin support page</a></li>
					<li><strong>Wanna help make this plugin better?</strong></li>
					<li><a href="https://wordpress.org/support/plugin/source-affix/reviews/" target="_blank">Review and rate this plugin on WordPress.org</a></li>
				</ul>
			</div>
		</div><!-- .sidebox -->
		<div class="sidebox">
			<h3 class="box-heading">My Blog</h3>
			<div class="box-content">
				<?php $rss_items = $this->get_feed_items(); ?>

				<?php if ( ! empty( $rss_items ) ) : ?>
					<ul>
						<?php foreach ( $rss_items as $item ) : ?>
							<li><a href="<?php echo esc_url( $item['url'] ); ?>" target="_blank"><?php echo esc_html( $item['title'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div><!-- .sidebox -->
		<?php
	}

	/**
	 * Get feed items.
	 *
	 * @since 2.0.0
	 *
	 * @return array Feed items array.
	 */
	private function get_feed_items() {
		$output = array();

		$rss = fetch_feed( 'https://www.nilambar.net/category/wordpress/feed' );

		$maxitems = 0;

		$rss_items = array();

		if ( ! is_wp_error( $rss ) ) {
			$maxitems  = $rss->get_item_quantity( 5 );
			$rss_items = $rss->get_items( 0, $maxitems );
		}

		if ( ! empty( $rss_items ) ) {
			foreach ( $rss_items as $item ) {
				$feed_item = array();

				$feed_item['title'] = $item->get_title();
				$feed_item['url']   = $item->get_permalink();

				$output[] = $feed_item;
			}
		}

		return $output;
	}

	/**
	 * Render attributes.
	 *
	 * @since 2.0.0
	 *
	 * @param array $attributes Attributes.
	 * @param bool  $echo Whether to echo or not.
	 */
	public function render_attr( $attributes, $echo = true ) {
		if ( empty( $attributes ) ) {
			return;
		}

		$html = '';

		foreach ( $attributes as $name => $value ) {

			$esc_value = '';

			if ( 'class' === $name && is_array( $value ) ) {
				$value = join( ' ', array_unique( $value ) );
			}

			if ( false !== $value && 'href' === $name ) {
				$esc_value = esc_url( $value );

			} elseif ( false !== $value ) {
				$esc_value = esc_attr( $value );
			}

			$html .= false !== $value ? sprintf( ' %s="%s"', esc_html( $name ), $esc_value ) : esc_html( " {$name}" );
		}

		if ( ! empty( $html ) && true === $echo ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			return $html;
		}
	}
}
