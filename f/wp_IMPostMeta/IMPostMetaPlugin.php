<?php
/*
Plugin Name: Immature Dawn Post Meta Plugin
Description: Simplified OO Post Meta Creation
Version: 0.1
Author: Immature Dawn
License: GPL2
Copyright 2017 Jim Brittain jim@immaturedawn.co.uk
 */
if (!class_exists('IMPostMetaPlugin')) {
    class IMPostMetaPlugin
    {
       function __construct()
       {
           if (!class_exists('IMPostMeta')) {
               include_once('wp_IMPostMeta.php');
           }
           if (!function_exists('getPostId')) {
               include_once('getPostId.php');
           }
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
if (class_exists('IMPostMetaPlugin')) {
    $imPostMetaPlugin = new IMPostMetaPlugin();
    register_activation_hook(__FILE__, array('IMPostMetaPlugin'), 'activate');
    register_deactivation_hook(__FILE__, array('IMPostMetaPlugin'), 'deactivate');
    register_uninstall_hook(__FILE__, array('IMPostMetaPlugin'), 'uninstall');
}
