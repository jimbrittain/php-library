<?php
/*
 * Plugin Name: IM Post Info Shortcodes
 * Description: Allows acces to Blog Info Shortcodes
 * Version: 0.1
 * Author: Immature Dawn
 * Author URI: http://immaturedawn.co.uk
 */
if (!class_exists('IMPostInfoShortCodes')) {
    if (!function_exists('isDateString')) {
        require_once('isDateString.php');
    }
    class IMPostInfoShortCodes 
    {
        static function get($atts) 
        {
            global $post;
            if (isset($post) && function_exists('shortcode_atts')) {
                extract(
                    shortcode_atts(
                        array('key' => '',),
                        $atts
                    )
                );
                $i = 0;
                $p = (array) $post;
                if (!empty($key) &&
                    property_exists($post, $key) &&
                    strpos($key, 'password') === false) {
                    $var = $p[$key];
                    //@codingStandardsIgnoreStart
                    $var = (isDateString($var)) ? date('d/m/Y', strtotime($var)) : $var;
                    //@codingStandardsIgnoreEnd
                    $var = (empty($var) ? '[UNDEFINED]' : htmlspecialchars($var, ENT_COMPAT, 'UTF-8', false));
                    return $var;
                }
            }
            return '[UNDEFINED]';
        }
        static function build() 
        {
            if (function_exists('add_shortcode')) {
                add_shortcode('postinfo', array('IMPostInfoShortCodes', 'get'));
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
if (class_exists('IMPostInfoShortCodes')) {
    register_activation_hook(__FILE__, array('IMPostInfoShortCodes', 'activate'));
    register_deactivation_hook(__FILE__, array('IMPostInfoShortCodes', 'deactivate'));
    register_uninstall_hook(__FILE__, array('IMPostInfoShortCodes', 'uninstall'));
    IMPostInfoShortCodes::build();
}

