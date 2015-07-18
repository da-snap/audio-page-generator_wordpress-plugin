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
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AudioPageGeneratorPlugin' ) ) :

class AudioPageGeneratorPlugin {

    public $version = '0.1';

    public $generat = null;

    public static function init() {

        $audiopagegenerator = new self();

    }

    public function __construct() {

        $this->define_constants();
        $this->includes();
        $this->setup_shortcode();
        $this->register_admin_menu();

    }

    private function define_constants() {

        define ( 'AUDIOPAGEGENERATOR_VERSION', $this->version );
        define ( 'AUDIOPAGEGENERATOR_PATH', plugin_dir_path(__FILE__) );
    }

    private function includes() {

        include AUDIOPAGEGENERATOR_PATH . 'inc/audio-generator.settings.class.php';
        include AUDIOPAGEGENERATOR_PATH . 'inc/audio-generator.id3-tags-reader.class.php';

    }
    
    /**
	* Register the [audioGenerator] shortcode.
	*/
	private function setup_shortcode() {

		add_shortcode( 'audioGenerator', array( $this, 'show_audio_page' ) );

	}

	public function register_admin_menu() {
     
    
        if( is_admin() ) {
        
            $my_settings_page = new AudioGeneratorSettingsPage();
     
        }
    
    }
    
    public function show_audio_page( $atts ) {
        
        wp_enqueue_script( 'Pagination', get_template_directory_uri() . '/js/pagination.js', array(), '1.0.0', true );
        $upload_dir = wp_upload_dir()['path'];
        $upload_dir .= "/*.mp3";
        $files = glob($upload_dir);
        // new object of our ID3TagsReader class
        $oReader = new ID3TagsReader();
        // passing through located files ..
        $sList = '<input type="hidden" id="current_page" /><input type="hidden" id="show_per_page" /><div class="sermon-view" id="wrapper">';
        foreach ($files as $sSingleFile) {
            $url = wp_upload_dir()['url'];
            $file_parts = explode( '\\', $sSingleFile );
            $link = $url . '/' . end($file_parts);
            $conf = array(
                'src'      => $link ,
                'loop'     => '',
                'autoplay' => '',
                'preload' => 'none'
            );
            $player = wp_audio_shortcode( $conf );
            $aTags = $oReader->getTagsInfo($sSingleFile); // obtaining ID3 tags info
            $sList .= '<div class="sermon"><div class="caption"><h2>'.$aTags['Comments'].'</h2></div><div id="download">Download MP3</div><div class="preacher"><h3>'.$aTags['Author'].'</h3>05.06.2015</div>'.$player.'</div>';
        }
        $sList .= '</div><div id="page_navigation"></div>';
        return $sList;
        
    }
}

endif;

add_action( 'plugins_loaded', array( 'AudioPageGeneratorPlugin', 'init' ), 10);
