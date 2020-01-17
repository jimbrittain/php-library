<?php

/*
Plugin Name: IM Restrict To Pages
Description: Restrict to a single page the pages made available by Wordpress
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
if (!function_exists('is_login_page')) {
  function is_login_page()
  {
    return in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'));
  }
}



if (!class_exists('IMRestrictToPagesPlugin')) {
    class IMRestrictToPagesPlugin
    {
        public static $optname = 'IMRestrictToPagesPlugin';
        public static $pagename = 'imrestricttopages';
        public function __construct()
        {
            require_once(sprintf("%s/settings.php", dirname(__FILE__)));
            require_once(sprintf("%s/inc/is_login_page", dirname(__FILE__)));
            require_once(sprintf("%s/inc/IMRestrictOpt.php", dirname(__FILE__)));
            require_once(sprintf("%s/inc/IMRestrictToPages.php", dirname(__FILE__)));
            $settings = new IMRestrictToPagesSettings();
            $plugin = plugin_basename(__FILE__);
            add_filter('plugin_action_links_'.$plugin, array($this, 'pluginSettingsLink'));
            $opt = self::get();
            if ($opt->enabled) {
                add_action('template_redirect', array(&$this, 'restrict'));
            }
        }
        public function restrict()
        {
            $opt = self::get();
            if ($opt->enabled) {
                $t = new IMRestrictToPages($opt);
                $t->restrict();
            }
        }
        public function pluginSettingsLink($links)
        {
            $settingsLink = '<a href="options-general.php?page='.self::$pagename.'">Settings</a>';
            array_unshift($links, $settingsLink);
            return $links;
        }
        public static function get()
        {
            if (function_exists('get_option')) {
                $temp = get_option(self::$optname);
                return self::unsanitze($temp);
            } else {
                return new IMRestrictOpt();
            }
        }
        public static function sanitize($p = '')
        {
            $temp = self::get();
            if (is_a($p, 'IMRestrictOpt')) {
                $temp = clone $p;
            } else if (is_string($p)) {
                $t = unserialize($p);
                if ($t !== false && is_a($t, 'IMRestrictOpt')) {
                    $temp = clone $t;
                } else {
                    $t = new IMRestrictOpt($p);
                    $temp = clone $t;
                }
            } else if (is_array($p)) {
                $p = new IMRestrictOpt($p);
                $temp = clone $t;
            }
            return serialize($temp);
        }
        public static function unsanitize($p = "")
        {
            if (is_string($p)) {
                $p = unserialize($p);
            }
            if (is_a($p, 'IMRestrictOpt')) {
                return $p;
            } else {
                return new IMRestrictOpt();
            }
        }
        public static function activate()
        {
            if (function_exists('add_option')) {
                $temp = self::get();
                add_option(self::$optname, $temp);
            }
        }
        public static function deactivate()
        {
            $opt = self::get();
            $opt->enabled = false;
            update_option(self::$optname, $opt);
        }
        public static function uninstall()
        {
            if (defined('WP_UNINSTALL_PLUGIN') && function_exists('delete_option')) {
                delete_option(self::$optname);
            }
        }
    }
}
if (class_exists('IMRestrictToPagesPlugin')) {
    register_activation_hook(__FILE__, array('IMRestrictToPagesPlugin', 'activate'));
    register_deactivation_hook(__FILE__, array('IMRestrictToPagesPlugin', 'deactivate'));
    register_uninstall_hook(__FILE__, array('IMRestrictToPagesPlugin', 'uninstall'));
    $IMRestrictToPagesPlugin = new IMRestrictToPagesPlugin();
}