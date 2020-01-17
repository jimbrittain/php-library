<?php

if (!function_exists('isDateString')) {
    function isDateString($str)
    {
        if (is_string($str) && !empty($str)) {
            $t = strtotime($str);
            if (!($t === false || $t === -1)) {
                return true;
            }
        }
        return false;
    }
}
