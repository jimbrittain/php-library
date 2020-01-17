<?php
if (!class_exists('AsyncScripts')) {
    class AsyncScripts
    {
        public static $scripts = [];
        private static $_initiated = false;
        public static function filter($tag, $handle)
        {
            if (in_array(self::$scripts, $handle)) {
                return str_replace(' src', ' async="async" src', $tag);
            }
            return $tag;
        }
        public static function init()
        {
            if (function_exists('add_filter')) {
                add_filter('script_loader_tag', array('AsyncScripts', 'filter'), 10, 2);
                self::$_initiated = true;
            }
        }
        public static function add($script)
        {
            if (is_string($script)) {
                if (!(in_array(self::$scripts, $script))) {
                    self::$scripts[] = $script;
                    if (!self::$_initiated) { 
                        self::init(); 
                    }
                    return true;
                }
            }
            return false;
        }
        public static function remove($script)
        {
            if (is_string($script) && in_array(self::$scripts)) {
                for ($i=0, $imax = count(self::$scripts); $i < $imax; $i += 1) {
                    if (self::$scripts[$i] === $script) {
                        array_splice(self::$scripts, $i, 1);
                        $i -= 1;
                    }
                }
                return true;
            }
            return false;
        }
    }
    function addAsyncAttribute($handle) 
    {
        return AsyncScripts::add($handle);
    }
}
if (!class_exists('DeferScripts')) {
    class DeferScripts
    {
        public static $scripts = [];
        private static $_initiated = false;
        public static function filter($tag, $handle)
        {
            if (in_array($handle, self::$scripts)) {
                return str_replace(' src', ' defer="defer" src', $tag);
            }
            return $tag;
        }
        public static function init()
        {
            if (function_exists('add_filter')) {
                add_filter('script_loader_tag', array('DeferScripts', 'filter'), 10, 2);
                self::$_initiated = true;
            }
        }
        public static function add($script)
        {
            if (is_string($script)) {
                if (!(in_array($script, self::$scripts))) {
                    self::$scripts[] = $script;
                    if (!self::$_initiated) { 
                        self::init(); 
                    }
                    return true;
                }
            }
            return false;
        }
        public static function remove($script)
        {
            if (is_string($script) && in_array($script, self::$scripts)) {
                for ($i=0, $imax = count(self::$scripts); $i < $imax; $i += 1) {
                    if (self::$scripts[$i] === $script) {
                        array_splice(self::$scripts, $i, 1);
                        $i -= 1;
                    }
                }
                return true;
            }
            return false;
        }
    }
    function addDeferAttribute($handle)
    {
        return DeferScripts::add($handle);
    }
}
