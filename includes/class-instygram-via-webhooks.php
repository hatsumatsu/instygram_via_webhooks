<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class instygram_via_webhooks {

	/**
	 * The single instance of instygram_via_webhooks.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;
	
	/**
	 * Use custom post type?.
	 * @var     boolean
	 * @access  public
	 * @since   1.0.0
	 */
	public $use_custom_post_type;

	/**
	 * IFTTT Maker Key.
	 * @var     string
	 * @access  private
	 * @since   1.0.0
	 */
	private $ifttt_maker_key;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {

        // include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        // if( !is_plugin_active('rest-api/plugin.php') ) {
        //     echo('Requires <a href='https://wordpress.org/plugins/rest-api/'>WordPress REST API (Version 2)</a>. (This will be built into Wordpress in an upcoming release.)');
        // }

		$this->_version = $version;
		$this->_token = 'instygram_via_webhooks';

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new instygram_via_webhooks_Admin_API();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		
        $this->use_custom_post_type = get_option('instygram_webhooks_use_custom_type');
        $this->ifttt_maker_key = get_option('instygram_webhooks_ifttt_maker_key');
        $this->new_post_status = get_option('instygram_webhooks_new_post_status');
        $this->author_id = get_option('instygram_webhooks_new_post_status') ?: 1;

        if($this->use_custom_post_type) {
            $this->register_taxonomy('instagram-tags', 'Instagram Tags', 'Instagram Tag', ['instygram']);
    		$this->register_post_type('instagram', 'Instagrams', 'Instagram', 'Instagram posts', [
    			'supports' => array( 'title', 'editor', 'thumbnail', 'instagram-tags' ),
    			'hierarchical' => false,
    		]);
        }
        
        $this->register_webhook_listener();

	} // End __construct ()


    public function register_webhook_listener() {
        // http://mysite.com/wp-json/instygram_via_webhooks/v1/post
        add_action( 'rest_api_init', function () {
            // receive POSTS

            register_rest_route( 'instagram_via_webhooks/v1', '/post', array(
                'methods' => 'POST',
                'callback' => function(WP_REST_Request $request) {
                    require ( ABSPATH . 'wp-admin/includes/image.php' );
                    
                    $post_id = $this->insert_instygram_post($request);
                    if(!$post_id) {
                        return [ 'success' => false ];
                    }
                    
                    $attach_id = $this->insert_instygram_image($request, $post_id);
                    if(!$attach_id) {
                        return [ 'success' => false ];
                    }

                    return [ 'success' => true ];
                }
            ) );
            register_rest_route( 'instagram_via_webhooks/v1', '/post/(?P<id>\d+)', array(
                'methods' => 'GET',
                'callback' => function($data) {
                    var_dump($_GET);
                }
            ) );
            register_rest_route( 'instagram_via_webhooks/v1', '/post/', array(
                'methods' => 'GET',
                'callback' => function($data) {
                    var_dump($_GET);
                }
            ) );
        } );
    }
    
    
    private function insert_instygram_post( WP_REST_REQUEST $request) {
        return wp_insert_post([
            'post_author'   => $this->author_id,
            'post_title'    => 'instygram: ' . $request->get_param('created_at'),
            'post_content'  => $request->get_param('caption'),
            'post_status'   => $this->new_post_status,
            'post_type'     => ($this->use_custom_post_type) ? 'instagram' : 'post',
            'meta_input'    => [
                'url'        => $request->get_param('url'),
                'source_url' => $request->get_param('source_url'),
                'embed_code' => $request->get_param('embed_code')
            ]
        ]);
    }
    
    
    private function insert_instygram_image( WP_REST_Request $request, $post_id ) {
        $image = file_get_contents( $request->get_param( 'source_url' ) );
        $filename = 'instygram_' . $post_id . '.jpg';
                
        $upload = wp_upload_bits( $filename, null, $image );

        $attach_id = wp_insert_attachment( 
            [
               'post_author'    => $this->author_id,
               'post_mime_type' => 'image/jpeg',
               'post_title'     => $filename,
               'post_content'   => $request->get_param('caption'),
               'post_status'    => 'inherit'
            ], 
            $upload['file'], 
            $post_id
        );

        require_once( ABSPATH . 'wp-admin/includes/image.php' );        
        wp_update_attachment_metadata( 
            $attach_id, 
            wp_generate_attachment_metadata( $attach_id, $upload['file'] )
        );
        set_post_thumbnail( $post_id, $attach_id );
        return $attach_id;
    }


	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new instygram_via_webhooks_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new instygram_via_webhooks_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'instygram-via-webhooks', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'instygram-via-webhooks';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main instygram_via_webhooks Instance
	 *
	 * Ensures only one instance of instygram_via_webhooks is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see instygram_via_webhooks()
	 * @return Main instygram_via_webhooks instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
