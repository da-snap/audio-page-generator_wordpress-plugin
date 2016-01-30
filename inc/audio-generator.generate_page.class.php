<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class  AudioGeneratorAudioPage {

    public function show_audio_page( $atts ) {
        $options = get_option('audio-generator_name');
		wp_register_script( 'pagination', plugins_url('../js/pagination.js' , __FILE__ ) );
		// Localize the script with new data
		$translation_array = array(
				'next' => __( 'Next', 'audio-page-generator' ),
				'prev' => __( 'Prev', 'audio-page-generator' ),
				'items' => (string) $options['pagination_sites']
		);
		wp_localize_script( 'pagination', 'wp_data', $translation_array );
		// Enqueued script with localized data.
		wp_enqueue_script( 'pagination' );
        wp_enqueue_style( 'audio_page_style', plugins_url('../css/audio_page_style.css', __FILE__ )  );
        $upload_dir = $options['upload_dir'];
        $upload_dir_mp3 = $upload_dir . "/*.mp3";
        $files = glob($upload_dir_mp3);
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
		$getID3 = new getID3;
        foreach ($sortFiles as $sSingleFile) {
            $file_parts = explode( '/', $sSingleFile );
			$filename = end($file_parts);
			$link = explode('wp-content', $upload_dir);
			$link = $site . '/wp-content'. $link[1] . '/' . rawurlencode(end($file_parts));
            $conf = array(
                'src'      => $link,
                'loop'     => '',
                'autoplay' => '',
                'preload' => 'none'
            );
            $player = wp_audio_shortcode( $conf );
			$aTags = $getID3->analyze($sSingleFile);
			$aTags = $aTags['tags']['id3v2'];
			$download_link = '<a href="'
				.admin_url ('admin-post.php')
				.'?action=download&file=' . $filename . '">Download MP3</a>';
			$sList .= '<div class="audio-box"><div class="caption_audio"><h2>'
				.esc_html($aTags[$options['title_tag']][0])
				.'</h2></div>';
			if($options['download']){
				$sList .= '<div class="download_audio">'
						.$download_link . '</div>';
			}
			$sList .= '<div class="subtitle_audio"><h3>'
				.esc_html($aTags[$options['subtitle_tag']][0]) . '</h3>'
				.date ("d.m.Y ", filectime($sSingleFile)) . '</div>'
				.$player . '</div>';
        }
        $sList .= '</div><div id="page_navigation"></div>';
        return $sList;
    }

}

