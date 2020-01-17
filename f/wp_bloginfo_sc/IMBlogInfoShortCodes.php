<?php
/*
 * Plugin Name: IM Blog Info Shortcodes
 * Description: Allows acces to Blog Info Shortcodes
 * Version: 0.2
 * Author: Immature Dawn
 * Author URI: http://immaturedawn.co.uk
 */
if (!class_exists('IMBlogInfoShortCodes')) {
    class IMBlogInfoShortCodes 
    {
        static function get($atts) 
        {
            if (function_exists('shortcode_atts')) {
                extract(
                    shortcode_atts(
                        array('key' => '',), 
                        $atts
                    )
                );
                $var = get_bloginfo($key);
                $var = (empty($var) ? '[UNDEFINED]' : htmlspecialchars($var, ENT_COMPAT, 'UTF-8', false));
                return $var;
            } else {
                return '[UNDEFINED]';
            }
        }
        static function build() 
        {
            if (function_exists('add_shortcode')) {
                add_shortcode('bloginfo', array('IMBlogInfoShortCodes', 'get'));
            }
        }
        static function activate() 
        {
        }
        static function deactivate() 
        {
        }
        static function uninstall() 
        {
        }
    }
}
if (class_exists('IMBlogInfoShortCodes')) {
    register_activation_hook(__FILE__, array('IMBlogInfoShortCodes', 'activate'));
    register_deactivation_hook(__FILE__, array('IMBlogInfoShortCodes', 'deactivate'));
    register_uninstall_hook(__FILE__, array('IMBlogInfoShortCodes', 'uninstall'));
    IMBlogInfoShortCodes::build();
}
