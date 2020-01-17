<?php
/*
 * Plugin Name: GlobalCustomFields
 * Description: Creates Global Custom Fields
 * Version: 0.2
 * Author: Immature Dawn
 * Author URI: http://immaturedawn.co.uk
 */
if (!function_exists('globalfield_array')) {
      function &globalfield_array()
      {
        if (!isset($GLOBALS['im_GLOBALCUSTOMFIELDS'])) {
          $GLOBALS['im_GLOBALCUSTOMFIELDS'] = array();
        }
        return $GLOBALS['im_GLOBALCUSTOMFIELDS'];
      }
}
/*
    @module IMGlobalCustomField
    @class IMGlobalCustomFieldPlugin
    GLOBALCUSTOMFIELD - based on initial code by David Hollander, http://www.soapboxdave.com
    on https://digwp.com/2009/09/global-custom-fields-take-two/
    Used to create the WP plugin fields
    TODO add proper interface, activate and deactivate functionaliy
 */
if (!class_exists('IMGlobalCustomFieldPlugin')) {
    class IMGlobalCustomFieldPlugin
    {
        public static $pagename = 'imglobalcustomfields';
        public static $groupname = 'imglobalcustomfields';
        public function __construct()
        {
            require_once(sprintf("%s/settings.php", dirname(__FILE__)));
            require_once(sprintf("%s/GlobalCustomField.php", dirname(__FILE__)));
            require_once(sprintf("%s/functions.php", dirname(__FILE__)));
            $settings = new IMGlobalCustomFieldSettings();
            $plugin = plugin_basename(__FILE__);
            add_filter('plugin_action_links_'.$plugin, array($this, 'pluginSettingsLink'));
            add_shortcode('globalcf', array($this, 'shortcode'));
        }
        public function shortcode($attr)
        {
            $str = '';
            $a = shortcode_atts(
                array(
                    'field' => '',
                ), $attr
            );
            if (!empty($a['field'])) {
                if (function_exists('getGlobalField')) {
                    $r = getGlobalField($a['field']);
                    $str = ($r != false) ? $r : '[UNDEFINED]';
                }
            }
            return $str;
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
        public function pluginSettingsLink($links)
        {
            $settingsLink = '<a href="options-general.php?page='.self::$pagename.'">Settings</a>';
            array_unshift($links, $settingsLink);
            return $links;
        }
    }
}
if (class_exists('IMGlobalCustomFieldPlugin')) {
    register_activation_hook(__FILE__, array('IMGlobalCustomFieldPlugin', 'activate'));
    register_deactivation_hook(__FILE__, array('IMGlobalCustomFieldPlugin', 'deactivate'));
    register_uninstall_hook(__FILE__, array('IMGlobalCustomFieldPlugin', 'uninstall'));
    $IMGlobalCustomFieldPlugin = new IMGlobalCustomFieldPlugin();
}
