<?php
/*
Plugin Name: Multi-Src Image Plugin
Description: Enables Multi-Src Image Plugin
Version: 0.1
Author: Immature Dawn
License: GPL2
Copyright 2017 Jim Brittain jim@immaturedawn.co.uk
 */
if (!class_exists('MultiSrcImagePlugin')) {
    class MultiSrcImagePlugin
    {
       function __construct()
       {
           $this->dependencies();
       }
       public function dependencies()
       {
           if (!class_exists('MultiSrcImage')) {
                include_once('MultiSrcImage.php');
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
if (class_exists('MultiSrcImagePlugin')) {
    $fandCoStaffPlugin = new MultiSrcImagePlugin();
    register_activation_hook(__FILE__, array('MultiSrcImagePlugin', 'activate'));
    register_deactivation_hook(__FILE__, array('MultiSrcImagePlugin', 'deactivate'));
    register_uninstall_hook(__FILE__, array('MultiSrcImagePlugin', 'uninstall'));
}
