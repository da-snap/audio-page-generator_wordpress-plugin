<?php

if ( ! defined( 'ABSPATH' ) ) exit;



class AudioGenerator_Database {

	private static $_instance = null;

	/**
	 * Store DB version
	 * @var string
	 * @access global
	 * @since 1.0.0
	 */
    public $ag_db_version = '1.0';

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function __construct ( $file, $version ) {

		global $wpdb;

		$this->file = $file;
		$this->_version = $version;
		$this->_token = 'audio-generator';
		$this->pref = $wpdb->prefix . 'ag_';
		register_activation_hook( $this->file, array( $this, 'install' ) );

	}

	public static function instance ( $file = '', $version = '1.0.0' ) {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;

	}

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		error_log("install...");
		$this->_log_version_number();

        $sql = "`index_id` int(10) NOT NULL AUTO_INCREMENT,
            `filename` varchar(50) NOT NULL,
            `hash` varchar(50) NOT NULL,
            `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`Title` varchar(50) DEFAULT '',
			`Album` varchar(50) DEFAULT '',
			`Author` varchar(50) DEFAULT '',
			`AlbumAuthor` varchar(50) DEFAULT '',
			`Track` varchar(50) DEFAULT '',
			`Year` varchar(50) DEFAULT '',
			`Length` varchar(50) DEFAULT '',
			`Lyrict` varchar(50) DEFAULT '',
			`Desc` varchar(50) DEFAULT '',
			`Genre` varchar(50) DEFAULT '',
			`Encoded` varchar(50) DEFAULT '',
			`Copyright` varchar(50) DEFAULT '',
			`Publisher` varchar(50) DEFAULT '',
			`OriginalArtist` varchar(50) DEFAULT '',
			`URL` varchar(50) DEFAULT '',
			`Comments` varchar(50) DEFAULT '',
			`Composer` varchar(50) DEFAULT '',
            PRIMARY KEY (`index_id`)";
        $this->_create_table("indexer", $sql);

		add_option( 'ag_db_version', $this->ag_db_version );
	} // End install ()

	function _create_table ($table_name, $sql) {
        global $wpdb;

		$table_name = $this->pref . $table_name;
		$charset_collate = $wpdb->get_charset_collate();

		$exec = "CREATE TABLE $table_name ($sql) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $exec );
	}

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}
