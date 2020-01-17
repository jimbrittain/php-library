<?php
    if (!function_exists('singleStringArray')) {
        function singleStringArray($arr = array())
        {
            $ret = array();
            $arr = (is_array($arr)) ? implode(' ', $arr) : $arr;
            if (is_string($arr)) {
                $arr = str_replace(', ', ' ', $arr);
                $arr = str_replace('  ', ' ', $arr);
                $arr = str_replace(',', ' ', $arr);
                $arr = explode(' ', $arr);
            }
            foreach ($arr as $a) {
                if (is_string($a) && !empty($a)) {
                    $ret[] = $a;
                }
            }
            return $ret;
        }
    }
