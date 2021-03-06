<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // disable direct access
}

class AudioGeneratorSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct($tags)
    {
		$this->tags = $tags;
        add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'page_init' ) );
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            __('Settings Admin', 'audio-page-generatori'),
            __('AudioGenerator Settings', 'audio-page-generator'),
            'manage_options',
            'audioa-generator-settings',
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'audio-generator_name' );
        ?>
        <div class="wrap">
            <h2>Generate Audio Settings</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'audio-generator_group' );
                do_settings_sections('audio-generator-setting-admin' );
                submit_button(); 
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {        
        register_setting(
            'audio-generator_group', // Option group
            'audio-generator_name', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            __('Customize Settings', 'audio-page-generator'),
            array( $this, 'print_section_info' ), // Callback
            'audio-generator-setting-admin' // Page
        );  

        add_settings_field(
            'pagination_sites', // ID
            __('How man Items shuld a page have?',  'audio-page-generator'),
            array( $this, 'pagination_sites_callback' ), // Callback
            'audio-generator-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'upload_dir',
            __('Upload Dir', 'audio-page-generator'),
            array( $this, 'upload_dir_callback'),
            'audio-generator-setting-admin',
            'setting_section_id'
        );

        add_settings_field(
            'download',
            __('Download Link', 'audio-page-generator'),
            array( $this, 'is_downloadable_callback'),
            'audio-generator-setting-admin',
            'setting_section_id'
        );

        add_settings_field(
            'title_tag',
            __('Which tag shuld shown in Titel:', 'audio-page-generator'),
            array( $this, 'title_tag_callback'),
            'audio-generator-setting-admin',
            'setting_section_id'
        );

        add_settings_field(
            'subtitle_tag',
            __('Which tag shuld shown in Subtitel:', 'audio-page-generator'),
            array( $this, 'subtitle_tag_callback'),
            'audio-generator-setting-admin',
            'setting_section_id'
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['pagination_sites'] ) )
            $new_input['pagination_sites'] = absint( $input['pagination_sites'] );

        if( isset( $input['upload_dir'] ) )
            $new_input['upload_dir'] = sanitize_text_field( $input['upload_dir'] );

        if( isset( $input['download'] ) && $input['download'] == '1') {
            $new_input['download'] = true;
        } else {
            $new_input['download'] = false;
        }
        if( isset( $input['title_tag'] ) )
            $new_input['title_tag'] = sanitize_text_field( $input['title_tag'] );
        if( isset( $input['subtitle_tag'] ) )
            $new_input['subtitle_tag'] = sanitize_text_field( $input['subtitle_tag'] );

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        //print 'Enter your settings below:';
		_e('Put [audiGenerator] in our side, where the Tracks shuld be shown', 
					'audio-page-generator.');
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function pagination_sites_callback()
    {
        printf(
            '<input type="text" id="pagination_sites" name="audio-generator_name[pagination_sites]" value="%s" />',
            isset( $this->options['pagination_sites'] ) ? esc_attr( $this->options['pagination_sites']) : '10'
        );
    }

    public function upload_dir_callback()
    {
        printf(
            '<input type="text" id="uplaod_dir" name="audio-generator_name[upload_dir]" value="%s" />',
            isset( $this->options['upload_dir'] ) ? esc_attr( $this->options['upload_dir']) : ''
        );
    }

    public function is_downloadable_callback() {
        printf(
            '<input type="checkbox" id="download" name="audio-generator_name[download]" value="1" %s>',
            isset( $this -> options['download'] ) && $this->options['download'] == '1' ? 'checked' : ''
        );
    }

    public function title_tag_callback()
    {
        printf('<select id="title_tag" name="audio-generator_name[title_tag]">');
		printf('<option value="">Select...</option>');
		foreach( $this->tags as  $key => $tag ) {
			printf('<option value="' . $key . '" %s>'. $tag . '</option>', 
					$this->options['title_tag'] == $key ? 'selected' : '');
		}
    }
    public function subtitle_tag_callback()
    {
        printf('<select id="subtitle_tag" name="audio-generator_name[subtitle_tag]">');
		printf('<option value="">Select...</option>');
		foreach( $this->tags as  $key => $tag ) {
			printf('<option value="' . $key . '" %s>'. $tag . '</option>', 
					$this->options['subtitle_tag'] == $key ? 'selected' : '');
		}
    }
}
