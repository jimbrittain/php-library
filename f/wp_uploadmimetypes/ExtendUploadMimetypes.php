<?php
if (!class_exists('ExtendedUploadMimetypes')) {
    class ExtendedUploadMimetypes
    {
        public static $mimes = array();
        private static $_init = false;
        public function __construct()
        {
            $args = func_get_args();
            $args = (count($args) === 1) ? $args[0] : $args; //assumes wrapping arr
            if (count($args) > 0) {
                // @codingStandardsIgnoreStart - line-length
                if (count($args) == 2 && isset($args[0]) && isset($args[1]) && is_string($args[0]) && is_string($args[1])) {
                // @codingStandardsIgnoreEnd
                    $m = new ExtendedMimetype($args[0], $args[1]);
                    if (ExtendedUploadMimetypes::isValid()) {
                        ExtendedUploadMimetypes::add($m);
                    }
                } else {
                    foreach ($args as $a) {
                        $a = new ExtendedMimetype($a);
                        if ($a->isValid()) {
                            ExtendedUploadMimetypes::add($a);
                        }
                    }
                }
            }
        }
        public static function init()
        {
            if (!ExtendedUploadMimetypes::$_init && count(ExtendedUploadMimetypes::$mimes) > 0) {
                ExtendedUploadMimetypes::addFilter();
                ExtendedUploadMimetypes::$_init = true;
            }
        }
        public static function addFilter()
        {
            if (function_exists('add_filter')) {
                add_filter('upload_mimes', array('ExtendedUploadMimetypes', 'returnMimes'), 1, 1);
            }
        }
        public static function returnMimes($arr)
        {
            //$arr = get_allowed_mime_types();
            $arr = array_merge($arr, ExtendedUploadMimetypes::$mimes);
            return $arr;
        }
        public static function add($mime, $ext = null)
        {
            $mimetype;
            if (is_a($mime, 'ExtendedMimetype')) {
                $mimetype = $mime;
            } else {
                $mimetype = (is_null($ext)) ? new ExtendedMimetype($mime) : new ExtendedMimetype($mime, $ext);
            }
            if ($mimetype->isValid() && !ExtendedUploadMimetypes::exists($mimetype)) {
                ExtendedUploadMimetypes::$mimes[$mimetype->extension] = $mimetype->mimetype;
                ExtendedUploadMimetypes::init();
                return true;
            }
            return false;
        }
        public static function remove($mimetype)
        {
            if (ExtendedUploadMimetypes::extendedExists($mimetype)) {
                unset(ExtendedUploadMimetypes::$mimes[$mimetype->extension]);
                return true;
            }
        }
        public static function exists($mimetype)
        {
            // @codingStandardsIgnoreStart - line length
            return (ExtendedUploadMimetypes::extendedExists($mimetype) || ExtendedUploadMimetypes::platformExists($mimetype)) ? true : false;
            // @codingStandardsIgnoreEnd
        }
        public static function extendedExists($mimetype)
        {
            return (array_key_exists($mimetype->extension, ExtendedUploadMimetypes::$mimes)) ? true : false;
        }
        public static function platformExists($mimetype)
        {
            $existing = (function_exists('get_allowed_mime_types')) ? get_allowed_mime_types() : array();
            return (array_key_exists($mimetype->extension, $existing)) ? true : false;
        }
    }
}
if (!function_exists('wordpress_extend_upload_mimetypes')) {
    function wordpress_extend_upload_mimetypes()
    {
        $args = func_get_args();
        $args = (count($args) === 1) ? $args[0] : $args;
        if (count($args) > 0) {
            if (count($args) == 2 && isset($args[0]) && isset($args[1]) && is_string($args[0]) && is_string($args[1])) {
                $m = new ExtendedMimetype($args[0], $args[1]);
                if (ExtendedMimetype::isValid()) {
                    ExtendedUploadMimetypes::add($m);
                }
            } else {
                foreach ($args as $a) {
                    $a = new ExtendedMimetype($a);
                    if ($a->isValid()) {
                        ExtendedUploadMimetypes::add($a);
                    }
                }
            }
        }
    }
}
