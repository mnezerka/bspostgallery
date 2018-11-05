<?php
/*
vim: set expandtab sw=4 ts=4 sts=4 foldmethod=indent:
Plugin Name: BSPostGallery
Description: WP Extension for google photos like gallery design
Version: 1.0
Author: Michal Nezerka
Author URI: http://blue.pavoucek.cz
Text Domain: bspostgallery
Domain Path: /languages
*/

// Require additional code
// require_once('bsmetabox.php');

class BSPostGallery
{
    protected $pluginPath;
    protected $pluginUrl;

    public function __construct()
    {
        $this->pluginPath = plugin_dir_path(__FILE__);
        $this->pluginUrl = plugin_dir_url(__FILE__);

        add_action('init', array($this, 'onInit'));
        // add_action('plugins_loaded', array($this, 'onPluginsLoaded'));
    }

    public function onInit()
    {
        ;
        //remove_shortcode( 'gallery' ); // Remove the default gallery shortcode implementation
        //add_shortcode( 'gallery', array( __CLASS__, "gallery_shortcode" ) ); // And replace it with our own!
    }
}

// create plugin instance
$bsGallery = new BSPostGallery();

?>
