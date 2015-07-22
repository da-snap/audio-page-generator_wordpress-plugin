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

    public $version = '0.1';

    public $generat = null;

    public static function init() {

        $audiopagegenerator = new self();

    }

    public function __construct() {
		$this->load_textdomain();
        $this->define_constants();
        $this->includes();
        $this->setup_shortcode();
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
        include AUDIOPAGEGENERATOR_PATH . 'inc/audio-generator.id3-tags-reader.class.php';
        //include AUDIOPAGEGENERATOR_PATH . 'inc/audio-generator.download.class.php';

    }
    
    /**
	* Register the [audioGenerator] shortcode.
	*/
	private function setup_shortcode() {

		add_shortcode( 'audioGenerator', array( $this, 'show_audio_page' ) );

	}

	public function register_admin_menu() {
     
    
        if( is_admin() ) {
        
			$TagsReader = new ID3TagsReader();
			$aAvailabelTags = $TagsReader->getAvailabel23Tags();
			$my_settings_page = new AudioGeneratorSettingsPage($aAvailabelTags);
     
        }
    
    }
    
    public function show_audio_page( $atts ) {
        $options = get_option('audio-generator_name');
		wp_register_script( 'pagination', plugins_url('/js/pagination.js' , __FILE__ ) );
		// Localize the script with new data
		$translation_array = array(
				'next' => __( 'Next', 'audio-page-generator' ),
				'prev' => __( 'Prev', 'audio-page-generator' ),
		 		'items' => (string) $options['pagination_sites']
		);
		wp_localize_script( 'pagination', 'wp_data', $translation_array );
		// Enqueued script with localized data.
		wp_enqueue_script( 'pagination' );
        wp_enqueue_style( 'audio_page_style', plugins_url('/css/audio_page_style.css', __FILE__ )  );
        $upload_dir = $options['upload_dir'];
        $upload_dir_mp3 = $upload_dir . "/*.mp3";
        $files = glob($upload_dir_mp3);
        // new object of our ID3TagsReader class
        $oReader = new ID3TagsReader();
        // passing through located files ..
		$sList = '<input type="hidden" id="current_page" />'
				 .'<input type="hidden" id="show_per_page" />'
				 .'<div id="audio-generator-view">';
		$site = get_site_url();
		//Sort by creation time
		$sortFiles = array();
		foreach ($files as $sSingleFile){
			$key = filectime($sSingleFile);
			$sortFiles[$key] = $sSingleFile;
		}
		krsort($sortFiles);
        foreach ($sortFiles as $sSingleFile) {
            $file_parts = explode( '/', $sSingleFile );
			$link = explode('wp-content', $upload_dir);
			$link = $site . '/wp-content'. $link[1] . '/' . rawurlencode(end($file_parts));
			//return $link;
            $conf = array(
                'src'      => $link,
                'loop'     => '',
                'autoplay' => '',
                'preload' => 'none'
            );
            $player = wp_audio_shortcode( $conf );
            $aTags = $oReader->getTagsInfo($sSingleFile); // obtaining ID3 tags info
			$download_link = '<a href="'
				.plugins_url('inc/audio-generator.download.class.php' ,__FILE__) 
				.'?file=' . $link . '">Download MP3</a>';
			$sList .= '<div class="audio-box"><div class="caption_audio"><h2>'
				.esc_html($aTags[$options['title_tag']])
				.'</h2></div>';
			if($options['download']){
				$sList .= '<div class="download_audio">'
						  .$download_link . '</div>';
			}
			$sList .= '<div class="subtitle_audio"><h3>'
				.esc_html($aTags[$options['subtitle_tag']]) . '</h3>'
				.date ("d.m.Y ", filectime($sSingleFile)) . '</div>'
				.$player . '</div>';
        }
        $sList .= '</div><div id="page_navigation"></div>';
        return $sList;
    }
}

endif;

add_action( 'plugins_loaded', array( 'AudioPageGeneratorPlugin', 'init' ), 10);
