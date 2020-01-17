<?php
if (!function_exists('prettifyUKTelephone')) {
    /*
    @method prettifyUKTelephone
    @param {String} $n
    @param {Boolean} [optional] $includeCC (default = false)
    @return {String} UK Telephone number without +44 (opt) and 0, replaces full-stops and dashes for nbsp;
     */
    function prettifyUKTelephone($n, $includeCC = false)
    {
        $reg = '/^\+[0-9]{1-4}[0-9\-\.]{8,20}$/';
        $n = (preg_match($reg, $n) !== false) ? $n : '';
        if (!empty($n)) {
            $reg = '/^\+44/';
            $n = (preg_match($reg, $n) !== false) ? substr($n, 3) : $n;
            $reg = '/^\-/';
            $n = (preg_match($reg, $n) !== false) ? substr($n, 1) : $n;
            $n = ($includeCC) ? '+44-(0)-'.$n : '0'.$n;
            $n = str_replace('--', '-', $n);
            $n = str_replace('.-', '-', $n);
            $n = str_replace('-.', '-', $n);
            $n = str_replace('.', '&nbsp;', $n);
            $n = str_replace('-', '&nbsp;', $n);
            return $n;
        } else {
            return $n;
        }
    }
}
