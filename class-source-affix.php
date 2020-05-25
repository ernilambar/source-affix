<?php
/**
 * Source Affix
 *
 * @package Source_Affix
 */

/**
 * Source Affix Plugin class.
 */
class Source_Affix
{
    /**
     * Plugin version, used for cache-busting of style and script file references.
     *
     * @since   1.0.0
     *
     * @var     string
     */

    const VERSION = SOURCE_AFFIX_VERSION;

    /**
     * Unique identifier for your plugin.
     *
     * The variable name is used as the text domain when internationalizing strings
     * of text. Its value should match the Text Domain file header in the main
     * plugin file.
     *
     * @since    1.0.0
     *
     * @var      string
     */
    protected $plugin_slug = 'source-affix';

    /**
     * Plugin default options
     *
     * Default options for the plugin.
     *
     * @since    1.0.0
     *
     * @var      array
     */
    protected static $default_options = null ;

	protected $options = array();

    /**
     * Instance of this class.
     *
     * @since    1.0.0
     *
     * @var      object
     */
    protected static $instance = null;

    /**
     * Initialize the plugin by setting localization and loading public scripts
     * and styles.
     *
     * @since     1.0.0
     */
    private function __construct()
    {

        // Load plugin text domain
        add_action('init', array($this, 'load_plugin_textdomain'));

        // Activate plugin when new blog is added
        add_action('wpmu_new_blog', array($this, 'activate_new_site'));

        // Load public-facing style sheet and JavaScript.
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

        self :: $default_options = array(
			'sa_source_posttypes'  => array('post' => 1),
			'sa_source_title'      => 'Source :',
			'sa_source_style'      => 'COMMA',
			'sa_source_open_style' => 'BLANK',
			'sa_source_position'   => 'APPEND',
			'sa_plugin_styles'     => 'YES',
        );

		$this -> _setDefaultOptions();

		//get current options
        $this->_getCurrentOptions();

        /**
         * Define custom functionality.
         */
        add_filter('the_content', array($this, 'source_affix_affix_sa_source'));
    }

    /**
     * Return the plugin slug.
     *
     * @since    1.0.0
     *
     * @return    Plugin slug variable.
     */
    public function get_plugin_slug()
    {
        return $this->plugin_slug;
    }

    /**
     * Return an instance of this class.
     *
     * @since     1.0.0
     *
     * @return    object    A single instance of this class.
     */
    public static function get_instance()
    {

        // If the single instance hasn't been set, set it now.
        if (null == self::$instance)
        {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Fired when the plugin is activated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Activate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       activated on an individual blog.
     */
    public static function activate($network_wide)
    {

        if (function_exists('is_multisite') && is_multisite())
        {

            if ($network_wide)
            {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id)
                {

                    switch_to_blog($blog_id);
                    self::single_activate();
                }

                restore_current_blog();
            }
            else
            {
                self::single_activate();
            }
        }
        else
        {
            self::single_activate();
        }
    }

    /**
     * Fired when the plugin is deactivated.
     *
     * @since    1.0.0
     *
     * @param    boolean    $network_wide    True if WPMU superadmin uses
     *                                       "Network Deactivate" action, false if
     *                                       WPMU is disabled or plugin is
     *                                       deactivated on an individual blog.
     */
    public static function deactivate($network_wide)
    {

        if (function_exists('is_multisite') && is_multisite())
        {

            if ($network_wide)
            {

                // Get all blog ids
                $blog_ids = self::get_blog_ids();

                foreach ($blog_ids as $blog_id)
                {

                    switch_to_blog($blog_id);
                    self::single_deactivate();
                }

                restore_current_blog();
            }
            else
            {
                self::single_deactivate();
            }
        }
        else
        {
            self::single_deactivate();
        }
    }

    /**
     * Fired when a new site is activated with a WPMU environment.
     *
     * @since    1.0.0
     *
     * @param    int    $blog_id    ID of the new blog.
     */
    public function activate_new_site($blog_id)
    {

        if (1 !== did_action('wpmu_new_blog'))
        {
            return;
        }

        switch_to_blog($blog_id);
        self::single_activate();
        restore_current_blog();
    }

    /**
     * Get all blog ids of blogs in the current network that are:
     * - not archived
     * - not spam
     * - not deleted
     *
     * @since    1.0.0
     *
     * @return   array|false    The blog ids, false if no matches.
     */
    private static function get_blog_ids()
    {

        global $wpdb;

        // get an array of blog ids
        $sql = "SELECT blog_id FROM $wpdb->blogs
            WHERE archived = '0' AND spam = '0'
            AND deleted = '0'";

        return $wpdb->get_col($sql);
    }

    /**
     * Fired for each blog when the plugin is activated.
     *
     * @since    1.0.0
     */
    private static function single_activate()
    {
        // Define activation functionality here
        $option_name = 'sa_plugin_options';
        update_option($option_name, self :: $default_options);
    }

    /**
     * Fired for each blog when the plugin is deactivated.
     *
     * @since    1.0.0
     */
    private static function single_deactivate()
    {
        // Define deactivation functionality here
        $option_name = 'sa_plugin_options';
        // delete_option($option_name);
    }

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain()
    {

        $domain = $this->plugin_slug;
        $locale = apply_filters('plugin_locale', get_locale(), $domain);

        load_textdomain($domain, trailingslashit(WP_LANG_DIR) . $domain . '/' . $domain . '-' . $locale . '.mo');
        load_plugin_textdomain($domain, FALSE, basename(dirname(__FILE__)) . '/languages');
    }

    /**
     * Register and enqueue public-facing style sheet.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
    	$options = $this->options ;

    	if ( 'NO' !== $options['sa_load_plugin_styles'] ) {
	        wp_enqueue_style($this->plugin_slug . '-plugin-styles', plugins_url('css/public.css', __FILE__), array(), self::VERSION);
    	}
    }

    /**
     * Affix source to the content.
     *
     * @params  $content    The content.
     * @returns             The content with affixed source.
     */
    function source_affix_affix_sa_source($content)
    {
        $options = $this->options;
        if ($options)
        {
            extract($options);
        }

        $available_post_types_array = array_keys($sa_source_posttypes);
        $current_post_type = get_post_type(get_the_ID());

        if (!in_array($current_post_type, $available_post_types_array))
        {
            return $content;
        }
        $sa_source = get_post_meta(get_the_ID(), 'sa_source', true) ;
        if ('' != $sa_source )
        {
            $links_array = source_affix_convert_meta_to_array( $sa_source );

            $single_link = array();
            if (!empty($links_array) && is_array($links_array)){
                foreach ($links_array  as $key => $eachline) {
                    if (! empty($eachline['url'] ) ) {
                        $lnk = '<a href="' . $eachline['url'] . '" ';
                        $lnk .= ($sa_source_open_style == 'BLANK') ? ' target="_blank" ' : '';
                        $lnk .= ' >' . esc_attr($eachline['title']) . '</a>';
                    }
                    else{
                        $lnk = esc_attr($eachline['title']);
                    }
                    $single_link[] = $lnk;
                }
            }

            $source_message = '<div class="sa-source-wrapper">';

            if ( $sa_source_title ) {
	            $source_message .= '<strong>' . $sa_source_title . '</strong>';
            }

            switch ($sa_source_style) {
                case 'COMMA':
                    $source_message .= '<div class="news-source">' . implode(', ', $single_link) . '</div>';
                    break;
                case 'LIST':
                    if (!empty($single_link))
                    {
                        $source_message.= '<ul class="list-source-links">';
                        $source_message .= '<li>'.implode('</li><li>', $single_link).'</li>';
                        $source_message.= '</ul>';
                    }
                    break;

                case 'ORDEREDLIST':
                    if (!empty($single_link))
                    {
                        $source_message.= '<ol class="list-source-links">';
                        $source_message .= '<li>'.implode('</li><li>', $single_link).'</li>';
                        $source_message.= '</ol>';
                    }
                    break;

                default:
                    break;
            }

            $source_message .= '</div>';

            if ( is_singular() && 'NO' !== $options['sa_source_position'] )
            {
                if ( 'APPEND' == $sa_source_position )
                {
                    $content = $content . $source_message;
                }
                else
                {
                    $content = $source_message . $content;
                }
            } // end if
        } // end if

        return $content;
    }
	private function _getCurrentOptions()
    {
		$sa_options = array_merge( self :: $default_options , (array) get_option( 'sa_plugin_options', array() ) );
        $this->options = $sa_options;
    }
	//get default options and saves in options table
    private function _setDefaultOptions()
    {
        if( !get_option( 'sa_plugin_options' ) ) {
            update_option('sa_plugin_options', self :: $default_options);
        }
    }
	private function _removePluginOptions()
    {
        delete_option('sa_plugin_options');
    }

	public function source_affix_get_options_array(){
		return $this->options;
	}

}
