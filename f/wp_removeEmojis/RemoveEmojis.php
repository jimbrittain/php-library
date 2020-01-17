<?php
defined('ABSPATH') or die('Script Error');
/**
Plugin Name: IM Remove Emojis
Description: Simple Plugin to remove Emojis as sick of writing the same lines in every functions.php
Author: Immature Dawn
Author URI: http://immaturedawn.co.uk
 */
if (!class_exists('IM_RemoveEmojis')) {
    class IM_RemoveEmojis
    {
        public function __construct()
        {
            if (function_exists('remove_action')) {
                remove_action('wp_head', 'print_emoji_detection_script', 7);
                remove_action('admin_print_scripts', 'print_emoji_detection_script');
                remove_action('wp_print_styles', 'print_emoji_styles');
                remove_action('admin_print_styles', 'print_emoji_styles');
            }
        }
    }
    $t = new IM_RemoveEmojis();
}
