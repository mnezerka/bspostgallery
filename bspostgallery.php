<?php
/*
vim: set expandtab sw=4 ts=4 sts=4 foldmethod=indent:
Plugin Name: BSPostGallery
dfsescription: WP Extension for google photos like gallery design
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
        //add_action('wp_enqueue_scripts',array($this, 'on_enqueue_scripts'));
        add_action('wp_footer', array($this, 'on_footer'));

    }

    public function on_enqueue_scripts()
    {
        wp_enqueue_script('1.chunk.js', $this->pluginUrl . 'js/1.chunk.js');
        wp_enqueue_script('main.chunk.js', $this->pluginUrl . 'js/main.chunk.js');
        wp_enqueue_script('runtime-main.js', $this->pluginUrl . 'js/runtime~main.js');
    }

    public function on_footer()
    {
        $this->on_enqueue_scripts();
    }

    public function onInit()
    {
         // Remove the default gallery shortcode implementation
        remove_shortcode( 'gallery' );
        // And replace it with our own!
        add_shortcode('gallery', array($this, 'gallery_shortcode'));
    }

    /**
    * The Gallery shortcode.
    *
    * This has been taken verbatim from wp-includes/media.php. There's a lot of good stuff in there.
    * All you want to do is add some more HTML to it, and since (for some reason) they didn't provide more
    * filters to be able to add, we have to replace the Gallery shortcode wholesale.
    *
    * @param array $attr Attributes of the shortcode.
    * @return string HTML content to display gallery.
    */
    public function gallery_shortcode($atts)
    {
        global $post;

        static $instance = 0;
        $instance++;

        $output = apply_filters('post_gallery', '', $attr);
        if ($output != '')
        {
            return $output;
        }

        if (isset( $attr['orderby']))
        {
            $attr['orderby'] = sanitize_sql_orderby($attr['orderby']);
            if (!$attr['orderby'])
            {
                unset($attr['orderby']);
            }
        }

        // NOTE: These are all the 'options' you can pass in through the shortcode definition, eg: [gallery itemtag='p']
        extract(shortcode_atts(array(
            'order'      => 'ASC',
            'orderby'    => 'menu_order ID',
            'id'         => $post->ID,
            'itemtag'    => 'dl',
            'icontag'    => 'dt',
            'captiontag' => 'dd',
            'columns'    => 3,
            'size'       => 'thumbnail',
            'include'    => '',
            'exclude'    => '',
            // Here's the new options stuff we added to the shortcode defaults
            'titletag'  => 'p',
            'descriptiontag' => 'p'
        ), $attr));

        $id = intval($id);
        if ('RAND' == $order)
        {
            $orderby = 'none';
        }

        if (!empty($include))
        {
            $include = preg_replace( '/[^0-9,]+/', '', $include);
            $_attachments = get_posts(array(
                'include' => $include,
                'post_status' => 'inherit',
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'order' => $order,
                'orderby' => $orderby));

            $attachments = array();
            foreach ($_attachments as $key => $val)
            {
                $attachments[$val->ID] = $_attachments[$key];
            }
        } elseif (!empty($exclude))
        {
            $exclude = preg_replace('/[^0-9,]+/', '', $exclude);
            $attachments = get_children(array(
                'post_parent' => $id,
                'exclude' => $exclude,
                'post_status' => 'inherit',
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'order' => $order,
                'orderby' => $orderby));
        } else {
            $attachments = get_children(array(
                'post_parent' => $id,
                'post_status' => 'inherit',
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'order' => $order,
                'orderby' => $orderby));
        }

        if (empty($attachments))
            return '';

        if (is_feed())
        {
            $output = "\n";
            foreach ($attachments as $att_id => $attachment)
                $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
            return $output;
        }

        $itemtag = tag_escape($itemtag);
        $captiontag = tag_escape($captiontag);
        $columns = intval($columns);
        $itemwidth = $columns > 0 ? floor(100/$columns) : 100;
        $float = is_rtl() ? 'right' : 'left';

        $selector = "gallery-{$instance}";

        $gallery_style = $gallery_div = '';
        if (apply_filters('use_default_gallery_style', true))
            $gallery_style = "
            <style type='text/css'>
                #{$selector} {
                    margin: auto;
                }
            </style>
            <!-- see gallery_shortcode() in wp-includes/media.php -->";

        $size_class = sanitize_html_class( $size );
        #$gallery_div = "<div id='$selector' class='gallery galleryid-{$id} gallery-columns-{$columns} gallery-size-{$size_class}'>";

        $gallery_div2 = "<div id='$selector'/>";

        $output = "
            <!-- data for bs react gallery -->
            <script type='text/javascript'>
                window.BSGALLERYNODEID = '$selector';
                window.BSGALLERYIMAGES = [];";


        foreach ($attachments as $id => $attachment)
        {
            /*
            $link = isset($attr['link']) && 'file' == $attr['link'] ?
                wp_get_attachment_link($id, $size, false, false) :
                wp_get_attachment_link($id, $size, true, false);
             */

            $img_url = wp_get_attachment_url($id);

            $img = wp_get_attachment_image_src($id, 'medium');

            // The DESCRIPTION, if we've not specified a blank 'descriptiontag'
            $img_description = '' . $attachment->post_content;

            $output .= "window.BSGALLERYIMAGES.push(
                {
                    src: '$img_url',
                    thumbnail: '$img[0]',
                    thumbnailWidth: $img[1],
                    thumbnailHeight: $img[2],
                    caption: '$img_description'
                });" . "\n";
        }

        $output .= '
            </script>
            <!-- data for bs react gallery -->';

        $output .= apply_filters('gallery_style', $gallery_style . "\n\t\t" . $gallery_div2);

        return $output;
    }
}

// create plugin instance
$bsGallery = new BSPostGallery();
?>
