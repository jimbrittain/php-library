<?php

if (!class_exists('WP_CustomFeatureImagePlugin')) {
    class WP_CustomFeatureImagePlugin
    {
        public function __construct()
        {
            WP_CustomFeatureImagePlugin::dependencies();
        }
        public static function dependencies()
        {
            //require_once(sprintf("%s/ExtendedMimetypes.php", dirname(__FILE__)));
            //require_once(sprintf("%s/ExtendUploadMimetypes.php", dirname(__FILE__)));
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

if (class_exists('WP_CustomFeatureImagePlugin')) {
    register_activation_hook(__FILE__, array('WP_CustomFeatureImagePlugin', 'activate'));
    register_deactivation_hook(__FILE__, array('WP_CustomFeatureImagePlugin', 'deactivate'));
    register_uninstall_hook(__FILE__, array('WP_CustomFeatureImagePlugin', 'uninstall'));
    $IMCustomFeatureImage = new WP_CustomFeatureImagePlugin();
}

