<?php
/**
* Auto Ajax Plugin for WP
*
* @wordpress-plugin
* Plugin Name:       Auto Ajax
* Plugin URI:        http://onethingsimple.com/auto-ajax
* Description:       Makes local links use Ajax rather then reload the entire page. Quickly turn your WP Site into an Ajax SPA (Single Page Application)
* Version:           0.1.0
* Author:            Stayshine Web Development
* Author URI:        http://stayshine.com/
* License:           GPL-2.0+
* License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
* Text Domain:       autobeast
* Domain Path:       /languages
*/


class Auto_Ajax {

    // Default Options, will be updated by setup method during init
    public $default_div = '#content',
           $adv_load_div = '',
           $adv_menu_div = '',
           $auto_ajax_level = 'basic',
           $adv_fallback_div = '',
           $adv_bubble_query = 'false',
           $options;

    function __construct () {
        // $setup var is just for the 'add_admin_subpage' function
        $this->admin_setup = 0;
        $this->setup();
        if ( is_admin() ) {
            // Setup the tools sub-menu for options if loaded in Admin
            add_action( 'admin_menu', array( $this, 'add_admin_subpage') );
        }

        // Setup the Auto Ajax JavaScript for frontend, backend loads in settings script to simplify
        add_action( 'wp_enqueue_scripts', array($this, 'enqueue_autoajax_scripts') );

    }

    public function add_admin_subpage () {
        if ( !$this->admin_setup ) {
            // Tell WP that we want our own sub page
            add_management_page( 'Auto Ajax Settings', 'Auto Ajax', 'manage_options', 'rosata-auto-ajax', array($this, 'add_admin_subpage') );
            $this->admin_setup = true;
        } elseif($this->admin_setup) {
            // Display the page for the options menu
            include 'php/settings-menu.php';
        }
    }

    private function setup () {
        // Get the options for plugin from database
        $options = get_option('rosata-auto-ajax');
        // If not there, add empty array
        if ( !is_array($options) ){
            add_option('rosata-auto-ajax', array());
        }
        // Now make sure we have either updated or default values
        $this->default_div      = isset($options['default-div'])      ? $options['default-div'] : '#content';
        $this->adv_load_div     = isset($options['adv-load-div'])     ? $options['adv-load-div'] : '';
        $this->adv_menu_div     = isset($options['adv-menu-div'])     ? $options['adv-menu-div'] : '';
        $this->auto_ajax_level  = isset($options['auto-ajax-level'])  ? $options['auto-ajax-level'] : 'basic';
        $this->adv_fallback_div = isset($options['adv-fallback-div']) ? $options['adv-fallback-div'] : '';
        $this->adv_bubble_query = isset($options['adv-bubble-query']) ? $options['adv-bubble-query'] : 'false';
        
        // Update the options in database in case this is initial setup or options have been added in upgrade
        update_option('rosata-auto-ajax', array(
            'default-div'       => $this->default_div,
            'adv-load-div'      => $this->adv_load_div,
            'adv-menu-div'      => $this->adv_menu_div,
            'auto-ajax-level'   => $this->auto_ajax_level,
            'adv-fallback-div'  => $this->adv_fallback_div,
            'adv-bubble-query'  => $this->adv_bubble_query
        ));

        // Set the objects options array to the correct settings
        $this->options = $options;
    }

    /**
     * Static to comply a bit with the wp_enqueue_scripts
     * This function loads the JavaScript to handle setting up every page with the Ajax plugin
     */
    public function enqueue_autoajax_scripts () {

        // Enqueue the script for the frontend use
        wp_enqueue_script('auto-ajax-plugin', plugins_url('/js/auto-ajax.js',__FILE__), array('jquery'));
        // Localize the plugin options and needed data
        wp_localize_script('auto-ajax-plugin', 'autoAjaxConfigObject',
            array(
                'ajaxUrl' => admin_url('ajax'),
                'blogUrl' => site_url(),
                'options' => $this->options,
                'nonce' => wp_create_nonce('doing-auto-ajax')
            )
        );
    }

}

// Instanciate the Auto_Ajax object which starts up and manages the entire plugin
$auto_ajax = new Auto_Ajax();