<?php

/**
* 
*/
class Nglogo
{
	// this variable will store almost all needed arguments for our logo
	public $logo = array(
		"olduri" 	=> null,
		"newuri"	=> null,
		"class" 	=> ".logo",
		"id" 		=> "#logo",
		"style" 	=> array(
			"width" 	=> 30,
			"height" 	=> 30,
			"animation" => array(
				"name" 	=> "flipInX",
				"class" => "flipInX animated"
			)
		)
	);

	public $settings = array(
		"keep" => array(
			"html" 		=> true,
			"xpath" 	=> true,
			"oldlogo" 	=> true
		),
		"font" => array(
			"family" 	=> "Arial black",
			"size" 		=> 15,
			"sizeunit" 	=> "px"
		),
		"animation" => array(
			"repeat" 	=> false,
			"delay" 	=> 5000,
		)
	);

	protected $types = array('image', 'text', 'svg', 'canvas');

	private static $instance = false;

	private static $initiated = false;

	public function __construct(){
		// silence is good
	}

	/**
	 * Initiate the plugin's hooks and actions, if it is not initiated already
	 */
	public static function _init() {
		if (!static::$initiated) {
			static::_init_hooks();
		}
	}

	/**
	 * Install function
	 */
	public static function install() {
		global $wpdb;

		$tablename = $wpdb->prefix . "ng_logoanimation";
		$ngcharset = $wpdb->get_charset_collate();

		if ($wpdb->get_var("SHOW TABLES LIKE '$tablename'") != $tablename) {
			// create the table
			$sqlng = "CREATE TABLE $tablename (
				id INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
				logo TEXT DEFAULT NULL,
				settings TEXT DEFAULT NULL,
				theme VARCHAR(255) NOT NULL,
				custom TEXT DEFAULT NULL,
				active BOOLEAN DEFAULT 1,
				created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				updated TIMESTAMP NULL,
				PRIMARY KEY(id)
			) $ngcharset;";
			// include the wp_settings
			static::_require(ABSPATH . 'wp-admin/includes/upgrade.php');
			// execute the create table query
			dbDelta($sqlng);
		}
	}

	/**
	 * Add filters, action and hooks to the init phase
	 */
	public static function _init_hooks() {
		// Init actions
		add_action( 'admin_menu', array(__CLASS__, '_add_menu'), 10 );
		add_action( 'admin_menu', array(__CLASS__, '_add_submenu_page'), 10 );
		// load admin scripts
		add_action( 'admin_enqueue_scripts', array(__CLASS__, '_load_stylesheets') );
	}

	/**
	 * return an instance of this class
	 */
	public static function instance(){
		if (is_bool(static::$instance))
			static::$instance = new static();

		return static::$instance;
	}

	/**
	 * forge the local settings with the user settings and return a new array of values
	 */
	public function froge($settings, $logo) {
		if (!is_bool($settings)) {
			$this->settings = array_merge($this->settings, $settings);
		}
		if (!is_bool($logo)) {
			$this->logo = array_merge($this->logo, $logo);
		}

		return $this;
	}

	/**
	 * add a menu page to the admin_menu
	 */
	public static function _add_menu() {
		// create the page menu
		add_menu_page(
			__( 'NG Animate Logo', 'ngtrans' ),
			__('Logo animate', 'ngtrans'),
			'manage_options',
			'ng_admin_menu_page',
			array(__CLASS__, '_render_admin_page_content'),
			"dashicons-art",
			4
		);
	}

	/**
	 * Admin page content
	 */
	public static function _render_admin_page_content () {

	}

	/**
	 * add a submenu page
	 */
	public static function _add_submenu_page() {
		// create the submenu elements
		add_submenu_page( 'ng_admin_menu_page', __('NG Logo animation settings', 'ngtrans'), __( 'Settings', 'ngtrans' ), 'manage_options', 'ng_settings_page', array(__CLASS__, '_ng_create_options_content') );
	}

	/**
	 * Load all stylesheets
	 */
	public static function _load_stylesheets() {
		wp_register_style( 'animate.css', NGPATH . 'css/animate.css', array(), NGVERSION );
		wp_enqueue_style( 'animate.css' );
	}
	/**
	 * create the options page content
	 */
	public static function _ng_create_options_content() {
		echo static::_render('settings.php', array( 'title' => esc_html__('NG Animation', 'ngtrans') ));
	}
	/**
	 * include/require a file in the path
	 */
	public static function _require($path = '.', $filename = null, $popvars = true) {
		// set the file fullpath
		$file = $path . NGDS . $filename;
		if (is_null($filename)) {
			$file = $path;
		}

		if (!file_exists($file)) {
			return new WP_Error( 'nofile', __( "The chosen file does not exist, please check your settings", 'ngtrans' ), array('line' => __LINE__, 'function' => '_require', 'file' => 'nglogo.php'));
		}

		// populate variables to the template
		if (!is_bool($popvars)) {
			if (is_array($popvars)) {
				extract( $popvars );
			}
		}

		require_once $file;
	}
	/**
	 * view render function
	 */
	public static function _render($view, $options) {
		// check if the view exists or not
		if (!file_exists(NGPATH . 'views' . NGDS . $view)) {
			return new WP_Error( 'noview', __( 'This view does not exist.', 'ngtrans' ), array('line' => __LINE__, 'function' => '_render', 'file' => 'nglogo.php') );
		}
		// one the fle exists proceed to populate the options
		// check first if the options are a list or jut one variable
		if ( !is_array($options) ) {
			return new WP_Error( 'noarray', __( 'An array of options is needed to populate variables.', 'ngtrans' ), array('line' => __LINE__, 'function' => '_render', 'file' => 'nglogo.php') );
		}
		// now start the buffering output
		ob_start();

		static::_require(NGPATH . 'views', $view, $options);

		return ob_get_clean();
	}
}