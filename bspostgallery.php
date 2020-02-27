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

/*
 * Implementation of BSPostGallery plugin
 */
class BSPostGallery
{
    // path to plugin in file system
    protected $pluginPath;

    // url of the plugin
    protected $pluginUrl;

    public function __construct()
    {
        $this->pluginPath = plugin_dir_path(__FILE__);
        $this->pluginUrl = plugin_dir_url(__FILE__);

        add_action('init', array($this, 'onInit'));
        add_action('wp_footer', array($this, 'on_footer'));
    }

    /*
     * Called when rendering footer. Gallery scripts are inserted here
     * to be sure all image definitions and gallery DOM node are already
     * defined and rendered
     */
    public function on_footer()
    {
        wp_enqueue_script('bsreactgallery.js', $this->pluginUrl . 'js/bsreactgallery.js');
    }

    public function onInit()
    {
        // Remove the default gallery shortcode implementation
        remove_shortcode('gallery');
        // And replace it with our own!
        add_shortcode('gallery', array($this, 'gallery_shortcode'));
    }

    /**
    * The Gallery shortcode.
    *
    * This has been inspired by wp-includes/media.php. We had to replace whole
    * implementation of gallery shortcode since (for some reason) they didn't provide more
    * filters to be able to add custom stuff.
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

        if (isset($attr['orderby']))
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

        // we have all attachments, let's start rendering html

        if (is_feed())
        {
            $output = "\n";
            foreach ($attachments as $att_id => $attachment)
                $output .= wp_get_attachment_link($att_id, $size, true) . "\n";
            return $output;
        }

        $output = '<!-- BEGIN ReactGallery -->';
        $output .= '<!-- see gallery_shortcode() in wp-includes/media.php -->';

        $selector = "gallery-{$instance}";

        $output .= "
        <!-- BEGIN react gallery style -->

        <style type='text/css'>
            #{$selector} {
                margin: auto;
            }
            #{$selector} .ReactGridGallery {
                overflow: hidden;
            }
        </style>
        <!-- END react gallery style -->";

        $output .= "
            <!-- BEGIN data for bs react gallery -->
            <script type='text/javascript'>
        ";

        if ($instance == 1)
        {
            $output .= "window.BSPOSTGALLERY = []; var g = {};";
        }

        $output .= "g = {node: '$selector', images: []};";

        foreach ($attachments as $id => $attachment)
        {
            $img_url = wp_get_attachment_url($id);

            $img = wp_get_attachment_image_src($id, 'medium');

            $img_description = '' . $attachment->post_content;

            $output .= "g.images.push(
                {
                    src: '$img_url',
                    thumbnail: '$img[0]',
                    thumbnailWidth: $img[1],
                    thumbnailHeight: $img[2],
                    caption: '$img_description'
                });" . "\n";
        }

        $output .= '
                window.BSPOSTGALLERY.push(g);
            </script>
            <!-- END data for bs react gallery -->';
        //
        $output .= '<div id="' . $selector . '"></div>';
        $output .= '<!-- END ReactGallery -->';

        return $output;
    }
}

// create plugin instance
$bsGallery = new BSPostGallery();
?>
