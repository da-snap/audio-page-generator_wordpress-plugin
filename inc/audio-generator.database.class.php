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
        $this->options = get_option('audio-generator_name');
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
		$this->_log_version_number();

        $sql = "`Index_id` int(10) NOT NULL AUTO_INCREMENT,
            `Filename` varchar(50) NOT NULL,
            `Hash` varchar(50) NOT NULL,
            `Date` varchar(20) NOT NULL,
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

	function reindex () {

		$this->getID3 = new getID3;
		$indexed = $this->get_indexer_rows();
		$files = [];
        $paths = glob($this->options['upload_dir'] . "/*.mp3" );
		foreach( $paths as $path ) {
				$file_parts = explode( '/', $path );
				$files[] = end($file_parts);
		}
		foreach ( $indexed as $row ) {
			$file_path = $this->options['upload_dir'] . "/" . $row[Filename];
			if ( in_array( $row[Filename], $files ) ) {
				$hash = hash_file('md5', $file_path);
				if( $row[hash] !=  $hash ){
					$tags = $this->getID3->analyze( $file_path );
					$tags = $tags['tags']['id3v2'];
					$this->update_indexer_row($row[Index_id], $row[Filename], $hash, $tags);
				}
				$files = array_diff( $files, [$row[Filename]]);
			} else {
				$this->delete_indexer_row($row[Index_id]);
			}
		}
		if ( count( $files ) > 0) {
			foreach ( $files as $file ) {
				$file_path = $this->options['upload_dir'] . "/" . $file;
				$hash = hash_file('md5', $file_path);
				$tags = $this->getID3->analyze($file_path);
				$tags = $tags['tags']['id3v2'];
				$this->add_indexer_row( $file, $hash, $tags );
			}
		}
	}

	function update_indexer_row ( $indexer_id, $filename, $new_hash, $tags) {
		# FIXME: get Date...
		$date = '2016-02-03 12:01:00';
		global $wpdb;
		$wpdb->show_errors();
		$res = $wpdb->update( $this->pref . 'indexer',
								array(
									'Filename' => $filename,
									'Hash' => $new_hash,
									'Date' => $date,
									'Title' => $tags['title'][0],
									'Album' => $tags['album'][0],
									'Author' => $tags['author'][0],
									'AlbumAuthor' => $tags['albumauthor'][0],
									'Track' => $tags['track'][0],
									'Year' => $tags['year'][0],
									'Length' => $tags['length'][0],
									'Lyrict' => $tags['lyrict'][0],
									'Desc' => $tags['desc'][0],
									'Genre' => $tags['genre'][0],
									'Encoded' => $tags['encoded'][0],
									'Copyright' => $tags['copyright'][0],
									'Publisher' => $tags['publisher'][0],
									'OriginalArtist' => $tags['originalartist'][0],
									'URL' => $tags['url'][0],
									'Comments' => $tags['comment'][0],
									'Composer' => $tags['composer'][0]
								),
								array( 'Index_id' => $indexer_id ),
								array(
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s'
								),
								array( '%d' )
							);
			error_log( "Update Result: $res" );
			error_log( "Last Error UPDATE $filename: " . $wpdb->last_query );
			$wpdb->flush();
	}

	function add_indexer_row ( $filename, $new_hash, $tags) {
		# FIXME: get Date...
		$date = '2016-02-03 12:01:00';
		error_log("try to insert $filename with $new_hash and $date.");
		global $wpdb;
		$wpdb->show_errors();
		$res = $wpdb->insert( $this->pref . 'indexer',
								array(
									'Filename' => $filename,
									'Hash' => $new_hash,
									'Date' => $date,
									'Title' => $tags['title'][0],
									'Album' => $tags['album'][0],
									'Author' => $tags['author'][0],
									'AlbumAuthor' => $tags['albumauthor'][0],
									'Track' => $tags['track'][0],
									'Year' => $tags['year'][0],
									'Length' => $tags['length'][0],
									'Lyrict' => $tags['lyrict'][0],
									'Desc' => $tags['desc'][0],
									'Genre' => $tags['genre'][0],
									'Encoded' => $tags['encoded'][0],
									'Copyright' => $tags['copyright'][0],
									'Publisher' => $tags['publisher'][0],
									'OriginalArtist' => $tags['originalartist'][0],
									'URL' => $tags['url'][0],
									'Comments' => $tags['comment'][0],
									'Composer' => $tags['composer'][0]
								),
								array(
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s',
									'%s'
								)
							);
			error_log( "Last Error inser $filename: " . $wpdb->last_query );
			$wpdb->flush();
	}
	function delete_indexer_row ( $indexer_id ) {

		global $wpdb;
		$wpdb->delete( $this->pref . 'indexer', array( 'Index_id' => $indexer_id ) );

	}

	function get_indexer_rows () {

		global $wpdb;
		$res = $wpdb->get_results(
			"SELECT * FROM {$this->pref}indexer",
			ARRAY_A
		);
		return $res;
	}

}
