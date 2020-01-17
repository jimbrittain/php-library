<?php
if (!function_exists('ensureIntegerArray')) {
    /**
    @method ensureIntegerArray
    @param {Array|Number|String} $a
    @return {Array}
    Returns an array containing only integers based on supplied parameters
    */
    function ensureIntegerArray($a) 
    {
        $arr = array();
        $a = (is_array($a)) ? $a : array($a);
        for ($i = 0; $i < count($a); $i += 1) {
            $n = $a[$i];
            if (is_numeric($n)) {
                $arr[] = (int) $n;
            }
        }
        return $arr;
    }
}
