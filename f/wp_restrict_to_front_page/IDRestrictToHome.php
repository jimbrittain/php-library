<?php

/*
Plugin Name: IM Restrict To Page
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

if (!class_exists(IMRestrictToPagePlugin)) {
    class IMRestrictToPage
    {
        public $page = "";
        public function __construct($p = '')
        {
            echo "here";
            $this->page = (is_string($p) && !empty($p)) ? $p : "";
        }

        public function isThisPage()
        {
            if ($this->page === "") {
                return (function_exists('is_front_page')) ? is_front_page() : false;
            } else {
                //check if this->page === current page;
            }
        }

        private function canCheck()
        {
            if (function_exists('is_admin') && function_exists('is_login_page')) {
                return true;
            } else { 
                return false;
            }
        }
        public function should()
        {
            if ($this->canCheck()) {
                if (!$this->isThisPage() && !is_admin() && !is_login_page() && !headers_sent()) {
                    return true;
                }
            }
            return false;
        }
        public function redirect()
        {
            if (function_exists('wp_redirect')) {
                wp_redirect(home_url('index.php'));
                exit;
            }
        }
        public function restrict()
        {
            if ($this->should()) {
                $this->redirect();
            }
        }
    }
    
    class IMRestrictOpt
    {
        public $page = "";
        public $enabled = true;
        public function __construct($p = '')
        {
            $this->page = $p;
            $this->enabled = true;
        }
    }
    
    class IMRestrictToPagePlugin
    {
        public $optname = "IMRestrictToPagePlugin";
        
        public function __construct()
        {
            add_action('admin_init', array(&$this, 'adminInit'));
            add_action('admin_menu', array(&$this, 'addMenu'));
            $opt = $this->get();
            if ($opt->enabled) {
                add_action('template_redirect', array(&$this, 'restrict'));
            }
        }
        
        public function adminInit()
        {
            $this->init_settings();
        }
        public function initSettings()
        {
            register_setting($this->optname.'_group', $this->optname, array(&$this, 'sanitize'));
        }
        public function addMenu()
        {
            // @codingStandardsIgnoreStart
            add_options_page('Restrict To Page Plugin', 'Restrict To Page', 'manage_options', 'imrestricttopage', array(&$this, 'pluginSettingsPage'));
            // @codingStandardsIgnoreEnd
        }
        public function pluginSettingsPage()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            include(sprintf("%s/settings.php", dirname(__FILE__)));
        }
        
        public function get()
        {
            if (function_exists('get_option')) {
                $tempSerialized = get_option($this->optname);
                $temp = (!empty($tempSerialized)) ? unserialize($tempSerialized) : new IMRestrictOpt();
                return $temp;
            }
        }
        public function sanitize($p = "")
        {
            echo "here: sanitizing";
            if (is_a($p, 'IMRestrictOpt')) {
                return $p;
            } else if (empty($p) || is_string($p)) {
                $opt = new IMRestrictOpt($p);
                return $opt;
            } else {
                return $this->get();
            }
        }
        
        public function activate()
        {
            if (function_exists('add_option')) {
                $temp = new IMRestrictOpt();
                $serializedTemp = serialize($temp);
                add_option($this->optname, $serializedTemp);
            }
        }
        public function deactivate()
        {
            if (function_exists('update_option')) {
                $opt = $this->get();
                $opt->enabled = false;
                $serializedOpt = serialize($opt);
                update_option($this->optname, $serializedOpt);
            }
        }
        public function uninstall()
        {
            if (defined('WP_UNINSTALL_PLUGIN') && function_exists('delete_option')) {
                delete_option($this->optname);
            }
        }
        public function restrict()
        {
            $opt = $this->get();
            if ($opt->enabled) {
                $r = new IMRestrictToFrontPage($opt->page);
                $r->restrict();
            }
        }
    }
    if (class_exists('IMRestrictToPagePlugin')) {
        register_activation_hook(__FILE__, array('IMRestrictToPagePlugin', 'activate'));
        register_deactivation_hook(__FILE__, array('IMRestrictToPagePlugin', 'deactivate'));
        register_uninstall_hook(__FILE__, array('IMRestrictToPagePlugin', 'uninstall'));
        
        $imrestrictToPage = new IMRestrictToPagePlugin();
    }
}