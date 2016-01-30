<?php
/*
* Audio Page Generator. Generates PAge with audiofiles for Wordpress.
*
* Plugin Name: Audio Page Generator
* Description: Takes audiofiles from an Folder an generates a list view from that.
* Version:     0.1
* Author:      Daniel Supplieth
* License:     GPL3
* License URI: http://www.gnu.org/licenses/gpl-3.0.en.html
* Text Domain: audio-page-generator
* Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AudioPageGeneratorPlugin' ) ) :

class AudioPageGeneratorPlugin {

    public $version = '0.2';

    public $generat = null;

    public static function init() {

        $audiopagegenerator = new self();

    }

    public function __construct() {
		$this->load_textdomain();
        $this->define_constants();
        $this->includes();
        $this->setup_shortcode();
        $this->add_actions();
        $this->register_admin_menu();

    }

	private function load_textdomain() {
		$location = dirname( plugin_basename( __FILE__ )) . '/languages';
		load_plugin_textdomain( 'audio-page-generator', false, $location );
	}

    private function define_constants() {
        define ( 'AUDIOPAGEGENERATOR_VERSION', $this->version );
        define ( 'AUDIOPAGEGENERATOR_PATH', plugin_dir_path(__FILE__) );
    }

    private function includes() {

        include AUDIOPAGEGENERATOR_PATH . 'inc/audio-generator.settings.class.php';
        include AUDIOPAGEGENERATOR_PATH . 'inc/audio-generator.generate_page.class.php';
        include AUDIOPAGEGENERATOR_PATH . 'inc/audio-generator.download.class.php';
        include AUDIOPAGEGENERATOR_PATH . 'getid3/getid3.php';

    }

    private function add_actions() {
        $DownloadClass = new Download();
        add_action( 'admin_post_download', array( $DownloadClass, 'download_audio' ) );
        add_action( 'admin_post_nopriv_download', array( $DownloadClass, 'download_audio' ) );
    }
    /**
	* Register the [audioGenerator] shortcode.
	*/
	private function setup_shortcode() {


		$AudioPageGenerator = new AudioGeneratorAudioPage();
		add_shortcode( 'audioGenerator', array( $AudioPageGenerator, 'show_audio_page' ) );

	}

	public function register_admin_menu() {

        if( is_admin() ) {

			$my_settings_page = new AudioGeneratorSettingsPage();

        }

    }

}

endif;

add_action( 'plugins_loaded', array( 'AudioPageGeneratorPlugin', 'init' ), 10);
