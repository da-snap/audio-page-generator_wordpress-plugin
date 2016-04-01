<?php

if ( ! defined( 'ABSPATH' ) ) exit;
require_once( 'audio-generator.overview.class.php');
class AudioGenerator_Manager {

	public function __construct () {
		$this->activate_actions();
	}


	private function activate_actions () {
		add_action( 'admin_menu', array( $this, 'add_top_menu' ) );
		//add_action( 'admin_menu', array( $this, 'add_overview_menu' ) );
	}

	public function add_top_menu () {
		add_menu_page (
			__( 'AudioGenerator', 'audiogenerator' ),
			__( 'AudioGenerator', 'audiogenerator' ),
			'edit_pages',
			'audigenerator-manage-page',
			'AudioGenerator_Overview::index',
			plugin_dir_url(__FILE__).'../assets/icons/audiogenerator_icon.png'
		);
	}

	public function add_overview_menu () {
		$hook = add_submenu_page (
			'audiogenerator-manage-page',
			__( 'Überblick', 'audiogenerator' ),
			__( 'Überblick', 'audiogenerator' ),
			'edit_pages',
			'audiogenerator-manage-page',
			'AudioGenerator_Overview::index'
		);
		add_action( "load-$hook", 'AudioGenerator_Overview::add_help_tab' );
	}

}
