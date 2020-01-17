<?php
/*
 * Plugin Name: WPOpenSearch Plugin
 * Description: Creates an Open Search Description for the Wordpress Site
 * Version: 0.1
 * Author: Immature Dawn
 * Author URI: http://immaturedawn.co.uk
*/
if (!class_exists('WPOpenSearchPlugin') && function_exists('get_bloginfo')) {
    class WPOpenSearchPlugin 
    {
        public function __construct()
        {
            self::_dependencies();
        }
        public static function activate()
        {
            return true;
        }
        public static function deactivate()
        {
            return true;
        }
        public static function uninstall()
        {
            return true;
        }
        public static function isEnabled()
        {
            return true;
        }
        public static function getUrl()
        {
            if (empty(self::$_url)) {
                self::$_url = plugin_dir_url(__FILE__).'/opensearchdescription.php';
            }
            return self::$_url;
        }
        public static function viewProfileAttr(Bool $includeProfileAttr = true)
        {
            $str = '';
            if (self::isEnabled()) {
                $includeProfileAttr = ($includeProfileAttr === null) ? true : $includeProfileAttr;
                $str .= ($includeProfileAttr) ? 'profile="' : ' ';
                $str .= 'http://a9.com/-/spec/opensearch/1.1/';
                $str .= ($includeProfileAttr) ? '"' : '';
                return $str;
            }
        }
        public static function viewLinkTag()
        {
            $str = '';
            if (self::isEnabled()) {
                //phpcs:disable
                $str .= '<link rel="search" type="application/opensearchdescription+xml" href="'.self::getUrl().'" title="Site Search" />';
                //phpcs:enable
            }
            return $str;
        }
    }
}
if (class_exists('WPOpenSearchPlugin')) {
    register_activation_hook(__FILE__, 'WPOpenSearchPlugin::activate');
    register_deactivation_hook(__FILE__, 'WPOpenSearchPlugin::deactivate');
    register_uninstall_hook(__FILE__, 'WPOpenSearchPlugin::uninstall');
}
