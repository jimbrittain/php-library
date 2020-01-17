<?php
if (!function_exists('isRationalInteger')) {
    /**
        @method isRationalInteger
        @param $n
        @return {Boolean}
        Determines whether supplied, $n, is a rational integer
     */
    function isRationalInteger($n)
    {
        return (isset($n) && is_numeric($n) && (int) $n == $n && $n > 0) ? true : false; 
    }
}
if (!function_exists('returnRationalInteger') && function_exists('isRationalInteger')) {
    /**
        @method returnRationalInteger
        @requires isRationalInteger
        @param $n
        @param $failure
        @return either $n, if rational integer, $failure if not
     */
    function returnRationalInteger($n, $failure = -1)
    {
        return (isRationalInteger($n)) ? (int) $n : $failure;
    }
}
if (!function_exists('isRationalIntegerInRange') && function_exists('returnRationalInteger')) {
    /**
        @method isRationalIntegerInRange
        @requires returnRationalInteger
        @param $n
        @param $failure
        @param $rangeArr {Array} two values, [0] = lower range [1] = upper range
        @return {Boolean}
     */
    function isRationalIntegerInRange($n, $failure = -1, $rangeArr = array(0, 1000000000))
    {
        $n = returnRationalInteger($n, $failure);
        if ($n != $failure) {
            if (!is_array($rangeArr)) {
                if (isRationalNumber($rangeArr) && $rangeArr > 0) {
                    $rangeArr = array(0, $rangeArr); 
                }
            }
            $lower = isset($rangeArr[0]) ? $rangeArr[0] : 0;
            $upper = isset($rangeArr[1]) ? $rangeArr[1] : 1000000000;
            return ($r >= $lower && $r <= $upper) ? true : false;
        } else {
            return false;
        }
    }
}
if (!function_exists('returnRationalIntegerInRange') && function_exists('returnRationalInteger')) {
    /**
        @method returnRationalIntegerInRange
        @requires returnRationalInteger
        @param $n
        @param $failure
        @paran $rangeArr {Array} two values, [0] = lower range [1] = upper range
        @return $n or $failure
     */
    function returnRationalIntegerInRange($n, $failure = -1, $rangeArr = array(0, 1000000000))
    {
        $r = returnRationalInteger($n, $failure);
        if ($r != $failure) {
            $lower = isset($rangeArr[0]) ? $rangeArr[0] : 0;
            $upper = isset($rangeArr[1]) ? $rangeArr[1] : 1000000000;
            return ($r >= $lower && $r <= $upper) ? $r : $failure;
        } else {
            return $failure;
        }
    }
}
