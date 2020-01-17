<?php

/*
Plugin Name: IM Contact Form
Description: Contact Form for Wordpress
Version: 0.1
Author: Immature Dawn
Author URI: http://immaturedawn.co.uk
License: GPL2
*/
/*
Copyright 2015  Jim Brittain jim@immaturedawn.co.uk

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists('IMContactFormPlugin')) {
    /**
     * @class IMContactFormPlugin
     * @constructor
     * 
     */
    class IMContactFormPlugin
    {
        /**
         *@property optname
         * @type String
         * @default 'IMContactForm'
         */
        public static $optname = 'IMContactForm';
        /*
         * @property pagename
         * @type String
         * @default 'imcontactform'
         */
        public static $pagename = 'imcontactform';
        /**
         * @method getTo
         * @static
         * @return {String} validated email address from either the stored WP option
         *  or from the generic WP admin_email. Then address validated
         */
        public static function getTo()
        {
            if (function_exists('get_option')) {
                $t = get_option(self::$optname);
                if (!isEmailAddress($t)) {
                    $t = get_option('admin_email');
                }
                return (isEmailAddress($t)) ? $t : '';
            }
        }
        /**
         * constructorm uses settings and class includes assumes files in same
         * folder as this class script. Builds settings suitable for wordpress.
         * Generic call on constructor.
         * @method __construct
         */
        public function __construct()
        {
            require_once(sprintf("%s/settings.php", dirname(__FILE__)));
            require_once(sprintf("%s/IMContactForm.php", dirname(__FILE__)));
            $settings = new IMContactFormSettings();
            $plugin = plugin_basename(__FILE__);
            add_filter('plugin_action_links_'.$plugin, array($this, 'pluginSettingsLink'));
            add_shortcode('contactform', array('IMContactFormPlugin', 'shortcode'));
        }
        /**
         * Adds the links to settings for the plugin at the beginning of its links
         * @method pluginSettingsLink
         * @param type {Array} $links
         * @return type {Array}
         */
        public function pluginSettingsLink($links)
        {
            $settingsLink = '<a href="options-general.php?page='.self::$pagename.'">Settings</a>';
            array_unshift($links, $settingsLink);
            return $links;
        }
        /**
         * Static method used to sanitize the email address either prior to submission or returned
         * uses WP method 'sanitize_email'
         * @method sanitize
         * @static
         * @param type {String} $r
         * @return type {String}
         * @requires method isEmailAddress, method sanitize_email (WP)
         * 
         */
        public static function sanitize($r)
        {
            $temp = (function_exists('sanitize_email')) ? sanitize_email($r) : $r;
            $temp = (isEmailAddress($temp)) ? $temp : "";
            return $temp;
        }
        /**
         * Static method used by WP upon plugin activation, add_option (which fails
         * if option already exists
         * @method activate
         * @static
         * @requires method add_option (WP)
         */
        public static function activate()
        {
            if (function_exists('add_option')) {
                $created = add_option(self::$optname, "");
            }
        }
        /**
         * Static method used by WP upon plugin deactivation
         * @method deactivate
         * @static
         */
        public static function deactivate()
        {
        }
        /**
         * Static method used by WP upon plugin uninstall, deletes option
         * @method uninstall
         * @static
         * @requires method delete_option (WP)
         */
        public static function uninstall()
        {
            if (defined('WP_UNINSTALL_PLUGIN') && function_exists('delete_option')) {
                delete_option(self::$optname);
            }
        }
        /**
         * Static method to create a shortcode, allows for different contact form names if necessary
         * @method shortcode
         * @static
         * @requires wordpress content environment and filtering of content enabled;
         */
        public static function shortcode($attr = array())
        {
            $name = '';
            if (function_exists('shortcode_atts')) {
                $attr = shortcode_atts(array('name' => ''), $attr);
                if (isset($attr['name']) && is_string($attr['name'])) {
                    $name = $attr['name'];
                }
            }
            $n = new IMContactForm($name);
            return $n->html();
        }
    }
    // @IgnoreCodingStandardsStart - wordpress style function name;
    function get_im_contact_form()
    {
    // @IgnoreCodingStandards End
        $n = new IM_ContactForm();
        echo $n->html();
    }
}
if (class_exists('IMContactFormPlugin')) {
    register_activation_hook(__FILE__, array('IMContactFormPlugin', 'activate'));
    register_deactivation_hook(__FILE__, array('IMContactFormPlugin', 'deactivate'));
    register_uninstall_hook(__FILE__, array('IMContactFormPlugin', 'uninstall'));
    $IMContactFormPlugin = new IMContactFormPlugin();
}
