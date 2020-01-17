<?php
if (!class_exists('ArgumentHandler')) {
    //This class is used to take multiple key=>values supplied as an array and if by addHandle an Object is passed
    //revised the passed k=>v to the structure and only contents of the addHandle defined.
    Class ArgumentHandler
    {
        private $_definer = null;
        public $returnObj = null;
        public function __construct($definer = null, $args = null) 
        {
            if ($definer != null && $args != null) {
              $this->addHandle($definer);
              return $this->passArguments();
            }
        }
        public function addHandle($arr)
        {
            $this->_definer = $arr;
        }
        public function passArguments()
        {
            $returnObj = array();
            $passed = array();
            $actualPass = func_get_args();
            //following line ensures args passed in wrapping array
            $actualPass = (count($actualPass) == 1 && isset($actualPass[0])) ? $actualPass : array($actualPass); 
            foreach ($actualPass as $a) {
                if (is_array($a)) {
                    foreach ($a as $k => $v) {
                        $passed[$k] = $v;
                    }
                }
            }
            if (is_array($this->_definer) && count($passed) > 0) {
                foreach ($this->_definer as $k => $v) {
                    if (isset($passed[$k])) {
                        $this->returnObj[$k] = $passed[$k];
                    }
                }
            } else {
                $this->returnObj = $passed;
            }
            return $this->returnObj;
        }
    }
}
