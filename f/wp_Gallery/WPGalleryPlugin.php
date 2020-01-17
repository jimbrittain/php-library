<?php
/*
 * Plugin Name: WPGallery Plugin
 * Description: Creates WPGallery
 * Version: 0.2
 * Author: Immature Dawn
 * Author URI: http://immaturedawn.co.uk
 */
if (!class_exists('WPGalleryPlugin')) {
    class WPGalleryPlugin
    {
        private static $_initialised = false;
        private static $_galleryDeclared = array();
        private static $_isajax = null;
        private static $_definitionDeclared = array();
        public static $gkGalleryName = 'gallery';
        public static $gkGalleryPage = 'gp';
        public static $gkGalleryDefinition = 'gd';
        public static $gkGalleryOffset = 'go';
        public static $gkGalleryPreview = 'preview';
        public static $gkGalleryNumberToRetrieve = 'gnr';
        public static $gkGalleryRequest = 'gallreq';
        public static $gkAjax = 'ajax';
        public function __construct()
        {
            $this->dependencies();
            $this->_initVars(); //builds getvars;
            if (function_exists('add_shortcode')) {
                add_shortcode('gallery', 'WPGalleryPlugin::shortcode');
            }
            if (function_exists('add_action')) {
                add_action('wp_ajax_wpgalleryajax', 'WPGalleryPlugin::ajax');
                add_action('wp_ajax_nopriv_wpgalleryajax', 'WPGalleryPlugin::ajax');
                add_action('wp_enqueue_scripts', 'WPGalleryPlugin::loadJavascript');
            }
        }
        public function dependencies()
        {
            if (!class_exists('ArgumentHandler')) {
                require_once(sprintf("%s/ArgumentHandler.php", dirname(__FILE__)));
            }
            if (!class_exists('WPGallery')) {
                require_once(sprintf("%s/WPGallery.php", dirname(__FILE__)));
            }
            if (!class_exists('WPGalleryDefinition')) {
                require_once(sprintf("%s/WPGalleryDefinition.php", dirname(__FILE__)));
            }
        }
        public static function loadJavascript() 
        {
            wp_enqueue_script('WPGalleryJS', plugin_dir_url(__FILE__).'/js/wpgallery.lib.min.js');
            wp_localize_script('WPGalleryJS', 'ajaxObject', array('wpgalleryajl' => admin_url('admin-ajax.php')));
        }
        public static function shortcode($attr)
        {
            $str = '';
            $a = shortcode_atts(
                array(
                    'definition' => '',
                    'definition_vars' => '',
                    'gallery' => ''
                ), $attr
            );
            if (!empty($a['definition'])) {
                if (self::isDefinitionDeclared($a['definition'])) {
                    $g = new WPGallery(
                        array(
                            'definition' => $a['definition'],
                            'definitionVars' => $defVars
                        )
                    );
                    $str = $g->view();
                }
            } else if (!empty($a['gallery'])) {
                if (self::isGalleryDeclared($a['gallery'])) {
                    //process definiton vars;
                    $g = self::findGallery($a['gallery']);
                    $str = $g->view();
                }
            }
            return $str;
        }
        private function _initVars()
        {
            add_filter('query_vars', 'WPGalleryPlugin::buildVars');
        }
        public static function buildVars($vars)
        {
            $vars[] = self::$gkGalleryName; //gallery page
            $vars[] = self::$gkGalleryPage; //gallery identifier
            $vars[] = self::$gkGalleryOffset; //gallery offset
            $vars[] = self::$gkGalleryPreview; //gallery preview get var;
            $vars[] = self::$gkGalleryNumberToRetrieve;
            $vars[] = self::$gkAjax; //used for ajax protection
            $vars[] = self::$gkGalleryRequest; //gallery request;
            return $vars;
        }
        public static function getContextVars($name = '')
        {
            if (self::contextAppliesToGallery($name)) {
                $arr = array();
                if (self::getNumberToRetrieve($name) !== false) {
                    $arr['posts_per_page'] = self::getNumberToRetrieve($name);
                }
                if (self::getCurrentPageForGallery($name) !== false) {
                    // phpcs:disable
                    $arr['offset'] = (self::getCurrentPageForGallery($name) * self::findGallery($name)->getNumberPerPage());
                    // phpcs:enable
                }
            }
        }
        public static function contextAppliesToGallery($name = '')
        {
            if (self::isAjax()) { 
                return true; 
            }
            if (is_a($name, 'WPGallery')) {
                $name = $gall->getName();
            }
            $applies = self::validatedPassedName();
            if (!empty($applies)) {
                return ($applies === $name) ? true : false;
            }
            return true;
        }
        public static function isAjax()
        {
            if (self::$_isajax !== null) {
                return self::$_isajax;
            } else {
                $aj = $_GET['ajax'];
                if (is_string($aj) && !empty($aj) && $aj === 'true') {
                    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
                        || (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) 
                        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
                        if (!empty($_SERVER['HTTP_REFERER'])) {
                            $s = urldecode($_SERVER['HTTP_REFERER']);
                            $sinfo = parse_url($s);
                            self::$_isajax = ($sinfo['host'] === $_SERVER['HTTP_HOST']) ? true : false; 
                            return self::$_isajax;
                        }
                    }
                }
                self::$_isajax = false;
                return false;
            }
        }
        public static function getQueryVar($name = '')
        {
            if (self::isAjax()) {
                return (isset($_GET[$name])) ? $_GET[$name] : false;
            } else {
                return get_query_var($name);
            }
        }
        public static function viewWhich()
        {
            //get the gallrequest
            $gr = (self::isAjax()) ? self::getQueryVar(self::$gkGalleryRequest) : 'all';
            switch ($gr) {
                case 'preview':
                    return ['preview'];
                case 'inner':
                case 'navigation':
                    return ['thumbs', 'navigation', 'inner'];
                default:
                    return ['thumbs', 'navigation', 'inner', 'preview', 'outer'];
            }
        }
        public static function shouldView($section)
        {
            if (self::isAjax()) {
                $arr = self::viewWhich();
                return (in_array($section, $arr)) ? true : false;
            }
            return true;
        }
        /* ::::::: VALIDATION SHITE :::::::: */
        public static function validatedPassedDefinition()
        {
            $def = self::getQueryVar(self::$gkGalleryDefinition);
            $def = (is_string($def) && !empty($def)) ? urldecode($def) : '';
            $def = (preg_match('/^[A-Za-z][A-Za-z0-9\-\_\.]*$/', $def)) ? $def : '';
            $def = (self::isDefinitionDeclared($def)) ? $def : '';
            return $def;
        }
        public static function validatedPassedName()
        {
            $name = self::getQueryVar(self::$gkGalleryName);
            $name = (is_string($name) && !empty($name)) ? urldecode($name) : '';
            $name = (preg_match('/^[A-Za-z][A-Za-z0-9\-\_\.]*$/', $name)) ? $name : '';
            return $name;
        }
        public static function validatedPassedPage()
        {
            $pg = self::getQueryVar(self::$gkGalleryPage);
            return (is_numeric($pg) && (int) $pg == $pg && $pg > -1) ? $pg : false;
        }
        public static function validatedPassedPreview()
        {
            $pre = self::getQueryVar(self::$gkGalleryPreview);
            return (is_numeric($pre) && (int) $pre == $pre && $pre > 0) ? $pre : false;
        }
        public static function validatedPassedNumberToRetrieve()
        {
            $num = self::getQueryVar(self::$gkGalleryNumberToRetrieve);
            $num = (is_numeric($num) && (int) $num == $num && $num > 0) ? $num : false;
            return $num;
        }
        public static function validatedPassedOffset()
        {
            $num = self::getQueryVar(self::$gkGalleryOffset);
            return (is_numeric($num) && (int) $num == $num && $num > -1) ? $num : false;
        }
        /* ::::::::::::::::::::::: GET SHITE ::::::::::::::::::: */
        public static function getName($name = '')
        {
            return (self::contextAppliesToGallery($name)) ? self::validatedPassedName() : '';
        }
        public static function getDefinition($name = '')
        {
            return (self::contextAppliesToGallery($name)) ? self::validatedPassedDefinition() : false;
        }
        public static function getPreviewId($name = '')
        {
            return (self::contextAppliesToGallery($name)) ? self::validatedPassedPreview() : false;
        }
        public static function getNumberToRetrieve($name = '') 
        {
            $num = self::validatedPassedNumberToRetrieve($name);
            return (self::contextAppliesToGallery($name)) ? $num : false;
        }
        public static function getCurrentOffsetForGallery($name = '')
        {
            $gall = null;
            if (!empty($name)) {
                $gall = (is_a($name, 'WPGallery')) ? $name : self::findGallery($name);
            }
            if (self::contextAppliesToGallery($name)) {
                $num = self::validatedPassedOffset($name);
                if ($num === false) {
                    $pg = self::validatedPassedPage($name);
                    if ($pg !== false && is_a($gall, 'WPGallery')) {
                        $num = $gall->getNumberPerPage() * ($pg - 1);
                    }
                }
                return ($num === false || $num < 1) ? 0 : $num;
            }
            return 0;
        }
        public static function getCurrentPageForGallery($name = '')
        {
            $off = self::getCurrentOffsetForGallery($name);
            $gall = (is_a($name, 'WPGallery')) ? $gall : self::findGallery($name);
            if ($off !== false && !empty($name)) {
                return ceil($off/$gall->getNumberPerPage());
            } else {
               return 0;
            }
        }
        public static function addDefinition($d)
        {
            if (is_a($d, 'WPGalleryDefinition') && !self::isDefinitionDeclared($d->getName())) {
                self::$_definitionDeclared[] = $d;
            }
        }
        public static function addGallery($g)
        {
            if (is_a($g, 'WPGallery') && !self::isGalleryDeclared($g->getName())) {
                self::$_galleryDeclared[] = $g;
            }
        }
        public static function beenInitalised()
        {
            return $this->_initialised;
        }
        public static function isDefinitionDeclared($id)
        {
            return (self::findDefinition($id) !== false) ? true : false;
        }
        public static function findDefinition($id)
        {
            foreach (self::$_definitionDeclared as $d) {
                if ($d->getName() === $id) {
                    return $d;
                }
            }
            return false;
        }
        public static function isGalleryDeclared($id)
        {
            return (self::findGallery($id) !== false) ? true : false;
        }
        public static function findGallery($id)
        {
            foreach (self::$_galleryDeclared as $d) {
                if ($d->getName() === $id) {
                    return $d;
                }
            }
            return false;
        }
        public static function ajax()
        {
            global $wpdb;
            /* error_reporting(-1);
            ini_set('display_errors', 'stdout'); */
            $wpg = new WPGallery();
            echo $wpg->view();
            wp_die();
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
if (class_exists('WPGalleryPlugin')) {
    register_activation_hook(__FILE__, array('WPGalleryPlugin', 'activate'));
    register_deactivation_hook(__FILE__, array('WPGalleryPlugin', 'deactivate'));
    register_uninstall_hook(__FILE__, array('WPGalleryPlugin', 'uninstall'));
    $WPGalleryPlugin = new WPGalleryPlugin();
}
