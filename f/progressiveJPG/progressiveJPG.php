<?php
defined('ABSPATH') or die('Script Error');
/**
Plugin Name: IM Progressive JPG
Description: Saves JPGs as Progressive Images
Author: Immature Dawn
Author URI: http://immaturedawn.co.uk
Version: 0.1
*/
if (!class_exists('IM_ProgressiveJPG')) {
    class IM_ProgressiveJPG
    {
        public static $initialised = false;
        public function __construct()
        {
            self::init();
        }
        public static function init()
        {
            if (function_exists('add_filter') && !self::$initialised) {
                add_filter('image_save_pre', array('IM_ProgressiveJPG', 'process'), 10, 2);
                add_filter('image_editor_save_pre', array('IM_ProgressiveJPG', 'process'), 10, 2);
                add_filter('wp_save_image_file', array('IM_ProgressiveJPG', 'processWithMime'), 10, 4);
                add_filter('wp_save_image_editor_file', array('IM_ProgressiveJPG', 'processWithMime'), 10, 4);
                self::$initialised = true;
            }
        }
        public static function gdInterface($image)
        {
            if (function_exists('imageinterlace')) {
                    imageinterlace($image, 1);
            }
            return $image;
        }
        public static function processWithMime($file, $image, $mime, $postID)
        {
            echo '<script type="text/javascript">console.log("'.$mime.'");</script>';
            if ($mime === "image/jpeg") {
                return self::gdInteface($image);
            } else {
                return $image;
            }
        }
        public static function process($image, $postID)
        {
            if (function_exists('imageinterlace')) {
                return self::gdInterface($image);
            } else {
                return $image;
            }
        }
    }
    IM_ProgressiveJPG::init();
}
