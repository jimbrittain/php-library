<?php
    /**
    * Plugin Name: IM User Form
    * Description: User Form for Wordpress
    * Version: 0.2
    * Author: Immature Dawn
    * Author URI: http://immaturedawn.co.uk
    * License: GPL2
    **/
    //@codingStandardsIgnoreStart
    /**
    * Copyright 2016  Jim Brittain jim@immaturedawn.co.uk
    * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.
    * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
    * You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
    **/
    //@codingStandardsIgnoreEnd
if (!class_exists('UserFormPlugin')) {
    class UserFormPlugin
    {
        public static $optname = 'UserForm';
        public static $pagename = 'userform';
        public static $ajaxpage = 'ajax.php';
        public function __construct()
        {
            require_once(sprintf('%s/settings.php', dirname(__FILE__)));
            if (!function_exists('isEmailAddress')) {
                require_once(sprintf('%s/inc/f/isEmailAddress.php', dirname(__FILE__)));
            }
            if (!class_exists('UserForm')) {
                require_once(sprintf('%s/UserForm.php', dirname(__FILE__)));
            }
            $settings = new UserFormSettings();
            $plugin = plugin_basename(__FILE__);
            add_filter('plugin_action_links_'.$plugin, array($this, 'pluginSettingsLink'));
        }
        public function pluginSettingsLink($links)
        {
            $settingsLink = '<a href="options-general.php?page='.self::$pagename.'">Settings</a>';
            array_unshift($links, $settingsLink);
            return $links;
        }
        public static function activate()
        {
            if (function_exists('add_option')) {
                $created = add_option(self::$optname, "");
            }
        }
        public static function deactivate()
        {
        }
        public static function uninstall()
        {
            if (defined('WP_UNINSTALL_PLUGIN') && function_exists('delete_option')) {
                delete_option(self::$optname);
            }
        }
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
        public static function addFormShortcode(UserForm $form)
        {
            if (function_exists('add_shortcode')) {
                add_shortcode('uf_'.$form->name, array($form, 'html'));
            }
        }
        public static function addWPAjax(UserForm $form)
        {
            if (function_exists('add_action')) {
                add_action('wp_ajax_nopriv_uf_'.$form->name, array($form, 'ajax'));
                // a priviledges version is also added in case the user is logged in
                add_action('wp_ajax_uf_'.$form->name, array($form, 'ajax')); 
            }
        }
    }
}
if (class_exists('UserFormPlugin')) {
    register_activation_hook(__FILE__, array('UserFormPlugin', 'activate'));
    register_deactivation_hook(__FILE__, array('UserFormPlugin', 'deactivate'));
    register_uninstall_hook(__FILE__, array('UserFormPlugin', 'uninstall'));
    $userFormPlugin = new UserFormPlugin();
}
/*
add_action('wp_ajax_my_ajax', 'my_ajax');
function my_ajax()
{
    die("Hello World");
} */
