<?php
if (!class_exists('WPGalleryDefinition')) {
    class WPGalleryDefinition 
    {
        private $_name = '';
        private $_initialized = false;
        private static $_galleryInt = -1;
        private $_numberPerPage = 8;
        private $_query = false;
        private static $_definitionVars = array();
        private static $_singletonInitialized = false;
        private $_defaults = array();
        public function __construct($args)
        {
            self::initDefinitionVars(); //used for definingDefinitionVars, may be better on first declaration;
            $defaultArgs = array(
                'name' => '',
                'numberPerPage' => '',
                'defaults' => '',
                'query' => '',
                'vars' => ''
            );
            $a = new ArgumentHandler($defaultArgs);
            $p = $a->passArguments($args);
            if (isset($p['name'])) {
                $this->_setName($p['name']);
            }
            if (isset($p['query'])) {
                $this->_setQuery($p['query']);
            }
            if (isset($p['numberPerPage'])) {
                $this->setNumberPerPage($p['numberPerPage']);
            }
            if (isset($p['defaults'])) {
                $this->setDefaults($p['defaults']);
            }
            if (isset($p['vars'])) {
                $p['vars'] = (is_string($p['vars'])) ? array($p['vars']) : $p['vars'];
                if (is_array($p['vars'])) {
                    foreach ($p['vars'] as $d) {
                        if (is_string($d)) {
                            $this->declareDefinitionVar($d);
                        }
                    }
                }
            }
            if (!empty($this->_name) && $this->_query !== false) {
                $this->_initialized = true;
                WPGalleryPlugin::addDefinition($this);
            }
        }
        public function setDefaults($arr = array())
        {
            if (is_array($arr) && count($arr) > 0) {
                $this->_defaults = $arr;
                return true;
            }
            return false;
        }
        public static function initDefinitionVars()
        {
            if (!self::$_singletonInitialized) {
                add_filter(query_vars, 'WPGalleryDefinition::produceDefinitionVars');
                self::$_singletonInitialized = true;
            }
        }
        public static function produceDefinitionVars($vars)
        {
            foreach (self::$_definitionVars as $d) {
                $vars[] = $d;
            }
            return $vars;
        }
        public function declareDefinitionVar($name)
        {
            if (preg_match('/^[a-z\-_]+$/', $name) && !self::isDefinitionDeclared($name)) {
                self::$_definitionVars[] = $name;
                return true;
            }
            return false;
        }
        public static function isDefinitionVarDeclared($name)
        {
            if (is_string($name) && !empty($name)) {
                foreach (self::$_definitionVars as $d) {
                    if ($d === $name) {
                        return true;
                    }
                }
                return false;
            }
            return true; //catcher, makes sure that a non-string or empty string won’t be added
        }
        public function getDefinitionVars($gallery = '')
        {
            return self::retrieveDefinitionVars($gallery);
        }
        public function getDefintionVar($name = '', $gallery = '')
        {
            return self::retrieveDefinitionVar($name, $gallery);
        }
        public static function isDefinitionDeclared($name)
        {
            if (!empty($name) && is_string($name)) {
                foreach (self::$_definitionVars as $d) {
                    if ($d === $name) {
                        return true;
                    }
                }
            }
            return false;
        }
        public static function retrieveDefintionVar($name = '', $gallery = '')
        {
            if (!empty($name) && self::isDefinitionDeclared($name)) {
                $var = get_query_var($name, false);
                if (!empty($gallery)) {
                    return (WPGalleryPlugin::contextAppliesToGallery($gallery)) ? $var : false;
                } else {
                    return $var;
                }
            }
        }
        public static function retrieveDefinitionVars($gallery = '')
        {
            if (empty($gallery) || WPGalleryPlugin::contextAppliesToGallery($gallery)) {
                $arr = array();
                foreach (self::$_definitionVars as $d) {
                    $arr[$d] = get_query_var($d, false);
                }
                return $arr;
            }
            return array();
        }
        private function _setQuery($func)
        {
            if (is_string($func) && function_exists($func) && !$this->_initialized) {
                /* Can’t use because of delayed creation
                $a = array();
                $b = call_user_func($func, $a);
                if (is_array($b)) {
                    $this->_query = $func;
                    return true;
                }
                 */ 
                $this->_query = $func;
                return true;
            }
        }
        public function setNumberPerPage($num)
        {
            if (is_numeric($num) && (int) $num === $num && $num > 0) {
                $this->_numberPerPage = $num;
                return true;
            }
            return false;
        }
        public function getNumberPerPage()
        {
            return $this->_numberPerPage;
        }
        private function _setName($name)
        {
            if (!$this->_initialized) {
                $this->_name = ($this->nameOkay($name)) ? $name : self::_defaultName();
            }
        }
        public function nameOkay($name)
        {
            if (!WPGalleryPlugin::isDefinitionDeclared($name)) {
                return true;
            }
        }
        public function getName()
        {
            return $this->_name;
        }
        public function definitionArgs($vars = '')
        {
            $arr1 = array();
            $arr2 = array();
            if ($vars !== '') {
                $arr1 = call_user_func($this->_query, $vars);
            }
            /*
            if ($this->_defVarHandler !== false) {
                $arr2 = call_user_func($this->_query, $this->_defVarHandler);
            }*/
            $arr = array_merge($arr1, $arr2); //get take preference over inline
            $arr = $this->_addDefaults($arr); //defaults in case of in neither inline nor get;
            return $arr; 
        }
        private function _addDefaults($arr)
        {
            foreach ($this->_defaults as $k => $v) {
                if (!array_key_exists($k, $arr)) {
                    $arr[$k] = $v;
                }
            }
            return $arr;
        }
        public function produceArgs($gallery)
        {
            $args = array(
                'post_type' => 'attachment',
                'post_status' => 'inherit' //inherit as attachments 'inherit' parent post-type
            );
            $args = array_merge($args, $gallery->produceContextArgs());
            $args = array_merge($args, $this->definitionArgs($gallery->getDefinitionVars()));
            return $args;
        }
    }
}
