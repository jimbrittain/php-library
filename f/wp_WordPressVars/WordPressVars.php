<?php

if (!class_exists('WordPressVars')) {
    class WordPressVars
    {
        private static $_vars = [];
        public static function add($reference, $jsvar)
        {
            if (is_string($reference) && is_string($jsvar)) {
                $o = new StdClass();
                $o->ref = $reference;
                $o->js = $jsvar;
                self::$_vars[] = $o;
            }
        }
        public static function produce()
        {
            $str = '';
            foreach (self::$_vars as $v) {
                $js = $v->js;
                $val = '';
                if (strpos($v->ref, '(') === false) {
                    if (isset($GLOBALS[$v->ref])) {
                       $val = $GLOBALS[$v->ref];
                    } else {
                        $val = $v->ref;
                    }
                } else {
                    //not using function parameters yet, not using object ones either
                    $method = substr($v->ref, 0, (strpos($v->ref, '(')));
                    if (function_exists($method)) {
                        $val = call_user_func($method);
                    }
                }
                $str .= (strlen($str) === 0) ? 'var ' : ', ';
                $str .= $js.' = ';
                $str .= (empty($val)) ? 'null' : "'".$val."'";
            }
            if (strlen($str) > 0) {
                $str = '<script type="text/javascript">'.$str.'</script>';
            }
            return $str;
        }
    }
}
