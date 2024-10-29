<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class autoshortcoder {

	/**
	 * The single instance of autoshortcoder.
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


   private $done;
	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		$this->_version = $version;
		$this->_token = 'autoshortcoder';
 
      $this->done = false;

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new autoshortcoder_Admin_API();
		}

		//add_action( 'init', array( $this, 'load_localisation' ), 0 );
      add_filter( 'the_content', array( $this, 'add_shortcodes'));
	} // End __construct ()


   public function get_tagarray () {
      $options = get_option('autoshortcode_autoshortcode_settings');
      $options = explode("\n", str_replace("\r", "", $options));
      $opt=array();
      foreach($options as $optline) {
         $optarray = explode("|", $optline);
         $tag = trim($optarray[0]);
         if(count($optarray) > 2) {
            $title=$optarray[1];
            $shortcode=$optarray[2];
         } else {
            $title=false;
            $shortcode=$optarray[1];
         }

         if(!in_array($tag, $opt))
            $opt[$tag] = array();
         $opt[$tag][] = array('title' => $title, 'shortcode' => $shortcode);
             
      }
      //print_r($opt);
      return $opt;
   }


   public function add_shortcodes ( $content ) {
      if(!$this->done) {
         $tags = wp_get_post_tags($GLOBALS['post']->ID, array('fields' => 'names'));
         $opts = $this->get_tagarray();
         foreach($opts as $tagname => $tagcontent)
         {
            if (in_array($tagname, $tags)) {
               foreach($tagcontent as $addcode) {
                  $content.="<hr>";
                  if($addcode['title']) {
                     $content.='<h4>'.$addcode['title'].'</h4>';
                  }
                  $content.=do_shortcode($addcode['shortcode']); 
                  $content.="<hr>";
               }
            }  
         }
      } 
      $this->done=true;
      return $content;
   }

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Main autoshortcoder Instance
	 *
	 * Ensures only one instance of autoshortcoder is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see autoshortcoder()
	 * @return Main autoshortcoder instance
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
