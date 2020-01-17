<?php
/*
 * Plugin Name: WP_ExtendUploadMimeTypes
 * Description: Allows for extending mimetypes able to be uploaded
 * Version: 0.2
 * Author: Immature Dawn
 * Author URI: http://immaturedawn.co.uk
 */
if (!class_exists('WP_ExtendUploadMimetypesPlugin')) {
    class WP_ExtendUploadMimetypesPlugin 
    {
        public function __construct()
        {
            require_once(sprintf("%s/ExtendedMimetype.php", dirname(__FILE__)));
            require_once(sprintf("%s/ExtendUploadMimetypes.php", dirname(__FILE__)));
        }
        public static function activate()
        {
        }
        public static function deactivate()
        {
        }
        public static function uninstall()
        {
        }
    }
}
if (class_exists('WP_ExtendUploadMimetypesPlugin')) {
    register_activation_hook(__FILE__, array('WP_ExtendUploadMimetypesPlugin', 'activate'));
    register_deactivation_hook(__FILE__, array('WP_ExtendUploadMimetypesPlugin', 'deactivate'));
    register_uninstall_hook(__FILE__, array('WP_ExtendUploadMimetypesPlugin', 'uninstall'));
    $IMExtendUploadMimetypesPlugin = new WP_ExtendUploadMimetypesPlugin();
}
